<?php
/**
 * @property int $id
 * @property boolean $active
 * @property string $opponentLink
 * @property integer $caller
 * @property integer $opponent
 * @property string $name_caller
 * @property string $name_opponent
 * @property integer $cnt_won_caller
 * @property integer $cnt_won_opponent
 * @property integer $point_caller
 * @property integer $point_opponent
 * @property integer $loot_caller
 * @property integer $loot_opponent
 * @property integer $startTime
 * @property integer $endTime
 * @property array $listDuels
 * @property string $winner
 */
class Challenge extends CModel
{
    const TIME_LIMIT_HOURS = 8;
    const TIME_LIMIT_LASTCALL_HOURS = 4;

    private $id;
    private $active = false;
    private $caller;
    private $opponent;
    private $loot_caller;
    private $loot_opponent;
    private $cnt_won_caller;
    private $cnt_won_opponent;
    private $point_caller;
    private $point_opponent;
    private $name_caller;
    private $name_opponent;
    private $winner;
    private $created;
    private $listDuels = [];

    public function attributeNames()
    {
        return [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getOpponentLink($clubID)
    {
        $oppID = $clubID == $this->caller ? $this->opponent : $this->caller;
        $oppName = $clubID == $this->caller ? $this->name_opponent : $this->name_caller;

        return CHtml::link($oppName, ['club/details', 'id'=>$oppID], ['data-ajax'=>'false']);
    }

    public function getCaller()
    {
        return $this->caller;
    }

    public function getOpponent()
    {
        return $this->opponent;
    }

    public function getName_caller()
    {
        return $this->name_caller;
    }

    public function getName_opponent()
    {
        return $this->name_opponent;
    }

    public function getCnt_won_caller()
    {
        return $this->cnt_won_caller;
    }

    public function getCnt_won_opponent()
    {
        return $this->cnt_won_opponent;
    }

    public function getPoint_caller()
    {
        return $this->point_caller;
    }

    public function getPoint_opponent()
    {
        return $this->point_opponent;
    }

    public function getLoot_caller()
    {
        return $this->loot_caller;
    }

    public function getLoot_opponent()
    {
        return $this->loot_opponent;
    }

    public function getStartTime()
    {
        return strtotime($this->created) + 1800;
    }

    public function getEndTime()
    {
        return $this->startTime + 1800;
    }

    public function getListDuels()
    {
        return $this->listDuels;
    }

    public function getWinner()
    {
        return $this->winner;
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setCaller($clubID)
    {
        $this->caller = (int)$clubID;
    }

    public function setOpponent($clubID)
    {
        $this->opponent = (int)$clubID;
    }

    public function fetch()
    {
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('challenge')
            ->where('id=:id', [':id'=>$this->id])
            ->queryRow();
        if (!$res) {
            throw new CHttpException(404, 'A lekért verseny nem található.');
        }

        foreach ($res as $k => $v) {
            $this->$k = $v;
        }
        $this->active = !$this->winner;
    }

    public function fetchActiveChallenge()
    {
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('challenge')
            ->where('caller=:id OR opponent=:id', [':id'=>$this->opponent])
            ->order('created DESC')
            ->limit(1)
            ->queryRow();
        if (!$res) {
            return false;
        }

        foreach ($res as $k => $v) {
            $this->$k = $v;
        }
        $this->active = !$this->winner;
    }

    public function hasActiveChallenge($clubID)
    {
        $res = Yii::app()->db->createCommand()
            ->select('id, winner')
            ->from('challenge')
            ->where('caller=:id OR opponent=:id', [':id'=>(int)$clubID])
            ->order('created DESC')
            ->limit(1)
            ->queryRow();
        if ($res['id'] and !$res['winner']) {
            return true;
        }
        return false;
    }

    public function underCallTimeLimit($clubID, $opponentID)
    {
        $res = Yii::app()->db->createCommand()
            ->select('id, created')
            ->from('challenge')
            ->where('caller=:cid AND opponent=:oid', [':cid'=>(int)$clubID, ':oid'=>$opponentID])
            ->order('created DESC')
            ->limit(1)
            ->queryRow();
        if (time() - strtotime($res['created']) > (self::TIME_LIMIT_HOURS * 3600)) {
            return false;
        }
        return true;
    }

    public function underLastCallTimeLimit($clubID)
    {
        $res = Yii::app()->db->createCommand()
            ->select('id, created')
            ->from('challenge')
            ->where('caller=:cid', [':cid'=>(int)$clubID])
            ->order('created DESC')
            ->limit(1)
            ->queryRow();
        if (time() - strtotime($res['created']) > (self::TIME_LIMIT_LASTCALL_HOURS * 3600)) {
            return false;
        }
        return true;
    }

    public function callToChallenge($opponent)
    {
        $player = Yii::app()->player->model;

        //requirements
        if (!$player->in_club) {
            throw new CFlashException('Csak egy klub tagjaként vagy alapítójaként hívhatsz ki versenyre másik klubot.');
        }

        if ($this->active) {
            throw new CFlashException('Ez a klub már részt vesz egy másik versenyben.');
        }

        if (!$opponent->would_compete) {
            throw new CFlashException('Ez a klub nem szeretne versenyezni.');
        }

        if ($this->hasActiveChallenge($player->in_club)) {
            throw new CFlashException('A klubod már részt vesz egy versenyben.');
        }

        if ($this->underCallTimeLimit($player->in_club, $this->opponent)) {
            throw new CFlashException('Az elmúlt '. self::TIME_LIMIT_HOURS .' órában már kihívtátok ezt a klubot. Unalmas volna ilyen gyakran játszani ellenük. :)');
        }

        //caller club
        $callerClub = Yii::app()->db->createCommand()
            ->select('name, would_compete')
            ->from('club')
            ->where('id=:clubID', [':clubID'=>$player->in_club])
            ->queryRow();
        if (!$callerClub['would_compete']) {
            throw new CFlashException('Mielőtt versenyre hívsz egy klubot, kapcsold be a saját klubodban a \'versenyezne\' beállítást.');
        }

        Yii::app()->db->createCommand()
            ->insert('challenge', [
            'caller'=>$this->caller,
            'opponent'=>$this->opponent,
            'name_caller'=>$callerClub['name'],
            'name_opponent'=>$opponent->name,
            ]);
        //set properties
        $this->fetchActiveChallenge();

        $this->addCommandToStack([
            'id'=>$this->id,
            ]);

        //add reminder
        $this->addReminder();

        return true;
    }

    public function fetchListDuels()
    {
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('duel')
            ->where('challenge_id=:id', [':id'=>$this->id])
            ->order('id DESC')
            ->queryAll();
        $player = new Player;
        foreach ($res as $d) {
            $player->subjectId = $d['caller'];
            $d['name_caller'] = $player->getSubjectName();

            $player->subjectId = $d['opponent'];
            $d['name_opponent'] = $player->getSubjectName();

            $d['awards'] = $this->getAwards($d['id'], $d['winner']);

            $this->listDuels[] = $d;
        }
    }

    private function getAwards($id, $role)
    {
        $res = Yii::app()->db->createCommand()
            ->select('award_dollar, duel_points, club')
            ->from('duel_player')
            ->where('duel_id=:id AND role=:role', [':id'=>(int)$id, ':role'=>$role])
            ->queryRow();
        return $res;
    }

    private function addCommandToStack($params)
    {
        Yii::app()->db->createCommand()
            ->insert('command_stack', [
            'command'=>'endChallenge',
            'process_time'=>date('Y-m-d H:i:s', time()+3600), //1800+1800
            'params'=>CJSON::encode($params)
            ]);
    }

    private function addReminder()
    {
        $redis = Yii::app()->redis->getClient();

        $redis->set('reminder:challenge:'.$this->caller, time()+3600);
        $redis->set('reminder:challenge:'.$this->opponent, time()+3600);
    }
}
