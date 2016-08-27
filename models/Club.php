<?php
/**
 * @property integer $id
 * @property integer $owner
 * @property string $ownerName
 * @property string $name
 * @property integer $would_compete
 * @property string $created
 * @property CPagination $pagination
 * @property integer $count
 * @property array $items
 * @property integer $page
 * @property array $members
 * @property array $entrants
 * @property array $challenges
 * @property integer $rank
 * @property integer $rankActual
 */
class Club extends CModel implements ISubject
{
    private $id;
    private $owner;
    private $ownerName;
    private $name;
    private $would_compete;
    private $created;
    private $items = [];
    private $page = 0;
    private $pagination;
    private $count;
    private $members = [];
    private $entrants = [];
    private $challenges = [];

    public function attributeNames()
    {
        return [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getOwnerName()
    {
        return $this->ownerName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWould_compete()
    {
        return (int)$this->would_compete;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getPagination()
    {
        return $this->pagination;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function getEntrants()
    {
        return $this->entrants;
    }

    public function getChallenges()
    {
        return $this->challenges;
    }

    public function getRank($getActual = false)
    {
        $redis = Yii::app()->redis->getClient();

        $key = $getActual ? 'board_c:' . date('Ym') : 'board_c:6month';
        $rank  = $redis->zRevRank($key, $this->id);
        if ($rank !== false) {
            $rank++;
        }
        return $rank;
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setSubjectId($id)
    {
        $this->setId($id);
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function fetch()
    {
        if (!$this->id) {
            return false;
        }

        //read all from db
        $res = Yii::app()->db->createCommand()
            ->select('c.owner, c.name, c.created, c.would_compete, m.user AS ownerName')
            ->from('club c')
            ->leftJoin('main m', 'c.owner=m.uid')
            ->where('c.id=:id', [':id'=>$this->id])
            ->queryRow();

        if (!is_array($res)) {
            $this->id = 0;
            return false;
        }

        foreach ($res as $k => $v) {
            $this->$k = $v;
        }
    }

    public function fetchName()
    {
        $name = Yii::app()->db->cache(86400)->createCommand()
            ->select('name')
            ->from('club')
            ->where('id=:id', [':id'=>$this->id])
            ->queryScalar();
        $this->name = $name;
    }

    public function getSubjectName()
    {
        $name = Yii::app()->db->cache(86400)->createCommand()
            ->select('name')
            ->from('club')
            ->where('id=:id', [':id'=>$this->id])
            ->queryScalar();
        if (!$name) {
            $name = '???';
        }
        return $name;
    }

    public function fetchItems($wouldCompete = false)
    {
        $where = $wouldCompete ? 'would_compete=1' : '';
        $limit = Yii::app()->params['listPerPage'];

        $this->count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from('club')
            ->where($where)
            ->queryScalar();

        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('club')
            ->where($where)
            ->order('id DESC')
            ->limit($limit, ($this->page * $limit) - $limit) // the trick is here!
            ->queryAll();

        $this->pagination = new CPagination($this->count);
        $this->pagination->setPageSize(Yii::app()->params['listPerPage']);

        $this->items = $res;
    }

    public function getJoinRequestSent()
    {
        $res = Yii::app()->db->createCommand()
            ->select('club_id')
            ->from('club_members')
            ->where('uid=:uid', [':uid'=>Yii::app()->player->model->uid])
            ->queryScalar();
        return (int)$res;
    }

    /**
     * @param integer $id
     */
    public function joinRequest($id)
    {
        $player = Yii::app()->player->model;
        if ($player->level < 15) {
            throw new CFlashException('Ahhoz, hogy csatlakozhass, min. 15-ös szintre kell fejlődnöd.');
        }

        if ($player->in_club) {
            throw new CFlashException('Már tagja vagy egy másik klubnak.');
        }

        if ($this->getJoinRequestSent()) {
            throw new CFlashException('Már jelentkeztél egy másik klubba.');
        }

        if (count($this->entrants) + count($this->members) >= 8) {
            throw new CFlashException('A klubtagok és jelentkezők száma elérte a 8-et, ezért nem jelentkezhetnek többen.');
        }

        Yii::app()->db->createCommand()
            ->insert(
                'club_members',
                [
                    'club_id'=>(int)$id,
                    'uid'=>$player->uid
                ]
            );
        //refresh list
        $this->entrants[$player->uid] = [
            'uid'=>$player->uid,
            'approved'=>0,
            'user'=>$player->user
            ];

        return true;
    }

    /**
     * @param integer $id
     */
    public function deleteOwnJoinRequest($id)
    {
        $player = Yii::app()->player->model;

        Yii::app()->db->createCommand()
            ->delete(
                'club_members',
                'club_id=:club_id AND uid=:uid AND approved=0',
                ['club_id'=>(int)$id, 'uid'=>$player->uid]
            );
        unset($this->entrants[$player->uid]);

        return true;
    }

    /* members */
    public function fetchMembers()
    {
        $res = Yii::app()->db->createCommand()
            ->select('cm.uid, cm.approved, m.user')
            ->from('club_members cm')
            ->join('main m', 'cm.uid=m.uid')
            ->where('cm.club_id=:club_id', [':club_id'=>$this->id])
            ->queryAll();

        foreach ($res as $u) {
            if ($u['approved']) {
                $this->members[$u['uid']] = $u;
            } else {
                $this->entrants[$u['uid']] = $u;
            }
        }
    }

    /**
     * @param integer $uid
     */
    public function fireMember($uid)
    {
        $player = Yii::app()->player->model;

        if ($player->in_club != $this->id) {
            return false;
        }

        $del = Yii::app()->db->createCommand()
            ->delete(
                'club_members',
                'club_id=:club_id AND uid=:uid AND approved=1',
                ['club_id'=>$this->id, 'uid'=>$uid]
            );

        if ($del) {
            Yii::app()->db->createCommand()
            ->update('main', ['in_club'=>0], 'uid=:uid', [':uid'=>(int)$uid]);

            unset($this->members[$uid]);
        }

        return (bool)$del;
    }

    /**
     * @param integer $uid
     */
    public function approveMember($uid)
    {
        $player = Yii::app()->player->model;

        if ($player->in_club != $this->id) {
            return false;
        }

        if (!array_key_exists($uid, $this->entrants)) {
            return false;
        }

        $cnt = count($this->members) + 1; //with owner
        if ($cnt >= 8) {
            return false;
        }

        $update = Yii::app()->db->createCommand()
            ->update('club_members', ['approved'=>1], 'uid=:uid', [':uid'=>(int)$uid]);

        if ($update) {
            Yii::app()->db->createCommand()
            ->update('main', ['in_club'=>$this->id], 'uid=:uid', [':uid'=>(int)$uid]);

            $this->members[$uid] = $this->entrants[$uid];
            unset($this->entrants[$uid]);
            $cnt++;

            $b = Yii::app()->badge->model;
            $b->uid = $uid;
            $b->triggerSimple('club_join');

            $b->uid = $this->owner;
            $b->triggerClubMembers($cnt);
            $b->uid - $player->uid; //reset
        }

        return (bool)$update;
    }

    /**
     * @param integer $uid
     */
    public function deleteJoinRequest($uid)
    {
        $del = Yii::app()->db->createCommand()
            ->delete(
                'club_members',
                'club_id=:club_id AND uid=:uid AND approved=0',
                [':club_id'=>$this->id, 'uid'=>$uid]
            );
        unset($this->entrants[$uid]);

        return (bool)$del;
    }

    public function close($pass)
    {
        if (!$this->requirementsForClose($pass)) {
            return false;
        }

        $this->fireMembers();
        $this->deleteForum();
        $this->deleteClub();
        return true;
    }

    private function requirementsForClose($pass)
    {
        if ((new Challenge)->hasActiveChallenge($this->id)) {
            throw new CFlashException('A klub nem szüntethető meg verseny közben.');
        }

        if (Yii::app()->player->uid <> $this->owner) {
            throw new CFlashException('A klubot csak az alapító szüntetheti meg.');
        }

        if (md5($pass) !== $_SESSION['pass']) {
            throw new CFlashException('A jelszó helytelen.');
        }

        return true;
    }

    private function fireMembers()
    {
        Yii::app()->db->createCommand()
            ->delete(
                'club_members',
                'club_id=:club_id',
                [':club_id'=>$this->id]
            );

        $this->members[$this->owner] = ['uid'=>$this->owner];
        foreach ($this->members as $member) {
            Yii::app()->db->createCommand()
                ->update('main', ['in_club'=>0], 'uid=:uid', [':uid'=>(int)$member['uid']]);
        }
    }

    private function deleteForum()
    {
        Yii::app()->db->createCommand()
            ->delete(
                'forum',
                'club_id=:club_id',
                [':club_id'=>$this->id]
            );
    }

    private function deleteClub()
    {
        Yii::app()->db->createCommand()
            ->delete(
                'club',
                'id=:club_id',
                [':club_id'=>$this->id]
            );
    }

    public function switchCompete()
    {
        $compete = (int)$this->would_compete ? 0 : 1;
        Yii::app()->db->createCommand()
            ->update('club', ['would_compete'=>$compete], 'id=:id', [':id'=>$this->id]);
        $this->would_compete = $compete;
    }

    public function fetchChallenges($limit = 15)
    {
        $res = Yii::app()->db->createCommand()
            ->select('id, caller, opponent, name_caller, name_opponent, winner, created')
            ->from('challenge')
            ->where('caller=:club_id OR opponent=:club_id', [':club_id'=>$this->id])
            ->order('id DESC')
            ->limit((int)$limit)
            ->queryAll();

        foreach ($res as $u) {
            $this->challenges[$u['id']] = $u;
        }
    }
}
