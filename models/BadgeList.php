<?php
/**
 * @property array $ownedKeys
 * @property array $owned
 * @property array $all
 * @property integer $percentOwned
 */
class BadgeList extends Badge
{
    private $ownedKeys = [];
    private $owned = [];
    private $all = [];
    private $categoryFirst = [
        'max_nrg_100'=>'s',
        'setpart_30'=>'g'
        ];

    public function getOwnedKeys()
    {
        return $this->ownedKeys;
    }

    public function getOwned()
    {
        return $this->owned;
    }

    public function getAll()
    {
        return $this->all;
    }

    public function getPercentOwned()
    {
        $redis = Yii::app()->redis->getClient();
        $cntOwned = $redis->sCard("badges:owned:{$this->uid}");
        $cntAll = $redis->zCard("badges:all");
        return round(($cntOwned / $cntAll) * 100);
    }

    public function fetchOwned()
    {
        if (!$this->uid) {
            $this->setUid(Yii::app()->player->model->uid); //set default uid
        }

        $redis = Yii::app()->redis->getClient();
        $this->ownedKeys = $redis->zRevRange("badges:added:{$this->uid}", 0, -1);

        foreach ($this->ownedKeys as $item) {
            $b = $this->getBadge($item);
            $this->owned[$item] = $b;
        }
    }

    public function fetchAll()
    {
        if (!$this->uid) {
            $this->setUid(Yii::app()->player->model->uid); //set default uid
        }

        $redis = Yii::app()->redis->getClient();
        $all = $redis->zRange('badges:all', 0, -1);

        $categ = 'b';
        foreach ($all as $item) {
            if (array_key_exists($item, $this->categoryFirst)) {
                $categ = $this->categoryFirst[$item];
            }

            if (array_key_exists($item, $this->owned)) {
                $b = $this->owned[$item];
                $b['owned'] = 1;
            } else {
                $b = $this->getBadge($item);
                $b['owned'] = 0;
            }
            $this->all[$categ][$item] = $b;
        }
    }
}
