<?php
/**
 * @property integer $uid
 * @property array $stats
 */
class PlayerStats extends CModel
{
    private $uid;
    private $stats = [
        'completed_missions'=>0,
        'visited_waters'=>0,
        'visited_counties'=>0,
        'owned_setitems'=>0,
        'setitems'=>[],
        'items'=>[],
        'baits'=>[],
        'sets'=>[],
        'counties'=>[],
        ];

    public function attributeNames() {
        return [];
    }

    public function getUid() { return $this->uid; }
    public function getStats() { return $this->stats; }

    public function setUid($uid) {
        $this->uid = (int)$uid;
    }

    public function fetchStats() {
        $this->fetchCompletedMissions();
        $this->fetchVisitedMissions();
        $this->fetchDuel();
        $this->fetchCountSets();
        $this->fetchItems();
        $this->fetchBaits();
        $this->fetchSets();
        $this->fetchRank();
    }

    protected function fetchCompletedMissions()
    {
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('users_missions')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryScalar();

        $this->stats['completed_missions'] = $res;
    }

    protected function fetchVisitedMissions()
    {
        $res = Yii::app()->db->createCommand()
            ->select('v.*, w.county_id, w.title')
            ->from('visited v')
            ->join('waters w', 'v.water_id=w.id')
            ->where('v.uid=:uid', [':uid'=>$this->uid])
            ->order('v.water_id')
            ->queryAll();

        foreach ($res as $dat) {
            $this->stats['routine'][$dat['water_id']] = $dat['routine'];
            $this->stats['visited_waters']++;
            if (!array_key_exists($dat['county_id'], $this->stats['counties'])) $this->stats['visited_counties']++;
            $this->stats['counties'][$dat['county_id']] = 1;
        }
    }

    protected function fetchDuel()
    {
        $logger = new Logger; 
        $logger->uid = $this->uid;
        $stat = $logger->getCounters();

        //duels
        $this->stats['duel_success'] = @(int)$stat['duel_success'];
        $this->stats['duel_fail'] = @(int)$stat['duel_fail'];
        $this->stats['duel_rate'] = '?';
        if ($this->stats['duel_success'] or $this->stats['duel_fail']) {
            $this->stats['duel_rate'] = round( $this->stats['duel_success'] / (($this->stats['duel_success'] + $this->stats['duel_fail'])/100) ,1);
        }
    }

    protected function fetchCountSets()
    {
        $res = Yii::app()->db->createCommand()
            ->select('item_id, item_count')
            ->from('users_items')
            ->where('uid=:uid AND item_id>999 AND item_count>0', [':uid'=>$this->uid])
            ->order('skill DESC, item_id DESC')
            ->queryAll();
        $best = 0;
        foreach ($res as $dat) {
            if ($best < 3 and $dat['item_count']) {
                $this->stats['setitems'][$dat['item_id']] = $dat['item_count'];
                $best++;
            }
            $this->stats['owned_setitems'] += $dat['item_count'];
        }
    }

    protected function fetchItems()
    {
        $res = Yii::app()->db->createCommand()
            ->select('item_id')
            ->from('users_items')
            ->where('uid=:uid AND item_id < 1000', [':uid'=>$this->uid])
            ->order('skill DESC')
            ->limit(3)
            ->queryAll();
        foreach ($res as $dat) {
            $i = new Item;
            $i->item_type = Item::TYPE_ITEM;
            $i->id = $dat['item_id'];
            $i->fetch();
            $this->stats['items'][] = $i->title;
        }
    }

    protected function fetchBaits()
    {
        $res = Yii::app()->db->createCommand()
            ->select('item_id')
            ->from('users_baits')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->order('skill DESC')
            ->limit(3)
            ->queryAll();
        foreach ($res as $dat) {
            $i = new Item;
            $i->item_type = Item::TYPE_BAIT;
            $i->id = $dat['item_id'];
            $i->fetch();
            $this->stats['baits'][] = $i->title;
        }
    }

    protected function fetchSets()
    {
        foreach ($this->stats['setitems'] as $dat => $cnt) {
            $i = new Item;
            $i->item_type = Item::TYPE_ITEMSET;
            $i->id = $dat;
            $i->fetchSet();
            $this->stats['sets'][] = $i->title;
        }
    }

    protected function fetchRank()
    {
        $redis = Yii::app()->redis->getClient();

        $rank  = $redis->zRevRank('board_p:'.date('Ym'), $this->uid);
        $this->stats['rankActual'] = $rank === false ? false : ++$rank;

        $rank  = $redis->zRevRank('board_p:6month', $this->uid);
        $this->stats['rank'] = $rank === false ? false : ++$rank;
    }
}
