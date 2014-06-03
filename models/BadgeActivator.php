<?php
class BadgeActivator extends Badge
{
    public function triggerMaxNrg($max)
    {
        if ($max >= 35) {
            $this->activate('max_nrg_35');
        }
        
        if ($max >= 100) {
            $this->activate('max_nrg_100');
        }
    }
    
    public function triggerSkill($max)
    {
        if ($max >= 35) {
            $this->activate('skill_35');
        }
        
        if ($max >= 100) {
            $this->activate('skill_100');
        }
    }
    
    public function triggerStrength($max)
    {
        if ($max >= 35) {
            $this->activate('strength_35');
        }
        
        if ($max >= 100) {
            $this->activate('strength_100');
        }
    }

    public function triggerHer($uid, $id, $data = []) {
        $this->setUid($uid);
        return $this->trigger($id, $data);
    }

    public function trigger($id, $data = []) {
        //echo "{$this->_uid}:trigger({$id})\n";
        if (!$this->_uid) $this->setUid(Yii::app()->player->model->uid); //set default uid

        $activate = false;
        switch ($id) {
            case 'energy_drink': $activate = true; break;
            case 'level_10': if ($data['level'] >= 10) $activate = true; break;
            case 'level_100': if ($data['level'] >= 100) $activate = true; break;
            case 'dollar_50': if ($data['dollar'] >= 50) $activate = true; break;
            case 'dollar_5000': if ($data['dollar'] >= 5000) $activate = true; break;

            case 'travel_loc3': if ($data['water_id'] == 3) $activate = true; break;
            case 'travel_county2': if ($data['county_id'] == 2) $activate = true; break;
            case 'travel_county9': if ($data['county_id'] == 9) $activate = true; break;
            case 'routine_100': if ($data['routine'] >= 100) $activate = true; break;
            case 'loc_routine_4b': if ($data['water_id']==4 and $data['routine'] > 0) $activate = true; break;
            case 'loc_routine_13s': if ($data['water_id']==13 and $data['routine'] >= 3) $activate = true; break;
            case 'loc_routine_28s': if ($data['water_id']==28 and $data['routine'] >= 3) $activate = true; break;
            case 'loc_routine_37g': if ($data['water_id']==37 and $data['routine'] >= 9) $activate = true; break;
            case 'loc_routine_52b': if ($data['water_id']==52 and $data['routine'] > 0) $activate = true; break;
            case 'loc_routine_61s': if ($data['water_id']==61 and $data['routine'] >= 3) $activate = true; break;
            case 'loc_routine_71g': if ($data['water_id']==71 and $data['routine'] >= 9) $activate = true; break;
            case 'loc_routine_72e': if ($data['water_id']==72 and $data['routine'] >= 27) $activate = true; break;
            case 'loc_routine_46d': if ($data['water_id']==46 and $data['routine'] >= 81) $activate = true; break;
            case 'setpart_3': if ($data['cnt'] >= 3) $activate = true; break;
            case 'setpart_10': if ($data['cnt'] >= 10) $activate = true; break;
            case 'setpart_30': if ($data['cnt'] >= 30) $activate = true; break;
            case 'first_duel_win': if ($data['role'] == 'caller' and $data['winner'] == 'caller') $activate = true; break;
            case 'duel_success_100': if ($data['cnt'] >= 100) $activate = true; break;
            case 'duel_fail_100': if ($data['cnt'] >= 100) $activate = true; break;
            case 'duel_rate_40': if ($this->getSuccessRate(100, $data) <= 40) $activate = true; break;
            case 'duel_rate_25': if ($this->getSuccessRate(300, $data) <= 25) $activate = true; break;
            case 'duel_rate_10': if ($this->getSuccessRate(600, $data) <= 10) $activate = true; break;
            case 'duel_rate_60': if ($this->getSuccessRate(100, $data) >= 60) $activate = true; break;
            case 'duel_rate_75': if ($this->getSuccessRate(300, $data) >= 75) $activate = true; break;
            case 'duel_rate_90': if ($this->getSuccessRate(900, $data) >= 90) $activate = true; break;
            case 'duel_money_100': if ($data['dollar'] >= 100) $activate = true; break;
            case 'duel_money_1000': if ($data['dollar'] >= 1000) $activate = true; break;
            case 'duel_win_chance35': if ($data['winner'] and $data['chance'] <= 35) $activate = true; break;
            case 'duel_win_chance20': if ($data['winner'] and $data['chance'] <= 20) $activate = true; break;
            case 'duel_win_chance5': if ($data['winner'] and $data['chance'] <= 5) $activate = true; break;
            case 'duel_lose_chance65': if (!$data['winner'] and $data['chance'] >= 65) $activate = true; break;
            case 'duel_lose_chance80': if (!$data['winner'] and $data['chance'] >= 80) $activate = true; break;
            case 'duel_lose_chance95': if (!$data['winner'] and $data['chance'] >= 95) $activate = true; break;
            case 'duel_2h': if ($data['role'] == 'caller' and date('G')==2) $activate = true; break;
            case 'shop_item10': if (Yii::app()->player->model->owned_items >= 10) $activate = true; break;
            case 'shop_bait20': if (Yii::app()->player->model->owned_baits >= 20) $activate = true; break;
            case 'set_b': if ($data['id']==1) $activate = true; break;
            case 'set_s': if ($data['id']==2) $activate = true; break;
            case 'set_g': if ($data['id']==3) $activate = true; break;
            case 'set_sell_b': if ($data['id']==1) $activate = true; break;
            case 'set_sell_s': if ($data['id']==2) $activate = true; break;
            case 'set_sell_g': if ($data['id']==3) $activate = true; break;
            case 'club_join': $activate = true; break;
            case 'club_create': $activate = true; break;
            case 'club_members_8': if ($data['cnt'] >= 8) $activate = true; break;
            case 'login_days_7': if ($this->getLoginDays() >= 7) $activate = true; break;
            case 'login_days_30': if ($this->getLoginDays() >= 30) $activate = true; break;
            case 'login_days_60': if ($this->getLoginDays() >= 60) $activate = true; break;
            case 'win_contest': $activate = true; break;
        }

        if ($activate) {
           return $this->activate($id);
        }
        return false;
    }

    private function getSuccessRate($limit, $data) {
        $rate = 50;
        if ($data['success'] + $data['fail'] >= $limit) {
            if ($data['success'] or $data['fail']) {
                $rate = round( $data['success'] / (($data['success'] + $data['fail'])/100) ,1);
            }
        }
        return $rate;
    }
    private function getLoginDays() {
        $redis = Yii::app()->redis->getClient();
        $key = "counter:login:days:".$this->_uid;
        return (int)$redis->hGet($key, 'cnt');
    }

    private function activate($id) {
        $redis = Yii::app()->redis->getClient();
        $saved = $redis->sadd('badges:owned:'.$this->_uid, $id);
        if ($saved) {
            //save the actual timestamp
            $redis->zadd('badges:added:'.$this->_uid, time(), $id);

            $badge = $this->getBadge($id);

            $score = 1;
            if ($badge['level'] == self::LEVEL_SILVER) $score = 3;
            if ($badge['level'] == self::LEVEL_GOLD) $score = 9;

            $redis->zIncrBy("badges:leaderboard", $score, $this->_uid);

            $this->postToWall($badge);
        }
        return $saved;
    }

    private function postToWall($badge) {
        $wall = new Wall;
        $wall->content_type = Wall::TYPE_BADGE;
        $wall->uid = $this->_uid;
        $wall->add($badge);
    }
}
