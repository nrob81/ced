<?php
class Duel extends CModel
{
    const LIMIT_WEAKER_OPPONENT_LEVEL_DIFF = 5;
    const REQ_LEVEL = 10;

    private $_caller;
    private $_opponent;
    private $competitors = [];

    private $_skill;
    private $_chance;
    private $_energy;
    private $_dollar;
    private $_random;
    private $_winner;
    private $_loser;

    private $_logAttributes = [];

    private $_challengeID = 0;
    private $_callersClub = '';
    private $_callersClubRole = '';
    private $_opponentsClub = '';
    private $_opponentsClubRole = '';

    public function attributeNames()
    {
        return [];
    }




    public function getOpponent()
    {
        return $this->_opponent;
    }

    public function getCaller()
    {
        return $this->_caller;
    }

    public function getIsChallenge()
    {
        return $this->_challengeID > 0;
    }

    public function getPlayed()
    {
        if (!isset($this->competitors[1])) {
            return false;
        }

        return ($this->competitors[0]->winner | $this->competitors[1]->winner);
    }

    public function getCompetitors()
    {
        return $this->competitors;
    }





    public function setCaller($id)
    {
        $this->_caller = $this->getPlayerModel($id);
    }

    public function setOpponent($id)
    {
        $this->_opponent = $this->getPlayerModel($id);
    }

    private function getPlayerModel($id) {
        if ($id == Yii::app()->player->uid) {
            return Yii::app()->player->model;
        }

        $player = new Player();
        if ($id) {
            $player->setAllAttributes($id);
        }

        return $player;
    }




    public function fetchClubChallengeState()
    {
        $c = $this->_caller->in_club;
        $o = $this->_opponent->in_club;

        if (!$c || !$o) {
            return false;
        }

        $last = Yii::app()->db->createCommand()
            ->select('*')
            ->from('challenge')
            ->where('(caller=:caller OR caller=:opponent) AND winner=0', [':caller'=>$c,':opponent'=>$o])
            ->limit(1)
            ->queryRow();

        if ($last['caller']==$c and $last['opponent']==$o or $last['caller']==$o and $last['opponent']==$c) {
            $created = strtotime($last['created']);

            if ($this->isBetweenDates($created + 1800, $created + 3600)) {
                $this->_challengeID = (int)$last['id'];
                $this->_callersClubRole = $last['caller'] == $c ? 'caller' : 'opponent';
                $this->_callersClub = $last['caller'] == $c ? $last['name_caller'] : $last['name_opponent'];
                $this->_opponentsClubRole = $last['opponent'] == $o ? 'opponent' : 'caller';
                $this->_opponentsClub = $last['opponent'] == $o ? $last['name_opponent'] : $last['name_caller'];
            }
        }
    }

    private function isBetweenDates($start, $end)
    {
        $now = time();
        return ($now >= $start && $now <= $end);
    }

    public function validate()
    {
        if (!$this->_opponent->uid) {
            throw new CFlashException('Az ellenfél nem létezik.');
        }

        if ($this->_opponent->uid == $this->_caller->uid) {
            throw new CFlashException('Magad ellen nem párbajozhatsz.');
        }

        if ($this->_caller->energy < $this->_caller->energyRequiredForDuel) {
            throw new CFlashException('Ahhoz, hogy párbajozhass, legalább ' . $this->caller->energyRequiredForDuel . ' energiára van szükséged.');
        }

        if ($this->_opponent->level < self::REQ_LEVEL) {
            throw new CFlashException('Az ellenfél még nem párbajozhat, mivel nem érte el a szükséges ' . self::REQ_LEVEL.'. szintet.');
        }

        $this->validateNonChallengeGame();

        return true;
    }

    private function validateNonChallengeGame()
    {
        if ($this->isChallenge) return true;

        if ($this->_opponent->level < Yii::app()->player->model->level - self::LIMIT_WEAKER_OPPONENT_LEVEL_DIFF) {
            if (!$this->isRevenge()) {
                throw new CFlashException('Az ellenfél gyengébb nálad a megengedettnél (5 szint).');
            }
        }

        if ($this->_opponent->energy < $this->_opponent->energyRequiredForDuel) {
            throw new CFlashException('Az ellenfélnek nincs elég energiája a párbajhoz.');
        }

        if ($this->duelsInLastHour() >= 3) {
            throw new CFlashException('Egy adott játékost max. 3x hívhatsz párbajra egy órán keresztül. Kérlek válassz másik ellenfelet.');
        }

        return true;
    }

