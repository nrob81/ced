<?php
class Competitor extends CModel
{
    protected $uid;
    protected $skill;
    protected $chance;
    protected $energy;
    protected $avgEnergy;
    protected $dollar;
    protected $club;

    protected $isCaller = false;
    protected $winner = false;

    protected $duelId;
    protected $opponent;

    //reqs, awards
    protected $reqEnergy = 0;
    protected $reqDollar = 0;
    protected $awardXp = 0;
    protected $awardDollar = 0;
    protected $awardPoints = 0;

    protected $mapReqs = ['energy'=>'reqEnergy', 'dollar'=>'reqDollar'];
    protected $mapAwards = ['xp_all'=>'awardXp', 'xp_delta'=>'awardXp', 'dollar'=>'awardDollar', 'duel_points'=>'awardPoints'];

    public function attributeNames()
    {
        return [];
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getSkill()
    {
        return $this->skill;
    }

    public function getChance()
    {
        return $this->chance;
    }

    public function getEnergy()
    {
        return $this->energy;
    }

    public function getDollar()
    {
        return $this->dollar;
    }
    public function getIsCaller()
    {
        return $this->isCaller;
    }
    public function getWinner()
    {
        return $this->winner;
    }

    public function getOpponent()
    {
        return $this->opponent;
    }

    public function getAvgEnergy()
    {
        return $this->avgEnergy;
    }
    public function getReqEnergy()
    {
        return $this->reqEnergy;
    }
    public function getReqDollar()
    {
        return $this->reqDollar;
    }
    public function getAwardXp()
    {
        return $this->awardXp;
    }
    public function getAwardDollar()
    {
        return $this->awardDollar;
    }
    public function getAwardPoints()
    {
        return $this->awardPoints;
    }
    public function getClub()
    {
        return $this->club;
    }


    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    public function setSkill($skill)
    {
        $this->skill = (int)$skill;
    }

    public function setChance($chance)
    {
        $this->chance = (int)$chance;
    }

    public function setEnergy($energy)
    {
        $this->energy = (int)$energy;
    }
    public function setAvgEnergy($avgEnergy)
    {
        $this->avgEnergy = (int)$avgEnergy;
    }

    public function setDollar($dollar)
    {
        $this->dollar = (int)$dollar;
    }
    public function setIsCaller($isCaller)
    {
        $this->isCaller = (bool)$isCaller;
    }
    public function setWinner($isWinner)
    {
        $this->winner = (int)$isWinner;
    }

    public function setDuelId($id)
    {
        $this->duelId = (int)$id;
    }
    public function setOpponent($opponent)
    {
        $this->opponent = $opponent;
    }
    public function setClub($club)
    {
        $this->club = $club;
    }

    public function fetchFromLog($duelId)
    {
        $role = $this->isCaller ? 'caller' : 'opponent';

        $res = Yii::app()->db->createCommand()
            ->select('dp.*, m.user')
            ->from('duel_player dp')
            ->join('main m', 'dp.uid=m.uid')
            ->where('dp.duel_id = :id AND dp.role=:role', [':id'=>$duelId, ':role'=>$role])
            ->queryRow();
        
        $this->skill = $res['skill'];
        $this->chance = $res['chance'];
        $this->energy = $res['energy'];
        $this->dollar = $res['dollar'];
        $this->reqEnergy = $res['req_energy'];
        $this->reqDollar = $res['req_dollar'];
        $this->awardXp = $res['award_xp'];
        $this->awardDollar = $res['award_dollar'];
        $this->awardPoints = $res['duel_points'];
        $this->winner = $res['winner'];
        $this->club = $res['club'];
    }
    public function play($isWinner)
    {
        $this->winner = $isWinner;

        if ($this->winner) {
            $this->winPrize();
            $this->updateLeaderboard();
        } else {
            $this->losePrize();
        }
        //print_r($this);
    }

    public function finish($player)
    {
        $this->updateAttributes($player);

        $contest = new Contest;
        $contest->addPoints($this->uid, Contest::ACT_DUEL, $this->reqEnergy, $this->awardXp, $this->awardDollar);

        $this->log();
        $stat = $this->incrementCounters($player);
        $this->addBadges($stat);

        if (!$this->isCaller) {
            $this->sendWallMessage();
        }
    }
    protected function updateAttributes($player)
    {
        $player->updateAttributes(
            $this->membersToArray($this->mapAwards), 
            $this->membersToArray($this->mapReqs)
        );
    }

    protected function membersToArray($map)
    {
        $result = [];
        foreach ($map as $sql => $member) {
            if ($this->$member) {
                $result[$sql] = $this->$member;
            }
        }
        //print_r($result);
        return $result;
    }

    public function resetAwards()
    {
        $this->reqEnergy = 0;
        $this->reqDollar = 0;
        $this->awardXp = 0;
        $this->awardDollar = 0;
        $this->awardPoints = 0;
    } 

    protected function winPrize()
    {
        $this->reqEnergy = $this->energy;
        $this->awardXp = round($this->avgEnergy * ($this->opponent['chance'] / 100));
        $this->awardDollar = round($this->opponent['dollar'] * ($this->opponent['chance'] / 100));
        $this->awardPoints = round($this->avgEnergy * ($this->compensator($this->opponent['chance']) / 100));
    }

    protected function losePrize()
    {
        $this->reqEnergy = $this->energy;
        $this->reqDollar = round($this->dollar * $this->chance / 100);
        $this->awardXp = round($this->avgEnergy * ($this->chance / 100) / 5); //20% of winners prize            
    }

    public function compensator($value)
    {
        if ($value < 33) $value = 33;
        if ($value > 66) $value = 66;
        return $value;
    }

    protected function updateLeaderboard()
    {
        if (!$this->awardPoints) {
            return false;
        }

        Yii::app()->redis->getClient()->zIncrBy('board_p:'.date('Ym'), $this->awardPoints, $this->uid);
        return true;
    }

    protected function log()
    {
        $role = $this->isCaller ? 'caller' : 'opponent';

        $parameters = [
            'duel_id'=>$this->duelId,
            'role'=>$role,
            'uid'=>$this->uid,
            'skill'=>$this->skill,
            'chance'=>$this->chance,
            'energy'=>$this->energy,
            'dollar'=>$this->dollar,
            'req_energy'=>$this->reqEnergy,
            'req_dollar'=>$this->reqDollar,
            'award_xp'=>$this->awardXp,
            'award_dollar'=>$this->awardDollar,
            'duel_points'=>$this->awardPoints,
            'winner'=>(int)$this->winner,
            'club'=>$this->club
            ];

        Yii::app()->db->createCommand()->insert('duel_player', $parameters);
    }

    protected function incrementCounters($player)
    {
        //log mission counter
        $cell = 'duel_' . ($this->winner ? 'success' : 'fail');
        $logger = new Logger;
        $logger->uid = $this->uid;
        $logger->level = $player->level;
        $logger->increment($cell, 1);
        return $logger->getCounters();
    }

    protected function addBadges($stat)
    {
        $role = $this->isCaller ? 'caller' : 'opponent';
        $b = Yii::app()->badge->model;

        $b->triggerHer($this->uid, 'first_duel_win', ['winner'=>$this->winner, 'role'=>$role]);
        $b->triggerHer($this->uid, 'duel_success_100', ['cnt'=>(int)@$stat['duel_success']]);
        $b->triggerHer($this->uid, 'duel_fail_100', ['cnt'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_rate_10', ['success'=>(int)@$stat['duel_success'], 'fail'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_rate_25', ['success'=>(int)@$stat['duel_success'], 'fail'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_rate_40', ['success'=>(int)@$stat['duel_success'], 'fail'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_rate_60', ['success'=>(int)@$stat['duel_success'], 'fail'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_rate_75', ['success'=>(int)@$stat['duel_success'], 'fail'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_rate_90', ['success'=>(int)@$stat['duel_success'], 'fail'=>(int)@$stat['duel_fail']]);
        $b->triggerHer($this->uid, 'duel_money_100', ['dollar'=>$this->awardDollar]);
        $b->triggerHer($this->uid, 'duel_money_1000', ['dollar'=>$this->awardDollar]);

        $b->triggerHer($this->uid, 'duel_win_chance35', ['winner'=>(int)$this->winner, 'chance'=>$this->chance]);
        $b->triggerHer($this->uid, 'duel_win_chance20', ['winner'=>(int)$this->winner, 'chance'=>$this->chance]);
        $b->triggerHer($this->uid, 'duel_win_chance5', ['winner'=>(int)$this->winner, 'chance'=>$this->chance]);
        $b->triggerHer($this->uid, 'duel_lose_chance65', ['winner'=>(int)$this->winner, 'chance'=>$this->chance]);
        $b->triggerHer($this->uid, 'duel_lose_chance80', ['winner'=>(int)$this->winner, 'chance'=>$this->chance]);
        $b->triggerHer($this->uid, 'duel_lose_chance95', ['winner'=>(int)$this->winner, 'chance'=>$this->chance]);
        $b->triggerHer($this->uid, 'duel_2h', ['role'=>$role]);
    }

    protected function sendWallMessage()
    {
        $wall = new Wall;
        $wall->content_type = Wall::TYPE_DUEL;
        $wall->uid = $this->uid;

        $wall->add([
            'duel_id'=>$this->duelId,
            'caller_uid'=>$this->opponent['uid'],
            'caller_user'=>$this->opponent['user'],
            'req_energy'=>$this->reqEnergy,
            'req_dollar'=>$this->reqDollar,
            'award_xp'=>$this->awardXp,
            'award_dollar'=>$this->awardDollar,
            'winner'=>$this->winner
            ]);
    }
}
