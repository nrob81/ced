<?php
/**
 * @property integer $uid
 * @property array $blackBait
 * @property array $packagesSms
 */
class Store extends CModel
{
    private $uid;

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
}
