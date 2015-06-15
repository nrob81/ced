<?php
/**
 * @property integer $caller
 * @property integer $opponent
 * @property boolean $isChallenge
 * @property boolean $played
 * @property array $competitors
 */
class DuelShield extends CModel
{
    private $uid;
    private $last = 0;
    
    public function attributeNames()
    {
        return [];
    }

    public function setUid($uid)
    {
        $this->uid = (int)$uid;
        $this->fetch();
    }

    /**
     * Get the time, until the shield is active
     */
    public function getLifetime()
    {
        return $this->last - time();
    }

    protected function fetch()
    {
        $last = Yii::app()->db->createCommand()
            ->select('until')
            ->from('duel_shield')
            ->where('uid = :uid', [':uid'=>$this->uid])
            ->order('until DESC')
            ->limit(1)
            ->queryScalar();
        $this->last = strtotime($last);
    }

    public function getPrices()
    {
        // 10min/gold
        return [
            15 => ['price'=>2, 'label'=>'15 perc'], //interval => price
            30 => ['price'=>3, 'label'=>'30 perc'],
            60 => ['price'=>6, 'label'=>'1 óra'],
            120 => ['price'=>12, 'label'=>'2 óra'],
            180 => ['price'=>18, 'label'=>'3 óra'],
        ];
    }

    public function activate($time)
    {
        $player = Yii::app()->player->model;
        $logger = new Logger;
        $logger->key = 'shield:'.date('Y-m-d').':'.$this->uid;
        $logger->addToSet('----start: '.date('H:i:s').'----');
        $logger->addToSet('gold:'.$player->gold.', time: ' . $time);

        $prices = $this->getPrices();
        if (!array_key_exists($time, $prices)) {
            throw new CFlashException('A pajzsot csak 10, 30 vagy 60 percre aktiválhatod.');
        }
        $price = $prices[$time]['price'];

        $logger->addToSet('price found: ' . $price);
        
        if ($player->gold < $price) {
            throw new CFlashException('Nincs elég aranyad a kiválasztott pajzs aktiválásához.');
        }
        $logger->addToSet('gold > price');

        if ($this->getLifetime() > 0) {
            throw new CFlashException('A korábban aktivált pajzsod még érvényes.');
        }
        $logger->addToSet('lifeTime = 0, can activate new');

        $player->updateAttributes([], ['gold'=>$price]);
        $logger->addToSet('gold decreased');
        
        Yii::app()->db->createCommand()->insert('duel_shield', [
            'uid'   =>  $this->uid,
            'until' =>  date('Y-m-d H:i:s', time()+($time*60)),
        ]);
        $logger->addToSet('shield activated, time:' . $time . ', price:' . $price);
        return true;
    }
}
