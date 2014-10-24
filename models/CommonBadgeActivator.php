<?php
class CommonBadgeActivator extends BadgeActivator
{
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
            if ($id == $params[0] && $routine >= $params[1]) {
                $this->activate('loc_routine_' . $key);
            }
        }
    }

    public function triggerSimple($id)
    {
        $activate = false;
        switch ($id) {
            case 'energy_drink':
                $activate = true;
                break;
            case 'win_contest':
                $activate = true;
                break;
            case 'club_join':
                $activate = true;
                break;
            case 'club_create':
                $activate = true;
                break;
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

        foreach ([1 => 'b', 2 => 's', 3 => 'g'] as $search => $type) {
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

    private function getLoginDays()
    {
        $redis = Yii::app()->redis->getClient();
        $key = "counter:login:days:".$this->uid;
        return (int)$redis->hGet($key, 'cnt');
    }
}
