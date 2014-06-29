<?php
/**
 * @property integer $uid
 * @property array $badges
 */
class Badge extends CModel
{
    const LEVEL_BRONZE = 1;
    const LEVEL_SILVER = 2;
    const LEVEL_GOLD = 3;

    protected $uid;
    protected $badges;

    public function attributeNames()
    {
        return [];
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    public function fetchBadges()
    {
        $redis = Yii::app()->redis->getClient();
        if ($this->badges) {
            return false;
        }

        $this->badges = $redis->sMembers('badges:all');
    }

    protected function getBadge($id)
    {
        $redis = Yii::app()->redis->getClient();
        return $redis->hGetAll("badges:d:$id");
    }
}
