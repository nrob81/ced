<?php
/**
 * @property array $ownedKeys
 * @property array $owned
 * @property array $all
 * @property integer $percentOwned
 */
class BadgeList extends Badge
{
    private $_ownedKeys = [];
    private $_owned = [];
    private $_all = [];
    private $_categoryFirst = [
        'max_nrg_100'=>'s',
        'setpart_30'=>'g'
        ];

    public function getOwnedKeys() { return $this->_ownedKeys; }
    public function getOwned() { return $this->_owned; }
    public function getAll() { return $this->_all; }
    public function getPercentOwned() {
        $redis = Yii::app()->redis->getClient();
        $cntOwned = $redis->sCard("badges:owned:{$this->_uid}");
        $cntAll = $redis->zCard("badges:all");
        return round(($cntOwned / $cntAll) * 100);
    }

    public function fetchOwned() {
        if (!$this->_uid) $this->setUid(Yii::app()->player->model->uid); //set default uid

        $redis = Yii::app()->redis->getClient();
        //$this->_ownedKeys = $redis->sInter("badges:owned:{$this->_uid}", 'badges:all');
        $this->_ownedKeys = $redis->zRevRange("badges:added:{$this->_uid}", 0, -1);

        foreach ($this->_ownedKeys as $item) {
            $b = $this->getBadge($item);
            $this->_owned[$item] = $b;
        }
    }
    
    public function fetchAll() {
        if (!$this->_uid) $this->setUid(Yii::app()->player->model->uid); //set default uid

        $redis = Yii::app()->redis->getClient();
        //$all = $redis->sMembers('badges:all');
        $all = $redis->zRange('badges:all', 0, -1);

        $categ = 'b';
        foreach ($all as $item) {
            if (array_key_exists($item, $this->_categoryFirst)) {
                $categ = $this->_categoryFirst[$item];
            }

            if (array_key_exists($item, $this->_owned)) {
                $b = $this->_owned[$item];
                $b['owned'] = 1;
            } else {
                $b = $this->getBadge($item);
                $b['owned'] = 0;
            }
            $this->_all[$categ][$item] = $b;
        }
    }
}
