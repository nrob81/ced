<?php
/**
 * @property integer $caller
 * @property integer $opponent
 * @property boolean $isChallenge
 * @property boolean $played
 * @property array $competitors
 */
class Duel extends CModel
{
    const LIMIT_WEAKER_OPPONENT_LEVEL_DIFF = 5;
    const REQ_LEVEL = 10;

    private $caller;
    private $opponent;
    private $competitors = [];
    private $callerDuelShieldLifeTime = false;

    private $challengeID = 0;
    private $callersClub = '';
    private $callersClubRole = '';
    private $opponentsClub = '';
    private $opponentsClubRole = '';

    public function attributeNames()
    {
        return [];
    }

    public function getOpponent()
    {
        return $this->opponent;
    }

    public function getCaller()
    {
        return $this->caller;
    }

    public function getIsChallenge()
    {
        return $this->challengeID > 0;
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
        $this->caller = $this->getPlayerModel($id);
        $duelShiel = new DuelShield();
        $duelShiel->uid = $id;
        $this->callerDuelShieldLifeTime = $duelShiel->lifeTime;
    }

    public function setOpponent($id)
    {
        $this->opponent = $this->getPlayerModel($id);
        $duelShiel = new DuelShield();
        $duelShiel->uid = $id;
        if ($duelShiel->lifeTime > 0) {
            $this->opponent->activateDuelShield();
        }
    }

    private function getPlayerModel($id)
    {
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
        $c = $this->caller->in_club;
        $o = $this->opponent->in_club;

        if (!$c || !$o) {
            return false;
        }

        $last = Yii::app()->db->createCommand()
            ->select('*')
            ->from('challenge')
            ->where('(caller=:caller OR caller=:opponent) AND winner=0', [':caller'=>$c, ':opponent'=>$o])
            ->limit(1)
            ->queryRow();

        if ($last['caller']==$c && $last['opponent']==$o || $last['caller']==$o && $last['opponent']==$c) {
            $this->setClubAttributes($last);
        }
    }

    private function setClubAttributes($challenge)
    {
        $created = strtotime($challenge['created']);

        if ($this->isBetweenDates($created + 1800, $created + 3600)) {
            $this->challengeID = (int)$challenge['id'];

            $this->callersClubRole = $challenge['caller'] == $this->caller->in_club ? 'caller' : 'opponent';
            $this->callersClub = $challenge['caller'] == $this->caller->in_club ? $challenge['name_caller'] : $challenge['name_opponent'];
            
            $this->opponentsClubRole = $challenge['opponent'] == $this->opponent->in_club ? 'opponent' : 'caller';
            $this->opponentsClub = $challenge['opponent'] == $this->opponent->in_club ? $challenge['name_opponent'] : $challenge['name_caller'];
        }
    }

    /**
     * @param integer $start
     * @param integer $end
     */
    private function isBetweenDates($start, $end)
    {
        $now = time();
        return ($now >= $start && $now <= $end);
    }

    public function validate()
    {
        if (!$this->opponent->uid) {
            throw new CFlashException('Az ellenfél nem létezik.');
        }

        if ($this->opponent->uid == $this->caller->uid) {
            throw new CFlashException('Magad ellen nem párbajozhatsz.');
        }

        if ($this->callerDuelShieldLifeTime > 0) {
            throw new CFlashException('Be van kapcsolva a párbaj-pajzsod, így nem hívhatsz párbajra másokat.');
        }

        Yii::trace('check: caller - energyRequiredForDuel');
        if ($this->caller->energy < $this->caller->energyRequiredForDuel) {
            throw new CFlashException('Ahhoz, hogy párbajozhass, legalább ' . $this->caller->energyRequiredForDuel . ' energiára van szükséged.');
        }

        if ($this->opponent->level < self::REQ_LEVEL) {
            throw new CFlashException('Az ellenfél még nem párbajozhat, mivel nem érte el a szükséges ' . self::REQ_LEVEL.'. szintet.');
        }

        $this->validateNonChallengeGame();

        return true;
    }

