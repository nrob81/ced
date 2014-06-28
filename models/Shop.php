<?php
/**
 * @property integer $id
 * @property array $items
 * @property string $item_type
 * @property integer $page
 * @property integer $owned_baits
 * @property integer $owned_items
 * @property array $success
 * @property CPagination $pagination
 * @property integer $count
 * @property integer $transactionId
 * @property integer $levelLimit
 * @property integer $nextItemsLevel
 */
class Shop extends CModel
{
    const TYPE_BAIT = 'bait';
    const TYPE_ITEM = 'item';
    const TYPE_PART = 'part';
    
    private $_id;
    private $_items = [];
    private $_item_type;
    private $_page = 0;
    private $_pagination;
    private $_count;
    private $_transactionId;
    private $_success = ['setSold'=>false];
    
    public function attributeNames() {
        return [];
    }
    
    public function getId() {
        return $this->_id;
    }

    public function getItems() {
        return $this->_items;
    }

    public function getOwned_baits() {
        return Yii::app()->player->model->owned_baits;
    }

    public function getOwned_items() {
        return Yii::app()->player->model->owned_items;
    }

    public function getSuccess() {
        return $this->_success;
    }

    public function getPagination() { 
        return $this->_pagination;
    }

    public function getCount() {
        return $this->_count;
    }

    public function getTransactionId() {
        return $this->_transactionId;
    }

    public function getLevelLimit () {
        $player = Yii::app()->player->model;
        $levelLimit = $player->level;
        if ($player->black_market) $levelLimit += 2;
        return (int)$levelLimit;    
    }
    
    public function getNextItemsLevel() {
        $nextLevel = Yii::app()->db->createCommand()
            ->select('level')
            ->from($this->_item_type.'s')
            ->where('level > :level', [':level'=>Yii::app()->player->model->level])
            ->order('level ASC')
            ->limit(1)
            ->queryScalar();
        return (int)$nextLevel;
    }

    public function setItem_type($type) {
        $this->_item_type = $type;
    }
    public function setPage($page) {
        $this->_page = $page;
    }
    public function setId($id) {
        $this->_id = (int)$id;
    }
    

    public function fetchSets() {
        //echo __FUNCTION__ . "\n";
        $res = Yii::app()->db->createCommand()
            ->select('id, parts, title')
            ->from('itemsets')
            ->order('id DESC')
            ->queryAll();
         
        foreach ($res as $item) {
            $parts = explode(',', $item['parts']);

            $ownedOne = false;
            $skill = 0;
            $constructable = true;
            foreach ($parts as $part) {
                $i = new Item();
                $i->id = $part;
                $i->item_type = $this->_item_type;
                $i->fetch();
                $skill += $i->skill;
                $item['items'][$part] = $i;

                if ($i->owned) {
                    $ownedOne = true;
                } else {
                    $constructable = false;
                }

            }
            $item['skill_multiplicator'] = $skill;
            $item['constructable'] = $constructable;
            $item['sold'] = false;
            if ($ownedOne) {
                $this->_items[$item['id']] = $item;
            }
        }
    }

    
    public function fetchItems() {
        $limit = Yii::app()->params['listPerPage'];
        $levelLimit = $this->levelLimit;

        //echo __FUNCTION__ . "\n";
        $this->_count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from($this->_item_type.'s')
            ->where('level <= :level', [':level'=>$levelLimit])
            ->queryScalar();

        $res = Yii::app()->db->createCommand()
            ->select('id')
            ->from($this->_item_type.'s')
            ->where('level <= :level', [':level'=>$levelLimit])
            ->order('skill DESC, level DESC')
            ->limit($limit, ($this->_page * $limit) - $limit) // the trick is here!
            ->queryAll();
         
        $this->_pagination = new CPagination($this->_count);
        $this->_pagination->setPageSize(Yii::app()->params['listPerPage']);
                
        foreach ($res as $item) {
            $i = new Item();
            $i->id = $item['id'];
            $i->item_type = $this->_item_type;
            $i->fetch();

            $this->_items[$item['id']] = $i;
        }
    }
    public function fetchPlayersItems() {
        $limit = Yii::app()->params['listPerPage'];

        //echo __FUNCTION__ . "\n";
        $this->_count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from('users_'.$this->_item_type.'s')
            ->where('uid=:uid AND item_count > 0', [':uid'=>Yii::app()->player->uid])
            ->queryScalar();

        $res = Yii::app()->db->createCommand()
            ->select('item_id')
            ->from('users_'.$this->_item_type.'s')
            ->where('uid=:uid AND item_count > 0', [':uid'=>Yii::app()->player->uid])
            ->order('skill DESC, item_id DESC')
            ->limit($limit, ($this->_page * $limit) - $limit) // the trick is here!
            ->queryAll();
         
        $this->_pagination = new CPagination($this->_count);
        $this->_pagination->setPageSize(Yii::app()->params['listPerPage']);
                
        foreach ($res as $item) {
            $i = new Item();
            $i->id = $item['item_id'];
            if ($i->id < 1000) {
                $i->item_type = $this->_item_type;
                $i->fetch();
            } else {
                $i->item_type = $this->_item_type;
                $i->fetchSet();
            }

            $this->_items[$item['item_id']] = $i;
        }
    }
    