    private function createCompetitors() 
    {
        if ($this->isChallenge) {
            $c = new ClubCompetitor();
            $o = new ClubCompetitor();
        } else {
            $c = new Competitor();
            $o = new Competitor();
        }

        $c->uid = $this->_caller->uid;
        $o->uid = $this->_opponent->uid;

        $c->skill = $this->_caller->skill_extended;
        $o->skill = $this->_opponent->skill_extended;

        $sumSkill = $c->skill + $o->skill;
        $c->chance = round($c->skill / ($sumSkill / 100));
        $c->chance = min($c->chance, 99); //max 99
        $o->chance = 100 - $c->chance;

        $c->energy = $this->_caller->energyRequiredForDuel;
        $o->energy = min($this->_opponent->energyRequiredForDuel, $this->_opponent->energy);
        
        $avgEnergy = round(($c->energy + $o->energy) / 2);
        $c->avgEnergy = $avgEnergy;
        $o->avgEnergy = $avgEnergy;

        $c->dollar = round($this->_caller->dollar / 10);
        $o->dollar = round($this->_opponent->dollar / 10);

        $c->club = $this->_callersClub;
        $o->club = $this->_opponentsClub;

        $c->opponent = [
            'chance'=>$o->chance, 
            'dollar'=>$o->dollar,
            'energy'=>$o->energy,
            ];
        $o->opponent = [
            'chance'=>$c->chance, 
            'dollar'=>$c->dollar,
            'energy'=>$c->energy,
            'uid'=>$c->uid, //only for wall messages
            'user'=>$this->_caller->user //only for wall messages
            ];

        $c->isCaller = true;

        $this->competitors[] = $c;
        $this->competitors[] = $o;
    }

    public function play()
    {
        $this->createCompetitors();

        //play
        $rnd = rand(1, 100);
        $winnersId = 1; //opponent
        if ($rnd <= $this->competitors[0]->chance) {
            $winnersId = 0; //caller
        }

        $this->competitors[0]->play(0==$winnersId);
        $this->competitors[1]->play(1==$winnersId);

        $this->log();
        $this->competitors[0]->finish($this->_caller);
        $this->competitors[1]->finish($this->_opponent);

        if ($this->isChallenge) {
            $this->updateWinnerClub();
        }
    }

    public function replay($duelId)
    {
        //fetch duel data
        $duel = Yii::app()->db->createCommand()
            ->select('*')
            ->from('duel')
            ->where('id = :id', [':id'=>$duelId])
            ->queryRow();
        if (!$duel['id']) throw new CFlashException('A lekért párbaj nem található.');

        if (Yii::app()->player->uid != $duel['caller'] && Yii::app()->player->uid != $duel['opponent']) {
            throw new CFlashException('A lekért párbajt mások játszották.');
        }
        
        $this->_challengeID = (int)$duel['challenge_id'];
        
        $this->caller = $duel['caller'];
        $this->opponent = $duel['opponent'];

        if ($this->isChallenge) {
            $c = new ClubCompetitor();
            $o = new ClubCompetitor();
        } else {
            $c = new Competitor();
            $o = new Competitor();
        }

        $c->uid = $duel['caller'];
        $c->isCaller = true;
        $c->fetchFromLog($duelId);
        $this->competitors[] = $c;

        $o->uid = $duel['opponent'];        
        $o->fetchFromLog($duelId);
        $this->competitors[] = $o;
    }

    private function log()
    {
        //insert the duel data
        Yii::app()->db->createCommand()
            ->insert('duel', [
            'winner'=>$this->competitors[0]->winner ? 'caller' : 'opponent',
            'caller'=>$this->_caller->uid,
            'opponent'=>$this->_opponent->uid,
            'challenge_id'=>$this->_challengeID
            ]);

        $duelId = Yii::app()->db->getLastInsertID();
        $this->competitors[0]->duelId = $duelId;
        $this->competitors[1]->duelId = $duelId;
    }

    private function updateWinnerClub()
    {
        $tag = $this->_callersClubRole;
        $winner = $this->competitors[0];
        if ($this->competitors[1]->winner) {
            $tag = $this->_opponentsClubRole;
            $winner = $this->competitors[1];
        }

        Yii::app()->db->createCommand("UPDATE challenge SET cnt_won_{$tag}=cnt_won_{$tag}+1, loot_{$tag}=loot_{$tag}+{$winner->lootDollar}, point_{$tag}=point_{$tag}+{$winner->awardPoints} WHERE id={$this->_challengeID}")->execute();

    }

    private function isRevenge()
    {
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('duel')
            ->where('caller=:caller AND opponent=:opponent AND created > DATE_SUB(NOW(), INTERVAL 12 hour)',
                [':caller'=>$this->_opponent->uid, ':opponent'=>$this->_caller->uid])
                ->queryScalar();
        return (boolean)($res > 0);
    }

    private function duelsInLastHour()
    {
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('duel')
            ->where('caller=:caller AND opponent=:opponent AND created > DATE_SUB(NOW(), INTERVAL 1 hour)',
                [':caller'=>$this->_caller->uid, ':opponent'=>$this->_opponent->uid])
                ->queryScalar();
        return (int)$res;
    }
}