    private function validateNonChallengeGame()
    {
        if ($this->isChallenge) {
            return true;
        }

        if ($this->opponent->level < Yii::app()->player->model->level - self::LIMIT_WEAKER_OPPONENT_LEVEL_DIFF) {
            if (!$this->isRevenge()) {
                throw new CFlashException('Az ellenfél gyengébb nálad a megengedettnél (5 szint).');
            }
        }

        Yii::trace('check: opponent - energyRequiredForDuel');
        if ($this->opponent->energy < $this->opponent->energyRequiredForDuel) {
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

        $c->uid = $this->caller->uid;
        $o->uid = $this->opponent->uid;

        $c->skill = $this->caller->skill_extended;
        $o->skill = $this->opponent->skill_extended;

        $sumSkill = $c->skill + $o->skill;
        $c->chance = round($c->skill / ($sumSkill / 100));
        $c->chance = min($c->chance, 99); //max 99
        $o->chance = 100 - $c->chance;

        $c->energy = $this->caller->energyRequiredForDuel;
        $o->energy = min($this->opponent->energyRequiredForDuel, $this->opponent->energy);
        
        $avgEnergy = round(($c->energy + $o->energy) / 2);
        $c->avgEnergy = $avgEnergy;
        $o->avgEnergy = $avgEnergy;

        $c->dollar = round($this->caller->dollar / 10);
        $o->dollar = round($this->opponent->dollar / 10);

        $c->club = $this->callersClub;
        $o->club = $this->opponentsClub;

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
            'user'=>$this->caller->user //only for wall messages
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
        $this->competitors[0]->finish($this->caller);
        $this->competitors[1]->finish($this->opponent);

        if ($this->isChallenge) {
            $this->updateWinnerClub();
        }
    }

    /**
     * @param integer $duelId
     */
    public function replay($duelId)
    {
        //fetch duel data
        $duel = Yii::app()->db->createCommand()
            ->select('*')
            ->from('duel')
            ->where('id = :id', [':id'=>$duelId])
            ->queryRow();
        if (!$duel['id']) {
            throw new CFlashException('A lekért párbaj nem található.');
        }

        if (Yii::app()->player->uid != $duel['caller'] && Yii::app()->player->uid != $duel['opponent']) {
            throw new CFlashException('A lekért párbajt mások játszották.');
        }
        
        $this->challengeID = (int)$duel['challenge_id'];
        
        $this->setCaller($duel['caller']);
        $this->setOpponent($duel['opponent']);

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
            ->insert(
                'duel',
                [
                'winner'=>$this->competitors[0]->winner ? 'caller' : 'opponent',
                'caller'=>$this->caller->uid,
                'opponent'=>$this->opponent->uid,
                'challenge_id'=>$this->challengeID
                ]
            );

        $duelId = Yii::app()->db->getLastInsertID();
        $this->competitors[0]->duelId = $duelId;
        $this->competitors[1]->duelId = $duelId;
    }

    private function updateWinnerClub()
    {
        $tag = $this->callersClubRole;
        $winner = $this->competitors[0];
        if ($this->competitors[1]->winner) {
            $tag = $this->opponentsClubRole;
            $winner = $this->competitors[1];
        }

        Yii::app()->db->createCommand("UPDATE challenge SET cnt_won_{$tag}=cnt_won_{$tag}+1, loot_{$tag}=loot_{$tag}+{$winner->lootDollar}, point_{$tag}=point_{$tag}+{$winner->awardPoints} WHERE id={$this->challengeID}")->execute();

    }

    private function isRevenge()
    {
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('duel')
            ->where(
                'caller=:caller AND opponent=:opponent AND created > DATE_SUB(NOW(), INTERVAL 12 hour)',
                [':caller'=>$this->opponent->uid, ':opponent'=>$this->caller->uid]
            )
            ->queryScalar();
        return (boolean)($res > 0);
    }

    private function duelsInLastHour()
    {
        $res = Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('duel')
            ->where(
                'caller=:caller AND opponent=:opponent AND created > DATE_SUB(NOW(), INTERVAL 1 hour)',
                [':caller'=>$this->caller->uid, ':opponent'=>$this->opponent->uid]
            )
            ->queryScalar();
        return (int)$res;
    }
}
