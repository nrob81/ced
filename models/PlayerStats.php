<?php
class PlayerStats extends CModel
{
    private $uid;
    private $stats = [];

    public function attributeNames() {
        return [];
    }

    public function getStats() { return $this->stats; }

    public function setUid($uid) {
        $this->uid = (int)$uid;
    }

    public function fetchStats() {
        $s = [
            'visited_waters'=>0,
            'visited_counties'=>0,
            'owned_setitems'=>0,
            'setitems'=>[],
            'items'=>[],
            'baits'=>[],
            'sets'=>[],
            'counties'=>[],
            ];       

        //mission counter
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('users_missions')
            ->where('uid=:uid', [':uid'=>$this->uid])
            ->queryScalar();
        $s['completed_missions'] = $res;

        //water,county counter
        $res = Yii::app()->db->createCommand()
            ->select('v.*, w.county_id, w.title')
            ->from('visited v')
            ->join('waters w', 'v.water_id=w.id')
            ->where('v.uid=:uid', [':uid'=>$this->uid])
            ->order('v.water_id')
            ->queryAll();
        foreach ($res as $dat) {
            $s['routine'][$dat['water_id']] = $dat['routine'];
            $s['visited_waters']++;
            if (!array_key_exists($dat['county_id'], $s['counties'])) $s['visited_counties']++;
            $s['counties'][$dat['county_id']] = 1;
        }

        $logger = new Logger; 
        $logger->uid = $this->uid;
        $stat = $logger->getCounters();

        //duels
        $s['duel_success'] = @(int)$stat['duel_success'];
        $s['duel_fail'] = @(int)$stat['duel_fail'];
        $s['duel_rate'] = '?';
        if ($s['duel_success'] or $s['duel_fail']) {
            $s['duel_rate'] = round( $s['duel_success'] / (($s['duel_success'] + $s['duel_fail'])/100) ,1);
        }

        //sets
        $res = Yii::app()->db->createCommand()
            ->select('item_id, item_count')
            ->from('users_items')
            ->where('uid=:uid AND item_id>999 AND item_count>0', [':uid'=>$this->uid])
            ->order('skill DESC, item_id DESC')
            ->queryAll();
        $best = 0;
        foreach ($res as $dat) {
            if ($best < 3 and $dat['item_count']) {
                $s['setitems'][$dat['item_id']] = $dat['item_count'];
                $best++;
            }
            $s['owned_setitems'] += $dat['item_count'];
        }

        //best items/baits/sets
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
            $s['items'][] = $i->title;
        }

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
            $s['baits'][] = $i->title;
        }

        foreach ($s['setitems'] as $dat => $cnt) {
            $i = new Item;
            $i->item_type = Item::TYPE_ITEMSET;
            $i->id = $dat;
            $i->fetchSet();
            $s['sets'][] = $i->title;
        }

        $redis = Yii::app()->redis->getClient();

        $rank  = $redis->zRevRank('board_p:'.date('Ym'), $this->uid);
        //var_dump($rank);
        $s['rankActual'] = $rank === false ? false : ++$rank;

        $rank  = $redis->zRevRank('board_p:6month', $this->uid);
        $s['rank'] = $rank === false ? false : ++$rank;

        $this->stats = $s;
    }
}
