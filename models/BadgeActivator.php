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

    public function triggerDollar($uid, $dollar)
    {
        $this->setUid($uid);
        if ($dollar >= 50) {
            $this->activate('dollar_50');
        }
        if ($dollar >= 5000){
            $this->activate('dollar_5000');
        }
    }
    
    public function triggerLevel($uid, $level)
    {
        $this->setUid($uid);
        if ($level >= 10) {
            $this->activate('level_10');
        }
        if ($level >= 100){
            $this->activate('level_100');
        }
    }

    public function triggerTravel($id)
    {
        if ($id == 3) {
            $this->activate('travel_loc3');
        }

        if ($id == 5) {
            $this->activate('travel_county2');
        }

        if ($id == 33) {
            $this->activate('travel_county9');
        }   
    }
    
    public function triggerLocationRoutine($id, $routine)
    {
        $map = [
            '4b' => [4, 1],
            '13s' => [13, 3],
            '28s' => [28, 3],
            '37g' => [37, 9],
            '52b' => [52, 1],
            '61s' => [61, 3],
            '71g' => [71, 9],
            '72e' => [72, 27],
            '46d' => [46, 81]
            ];
        foreach ($map as $key => $params) {
            if ($id == $params[0] and $routine >= $params[1]) {
                $this->activate('loc_routine_' . $key);
            }
        }
    }

    public function triggerSimple($id)
    {
        $activate = false;
        switch ($id) {
            case 'energy_drink': $activate = true; break;
            case 'win_contest': $activate = true; break;
            case 'club_join': $activate = true; break;
            case 'club_create': $activate = true; break;
        }
        
        if ($activate) {
           $this->activate($id);
        }
    }
    
    public function triggerRoutine($routine)
    {
        if ($routine >= 100) {
            $this->activate('routine_100');
        }
    }
    public function triggerSetPart($part)
    {
        foreach ([3, 10, 30] as $cnt) {
            if ($part >= $cnt) {
                $this->activate('setpart_' . $cnt);
            }
        }
    }

    public function triggerHer($uid, $id, $data = []) {
        $this->setUid($uid);
        return $this->trigger($id, $data);
    }

    public function trigger($id, $data = []) {
        if (!$this->_uid) $this->setUid(Yii::app()->player->model->uid); //set default uid

        $activate = false;
        switch ($id) {
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
            case 'club_members_8': if ($data['cnt'] >= 8) $activate = true; break;
            case 'login_days_7': if ($this->getLoginDays() >= 7) $activate = true; break;
            case 'login_days_30': if ($this->getLoginDays() >= 30) $activate = true; break;
            case 'login_days_60': if ($this->getLoginDays() >= 60) $activate = true; break;
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
        echo "active: $this->uid:$id:$saved \n";
        return $saved;
    }

    private function postToWall($badge) {
        $wall = new Wall;
        $wall->content_type = Wall::TYPE_BADGE;
        $wall->uid = $this->_uid;
        $wall->add($badge);
    }
}
