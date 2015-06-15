<?php
/**
 * @property integer $id
 * @property string $item_type
 * @property integer $skill
 * @property integer $level
 * @property string $title
 * @property integer $price
 * @property integer $price_sell
 * @property integer $owned
 * @property integer $buy_amount
 * @property integer $sell_amount
 * @property array $errors
 * @property boolean $success
 */
class Item extends CModel
{
    const TYPE_BAIT = 'bait';
    const TYPE_ITEM = 'item';
    const TYPE_ITEMSET = 'itemset';
    const TYPE_PART = 'part';

    private $id;
    private $item_type;
    private $skill;
    private $level;
    private $title;
    private $price;
    private $owned;
    private $buy_amount = [];
    private $sell_amount = [];

    private $errors = ['dollar'=>false, 'amount'=>false, 'owned'=>false, 'isLast'=>false, 'freeSlots'=>false];
    private $success;
    
    public function attributeNames()
    {
        return [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getItem_type()
    {
        return $this->item_type;
    }

    public function getSkill()
    {
        return $this->skill;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getPrice_sell()
    {
        return floor($this->price / 2);
    }

    public function getOwned()
    {
        return $this->owned;
    }

    public function getBuy_amount()
    {
        return $this->buy_amount;
    }

    public function getSell_amount()
    {
        return $this->sell_amount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setItem_type($type)
    {
        $this->item_type = $type;
    }

    public function setOwned($owned)
    {
        $this->owned = (int)$owned;
    }
    
    public function fetch()
    {
        if (!$this->id) {
            return false;
        }

        $uid = Yii::app()->player->uid;

        //read all from db
        $dependency = new CExpressionDependency("Yii::app()->params['{$this->item_type}s_version']");
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('*')
            ->from($this->item_type.'s')
            ->where('id=:id', [':id'=>$this->id])
            ->queryRow();

        if (!$res) {
            return false;
        }

        foreach ($res as $k => $v) {
            if ($k == 'id') {
                continue;
            }

            $this->$k = $v;
        }
        
        $own = Yii::app()->db->createCommand()
            ->select('item_count')
            ->from('users_'.$this->item_type.'s')
            ->where('uid=:uid and item_id=:item_id', [':uid'=>$uid, ':item_id'=>$this->id])
            ->queryRow();
        $this->owned = (int)$own['item_count'];

        $this->setBuyAmount();
        $this->setSellAmount();
    }
    
    public function fetchSet()
    {
        if (!$this->id) {
            return false;
        }

        $uid = Yii::app()->player->uid;

        $combinedId = (string)$this->id;
        $setId = (int)substr($combinedId, 0, -3);
        $this->level = (int)substr($combinedId, -3);


        //read all from db
        $dependency = new CExpressionDependency("Yii::app()->params['{$this->item_type}s_version']");
        $res = Yii::app()->db->cache(Yii::app()->params['cacheDuration'], $dependency)->createCommand()
            ->select('*')
            ->from('itemsets')
            ->where('id=:id', [':id'=>$setId])
            ->queryRow();

        if (!$res) {
            return false;
        }

        foreach ($res as $k => $v) {
            if ($k == 'id') {
                continue;
            }

            $this->$k = $v;
        }
        
        $own = Yii::app()->db->createCommand()
            ->select('skill, item_count, price')
            ->from('users_items')
            ->where('uid=:uid and item_id=:item_id', [':uid'=>$uid, ':item_id'=>$this->id])
            ->queryRow();
        $this->owned = (int)$own['item_count'];

        //read skill from users tbl
        $this->title = $this->level . '. szintű ' . $this->title;
        $this->skill = (int)$own['skill'];
        $this->price = (int)$own['price'];
        $this->level = 1;

        $this->setBuyAmount();
        $this->setSellAmount();
    }

    /**
     * @param integer $amount
     */
    public function buy($amount)
    {
        $decr = [];
        $amount = (int)$amount;
        if ($amount < 1) {
            $this->errors['amount'] = true;
            return false;
        }

        if ($this->price * $amount > Yii::app()->player->model->dollar) {
            $this->errors['dollar'] = true;
            return false;
        }

        if ($this->item_type !== self::TYPE_PART) {
            if ($amount > Yii::app()->player->model->freeSlots) {
                $this->errors['freeSlots'] = true;
                return false;
            }
        }

        $uid = Yii::app()->player->uid;

        //add to inventory
        $update = Yii::app()->db
            ->createCommand("UPDATE users_{$this->item_type}s SET item_count=item_count+:amount WHERE uid=:uid AND item_id=:item_id")
            ->bindValues([':uid'=>$uid, ':item_id'=>(int)$this->id, ':amount'=>$amount])
            ->execute();
        
        if (!$update) {
            Yii::app()->db->createCommand()
                ->insert('users_'.$this->item_type.'s', [
                'uid'=>$uid,
                'item_id'=>(int)$this->id,
                'item_count'=>$amount,
                'skill'=>(int)$this->skill,
                ]);
        }

        //pay for it
        if ($this->price > 0) {
            $decr['dollar'] = $amount * $this->price;
            Yii::app()->player->model->updateAttributes([], $decr);
        }

        $this->success = true;
        $this->owned += $amount;
        $this->setBuyAmount();
    }
    
    public function sell($amount)
    {
        $incr = [];
        $amount = (int)$amount;
        if ($amount < 1) {
            $this->errors['amount'] = true;
            return false;
        }

        if ($this->owned < $amount) {
            $this->errors['owned'] = true;
            return false;
        }

        $player = Yii::app()->player->model;

        $owned = $this->item_type == self::TYPE_BAIT ? $player->owned_baits : $player->owned_items;
        if ($owned == 1) {
            $this->errors['isLast'] = true;
            return false;
        }

        //remove from inventory
        Yii::app()->db
            ->createCommand("UPDATE users_{$this->item_type}s SET item_count=item_count-:amount WHERE uid=:uid AND item_id=:item_id")
            ->bindValues([':uid'=>$player->uid, 'item_id'=>(int)$this->id, ':amount'=>$amount])
            ->execute();
        
        //give money for it
        $incr['dollar'] = $amount * $this->price_sell;
        Yii::app()->player->model->updateAttributes($incr, []);
        
        $this->success = true;
        $this->owned -= $amount;
        $this->setSellAmount();
        
    }

    private function setBuyAmount()
    {
        foreach ([1,5,10] as $amount) {
            $this->buy_amount[$amount] = (bool)($this->price * $amount <= Yii::app()->player->model->dollar);
        }
    }

    private function setSellAmount()
    {
        foreach ([1,5,10] as $amount) {
            $this->sell_amount[$amount] = (bool)($this->owned >= $amount);
        }
    }

    public function __toString()
    {
        $attributes = ['id','item_type','skill','level','price','title','owned'];
        $ret = '';
        foreach ($attributes as $attribute) {
            $ret .= $attribute . ': ' . $this->$attribute . "\n";
        }
        return $ret;
    }
}
