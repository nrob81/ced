<?php
class Badge extends CModel
{
    const LEVEL_BRONZE = 1;
	const LEVEL_SILVER = 2;
	const LEVEL_GOLD = 3;

    protected $_uid;
    protected $_badges;

    public function attributeNames() {
        return [];
    }

    public function getUid() { return $this->_uid; }
    public function setUid($uid) {
        $this->_uid = (int)$uid;
    }

    public function fetchBadges() {
        $redis = Yii::app()->redis->getClient();
        if ($this->_badges) return false;

        $this->_badges = $redis->sMembers('badges:all');
    }
    
    protected function getBadge($id) {
        $redis = Yii::app()->redis->getClient();
        return $redis->hGetAll("badges:d:$id"); 
    }
}
