<?php
/**
 * @property integer $uid
 * @property array $blackBait
 * @property array $packagesSms
 */
class Store extends CModel
{
    private $uid;
    private $missingSetItemPrice = 100;

    public function attributeNames()
    {
        return [];
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getPackagesSms()
    {
        return Yii::app()->params['packagesSms'];
    }

    public function getMissingSetItemPrice()
    {
        return $this->missingSetItemPrice;
    }
    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    public function refillEnergy()
    {
        $player = Yii::app()->player->model;

        $logger = new Logger;
        $logger->key = 'refillEnergy:'.date('Y-m-d').':'.$this->uid;
        $logger->addToSet('----start: '.date('H:i:s').'----');
        $logger->addToSet('gold:'.$player->gold.', energy: ' . $player->energy.'/'.$player->energy_max);

        if ($player->gold < 20) {
            throw new CFlashException('Nincs elég aranyad az energiaital kifizetésére.');
        }

        $logger->addToSet('gold > 20');
        if ($player->energy_missing < 3) {
            throw new CFlashException('Kevesebb, mint 3 energiára van szükséged. Emiatt nem érdemes energiaitalt innod.');
        }

        $logger->addToSet('energy_missing > 3');

        $player->updateAttributes(['energy'=>$player->energy_missing], ['gold'=>20]);
        $logger->addToSet('energy increased, gold decreased');

        $logger->addToSet('---- end: '.date('H:i:s').'----');

        Yii::app()->badge->model->triggerSimple('energy_drink');
        return true;
    }

    public function listMissingSetItems()
    {
        $shop = new Shop;
        $shop->item_type = Shop::TYPE_PART;
        $shop->fetchSets();
        $sets = $shop->items;

        $missing = [];
        foreach ($sets as $set) {
            foreach ($set['items'] as $item) {
                if (!$item->owned) {
                    $missing[] = $item;
                }
            }
        }

        return $missing;
    }
    public function buySetItem($id)
    {
        $player = Yii::app()->player->model;
        $userName = $player->user;

        //check is we have the selected item in the missing list
        $missingList = $this->listMissingSetItems();
        $buyItem = null;
        foreach ($missingList as $item) {
            if ($item['id'] == $id) {
                $buyItem = $item;
                break;
            }
        }
        //check it it is an existing item
        if ($buyItem == null) {
            throw new CFlashException('A keresett elem nem létezik.');
        }
        //check the amount of gold
        if ($player->gold < $this->missingSetItemPrice) {
            throw new CFlashException('Nincs elég aranyad a szett elem kifizetésére.');
        }

        //check is we bought one this week of this type
        $setItemType = strtok($buyItem['title'], ' ');
        $logKey = 'debug:buySetItem:' . date('Y:W') . ':' . strtolower($setItemType);

        $hash = new ARedisHash($logKey);
        if ($hash->offsetExists($userName)) {
            throw new CFlashException($setItemType .' típusú szett elemet már vettél a héten. Jövő héten vásárolhatsz belőle újból.');
        }

        //buy the item
        $i = new Item;
        $i->id = $buyItem['id'];
        $i->item_type = Item::TYPE_PART;
        $i->fetch();
        $i->buy(1);

        //pay the price
        $player->updateAttributes([], ['gold'=>$this->missingSetItemPrice]);

        //log the purchase
        $hash->add($userName, date('H:i:s') . ', ' . $buyItem['title']);

        return true;
    }
}