    public function buyItem($id, $amount) {
        if (!isset($this->_items[$id])) {
            //todo log missing mission
            return false;
        }

        //echo __FUNCTION__ . "\n";
        $this->_transactionId = $id;
        $i = $this->_items[$id];
        $i->buy($amount);
        if ($i->success) {
            $b = Yii::app()->badge->model;

            if ($i->item_type == Item::TYPE_BAIT) {
                Yii::app()->player->model->owned_baits = Yii::app()->player->model->owned_baits + $amount;
                $b->triggerBaits(Yii::app()->player->model->owned_baits);            
            }
            if ($i->item_type == Item::TYPE_ITEM) {
                Yii::app()->player->model->owned_items = Yii::app()->player->model->owned_items + $amount;
                $b->triggerItems(Yii::app()->player->model->owned_items);            
            }
            
            (new Skill)->updateExtended();
        }
    }
    
    public function sellItem($id, $amount) {
        if (!isset($this->_items[$id])) {
            //todo log missing mission
            return false;
        }

        //echo __FUNCTION__ . "\n";
        $this->_transactionId = $id;
        $i = $this->_items[$id];
        $i->sell($amount);
        if ($i->success) {
            if ($i->item_type == Item::TYPE_BAIT) Yii::app()->player->model->owned_baits = Yii::app()->player->model->owned_baits - $amount;
            if ($i->item_type == Item::TYPE_ITEM) Yii::app()->player->model->owned_items = Yii::app()->player->model->owned_items - $amount;

            (new Skill)->updateExtended();
            
            $setId = $id > 999 ? $id[0] : 0;
            if ($setId) {
                Yii::app()->badge->model->triggerSet($setId, true);
            }
        }
    }

    public function constructItem($id) {
        if (!isset($this->items[$id])) throw new CFlashException('A kiválasztott szett nem létezik.');
        $set = $this->_items[$id];
        if (!$set['constructable']) throw new CFlashException('Nem rendelkezel a kiválasztott szett összes elemével.');
        if (!Yii::app()->player->model->freeSlots) throw new CFlashException('Nincs szabad helyed, most nem tudod összeszerelni a szettet. Tipp: adj el egy felszerelést vagy növeld meg a teherbírásodat.');

        $player = Yii::app()->player->model;
        if ($player->energy < $player->energy_max) throw new CFlashException('A szett elkészítéséhez az összes energiádra szükség lesz.');

        foreach ($set['items'] as $i) {
            $remaining = $i->owned - 1;
            //remove from inventory
            Yii::app()->db
                ->createCommand("UPDATE users_parts SET item_count=:remaining WHERE uid=:uid AND item_id=:item_id")
                ->bindValues([':uid'=>$player->uid, 'item_id'=>(int)$i->id, ':remaining'=>$remaining])
                ->execute();
            $set['items'][$i->id]->owned--;
        }

        //the id of new item
        $itemId = $set['id'] . sprintf('%03d', $player->level);

        //add created item to inventory
        $update = Yii::app()->db
            ->createCommand("UPDATE users_items SET item_count=item_count+:amount WHERE uid=:uid AND item_id=:item_id")
            ->bindValues([':uid'=>$player->uid, 'item_id'=>(int)$itemId, ':amount'=>1])
            ->execute();
        
        if (!$update) {
            $best = Yii::app()->db->createCommand()
                ->select('skill, price')
                ->from('items')
                ->where('level <= :level', [':level'=>Yii::app()->player->model->level])
                ->order('skill DESC, level DESC')
                ->limit(1)
                ->queryRow();

            Yii::app()->db->createCommand()
                ->insert('users_items', [
                'uid'=>$player->uid,
                'item_id'=>(int)$itemId,
                'item_count'=>1,
                'skill'=>(int)$best['skill'] * $set['skill_multiplicator'],
                'price'=>(int)$best['price'] * $set['skill_multiplicator'] * 2,
                ]);
        }
        $this->_items[$id]['sold'] = true;
        $this->_success['setSold'] = true;
        
        (new Skill)->updateExtended();

        //decrement energy
        $player->rewriteAttributes(['energy'=>0]);
        
        Yii::app()->badge->model->triggerSet($set['id']); 
    }
}
