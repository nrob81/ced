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

    public function triggerFirstDuelWin($role, $winner)
    {
        if ($role == 'caller' and $winner == 'caller') {
            $this->activate('first_duel_win');
        }
    }

    public function triggerDuelSuccess($cnt)
    {
        if ($cnt >= 100) {
            $this->activate('duel_success_100');
        }
    }
    
    public function triggerDuelFail($cnt)
    {
        if ($cnt >= 100) {
            $this->activate('duel_fail_100');
        }
    }

    public function triggerDuelRate($cntSuccess, $cntFail)
    {
        $mapMax = [
            ['limit'=>100, 'percent'=>40],
            ['limit'=>300, 'percent'=>25],
            ['limit'=>600, 'percent'=>10],
            ];
        foreach ($mapMax as $params) {
            if ($this->getSuccessRate($params['limit'], $cntSuccess, $cntFail) <= $params['percent']) {
                $this->activate('duel_rate_' . $params['percent']);
            }
        }
        
        $mapMin = [
            ['limit'=>100, 'percent'=>60],
            ['limit'=>300, 'percent'=>75],
            ['limit'=>900, 'percent'=>90],
            ];
        foreach ($mapMin as $params) {
            if ($this->getSuccessRate($params['limit'], $cntSuccess, $cntFail) >= $params['percent']) {
                $this->activate('duel_rate_' . $params['percent']);
            }
        }
    }

    public function triggerDuelMoney($dollar)
    {
        foreach ([100, 1000] as $limit) {
            if ($dollar >= $limit) {
                $this->activate('duel_money_' . $limit);
            }
        }
    }

    public function triggerDuelWinChance($isWinner, $chance)
    {
        if (!$isWinner) return false;

        foreach ([35, 20, 5] as $limit) {
            if ($chance <= $limit) {
                $this->activate('duel_win_chance' . $limit);
            }
        }
    }
    
    public function triggerDuelLoseChance($isWinner, $chance)
    {
        if ($isWinner) return false;

        foreach ([65, 80, 95] as $limit) {
            if ($chance >= $limit) {
                $this->activate('duel_lose_chance' . $limit);
            }
        }
    }

    public function triggerDuel2h($role)
    {
        if ($role == 'caller' and date('G') == 2) {
            $this->activate('duel_2h');
        }
    }

    public function triggerItems($cnt)
    {
        if ($cnt >= 10) {
            $this->activate('shop_item10');
        }
    }
    
    public function triggerBaits($cnt)
    {
        if ($cnt >= 20) {
            $this->activate('shop_bait20');
        }
    }

    public function triggerSet($id, $sold = false)
    {
        $key = $sold ? 'set_sell_': 'set_';

        foreach ([1=>'b', 2=>'s', 3=>'g'] as $search => $type) {
            if ($id == $search) {
                $this->activate($key . $type);
            }
        }
    }

    public function triggerClubMembers($cnt)
    {
        if ($cnt >= 8) {
            $this->activate('club_members_8');
        }
    }
    
    public function triggerLoginDays()
    {
        $cnt = $this->getLoginDays();
        foreach ([7, 30, 60] as $limit) {
            if ($cnt >= $limit) {
                $this->activate('login_days_' . $limit);
            }
        }
    }
    
    private function getSuccessRate($limit, $cntSuccess, $cntFail) {
        $rate = 50;
        if ($cntSuccess + $cntFail >= $limit) {
            $rate = round( $cntSuccess / (($cntSuccess + $cntFail)/100) ,1);
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
