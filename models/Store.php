<?php
class Store extends CModel
{
    private $_uid;
    private $_blackBait = ['id'=>0];
    
    public function attributeNames() {
        return [];
    }

    public function getUid() { return $this->_uid; }
    public function getBlackBait() { return $this->_blackBait; }
    public function getPackagesSms() { return Yii::app()->params['packagesSms']; }
    

    public function setUid($uid) {
        $this->_uid = (int)$uid;
    }

    public function fetch() {
        $level = Yii::app()->player->model->level;
        $bait = Yii::app()->db->createCommand()
            ->select('*')
            ->from('baits')
            ->where('level >= :min AND level <= :max', [':min'=>$level, ':max'=>$level+2])
            ->order('skill DESC, level DESC')
            ->limit(1)
            ->queryRow();
        $this->_blackBait = $bait;
    }

    public function refillEnergy() {
        $player = Yii::app()->player->model;

        $logger = new Logger;
        $logger->key = 'refillEnergy:'.date('Y-m-d').':'.$this->_uid;
        $logger->addToSet('----start: '.date('H:i:s').'----');
        $logger->addToSet('gold:'.$player->gold.', energy: ' . $player->energy.'/'.$player->energy_max);

        if ($player->gold < 20) throw new CFlashException('Nincs elég aranyad az energiaital kifizetésére.');
        $logger->addToSet('gold > 20');
        if ($player->energy_missing < 3) throw new CFlashException('Kevesebb, mint 3 energiára van szükséged. Emiatt nem érdemes energiaitalt innod.');
        $logger->addToSet('energy_missing > 3');

        $player->updateAttributes(['energy'=>$player->energy_missing], ['gold'=>20]);
        $logger->addToSet('energy increased, gold decreased');
        
        $logger->addToSet('---- end: '.date('H:i:s').'----');
        
        Yii::app()->badge->model->trigger('energy_drink');
        return true;
    }
    public function activateBlackMarket() {
        $player = Yii::app()->player->model;
        $logger = new Logger;
        $logger->key = 'blackMarket:'.date('Y-m-d').':'.$this->_uid;
        $logger->addToSet('----start: '.date('H:i:s').'----');
        $logger->addToSet('gold:'.$player->gold.', level: ' . $player->level);
        
        if ($player->gold < 10) throw new CFlashException('Nincs elég aranyad.');
        $logger->addToSet('gold > 10');
        if ($player->black_market) throw new CFlashException('Még működik az előzőleg aktivált feketepiac.');
        $logger->addToSet('black_market inactive');

        $timeBlackMarket = date('Y-m-d H:i:s', time()+600);
        $player->rewriteAttributes(['black_market'=>$timeBlackMarket, 'gold'=>$player->gold-10]);

        $logger->addToSet('timeBM:'.$timeBlackMarket);
        $logger->addToSet('---- end: '.date('H:i:s').'----');

        return true;
    }

}
