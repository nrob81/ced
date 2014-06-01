<?php
class Club extends CModel
{
    private $_id;
    private $owner;
    private $ownerName;
    private $name;
    private $would_compete;
    private $created;
    private $_items = [];
    private $_page = 0;
    private $_pagination;
    private $_count;
    private $_members = [];
    private $_entrants = [];
    private $_challenges = [];
    
    public function attributeNames() {
        return [];
    }
    
    public function getId() { return $this->_id; }
    public function getOwner() { return $this->owner; }
    public function getOwnerName() { return $this->ownerName; }
    public function getName() { return $this->name; }
    public function getWould_compete() { return (int)$this->would_compete; }
    public function getCreated() { return $this->created; }
    public function getPagination() { return $this->_pagination; }
    public function getCount() { return $this->_count; }
    public function getItems() { return $this->_items; }
    public function getMembers() { return $this->_members; }
    public function getEntrants() { return $this->_entrants; }
    public function getChallenges() { return $this->_challenges; }

    public function setId($id) {
        $this->_id = (int)$id;
    }
    public function setPage($page) {
        $this->_page = $page;
    }

    public function getRankActual() {
        $redis = Yii::app()->redis->getClient();

        $key = 'board_c:' . date('Ym');
        $rank  = $redis->zRevRank($key, $this->id);
        if ($rank !== false) $rank++;

        return $rank;
    }
    public function getRank() {
        $redis = Yii::app()->redis->getClient();

        $key = 'board_c:6month';
        $rank  = $redis->zRevRank($key, $this->id);
        if ($rank !== false) $rank++;

        return $rank;
    }

    public function fetch() {
        if (!$this->id) return false;

        //read all from db
        $res = Yii::app()->db->createCommand()
            ->select('c.owner, c.name, c.created, c.would_compete, m.user AS ownerName')
            ->from('club c')
            ->leftJoin('main m', 'c.owner=m.uid')
            ->where('c.id=:id', [':id'=>$this->_id])
            ->queryRow();
        
        if (!is_array($res)) {
            $this->_id = 0;
            return false;
        }

        foreach ($res as $k => $v) {
            $this->$k = $v;
        }
    }
    public function fetchName() {
        $name = Yii::app()->db->cache(86400)->createCommand()
            ->select('name')
            ->from('club')
            ->where('id=:id', [':id'=>$this->_id])
            ->queryScalar();
        //if (!$name) $name = '???';
        $this->name = $name;
    }
    public function fetchItems($would_compete = false) {
        $player = Yii::app()->player->model;
        $where = $would_compete ? 'would_compete=1' : '';
        $limit = Yii::app()->params['listPerPage'];
        
        $this->_count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from('club')
            ->where($where)
            ->queryScalar();
        
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('club')
            ->where($where)
            ->order('id DESC')
            ->limit($limit, ($this->_page * $limit) - $limit) // the trick is here!
            ->queryAll();
        
        $this->_pagination = new CPagination($this->_count);
        $this->_pagination->setPageSize(Yii::app()->params['listPerPage']);

        $this->_items = $res;
    }

    public function getJoinRequestSent() {
        $res = Yii::app()->db->createCommand()
            ->select('club_id')
            ->from('club_members')
            ->where('uid=:uid', [':uid'=>Yii::app()->player->model->uid])
            ->queryScalar();
        return (int)$res;
    }

    public function joinRequest($id) {
        $player = Yii::app()->player->model;
        if ($player->level < 15) throw new CFlashException('Ahhoz, hogy csatlakozhass, min. 15-ös szintre kell fejlődnöd.');
        if ($player->in_club) throw new CFlashException('Már tagja vagy egy másik klubnak.');
        if ($this->getJoinRequestSent()) throw new CFlashException('Már jelentkeztél egy másik klubba.');
        if (count($this->_entrants) + count($this->_members) >= 8) throw new CFlashException('A klubtagok és jelentkezők száma elérte a 8-et, ezért nem jelentkezhetnek többen.');

        $insert = Yii::app()->db->createCommand()
            ->insert('club_members', [
                'club_id'=>(int)$id,
                'uid'=>$player->uid
                ]);
        //refresh list
        $this->_entrants[$player->uid] = [
            'uid'=>$player->uid,
            'approved'=>0,
            'user'=>$player->user
            ];

        return true;
    }
    public function deleteOwnJoinRequest($id) {
        $player = Yii::app()->player->model;

        $del = Yii::app()->db->createCommand()
            ->delete('club_members', 
                'club_id=:club_id AND uid=:uid AND approved=0', 
                ['club_id'=>(int)$id, 'uid'=>$player->uid]
            );
        unset($this->_entrants[$player->uid]);

        return true;
    }

    /* members */
    public function fetchMembers() {
        $res = Yii::app()->db->createCommand()
            ->select('cm.uid, cm.approved, m.user')
            ->from('club_members cm')
            ->join('main m', 'cm.uid=m.uid')
            ->where('cm.club_id=:club_id', [':club_id'=>$this->_id])
            ->queryAll();
        
        foreach ($res as $u) {
            if ($u['approved']) {
                $this->_members[$u['uid']] = $u;
            } else {
                $this->_entrants[$u['uid']] = $u;
            }
        }
    }
    public function fireMember($uid) {
        $player = Yii::app()->player->model;

        if ($player->in_club != $this->_id) return false;

        $selfMod = $uid == $player->uid;

        $del = Yii::app()->db->createCommand()
            ->delete('club_members', 
                'club_id=:club_id AND uid=:uid AND approved=1', 
                ['club_id'=>$this->_id, 'uid'=>$uid]
            );

        if ($del) {
            $cmd = Yii::app()->db->createCommand()
            ->update('main', ['in_club'=>0], 'uid=:uid', [':uid'=>(int)$uid]);

            unset($this->_members[$uid]);
        }
        
        return (bool)$del;
    }
    
    public function approveMember($uid) {
        $player = Yii::app()->player->model;

        if ($player->in_club != $this->_id) return false;
        if (!array_key_exists($uid, $this->_entrants)) return false;
        $cnt = count($this->_members) + 1; //with owner
        if ($cnt >= 8) return false; 

        $update = Yii::app()->db->createCommand()
            ->update('club_members', ['approved'=>1], 'uid=:uid', [':uid'=>(int)$uid]
            );

        if ($update) {
            $cmd = Yii::app()->db->createCommand()
            ->update('main', ['in_club'=>$this->_id], 'uid=:uid', [':uid'=>(int)$uid]);
            
            $this->_members[$uid] = $this->_entrants[$uid];
            unset($this->_entrants[$uid]);
            $cnt++;
            $b = Yii::app()->badge->model;
            $b->triggerHer($uid, 'club_join');
            $b->triggerHer($this->owner, 'club_members_8', ['cnt'=>$cnt]);
        }
        
        return (bool)$update;
    }
    
    public function deleteJoinRequest($uid) {
        $player = Yii::app()->player->model;

        $selfMod = $uid == $player->uid;
        if (!$selfMod and $player->in_club != $this->_id) return false;

        $del = Yii::app()->db->createCommand()
            ->delete('club_members', 
                'club_id=:club_id AND uid=:uid AND approved=0', 
                [':club_id'=>$this->_id, 'uid'=>$uid]
            );
        unset($this->_entrants[$uid]);

        return (bool)$del;
    }

    public function close($pass) {
        $player = Yii::app()->player->model;
        
        $challenge = new Challenge;
        if ($challenge->hasActiveChallenge($this->id)) throw new CFlashException('A klub nem szüntethető meg verseny közben.');
        if ($player->uid <> $this->owner) throw new CFlashException('A klubot csak az alapító szüntetheti meg.');
        if (md5($pass) !== $_SESSION['pass']) throw new CFlashException('A jelszó helytelen.');
        
        //delete members
        $del = Yii::app()->db->createCommand()
            ->delete('club_members', 
                'club_id=:club_id', 
                [':club_id'=>$this->_id]
            );
        //update in_club
        $this->_members[$this->owner] = ['uid'=>$this->owner];
        foreach ($this->_members as $member) {
            $cmd = Yii::app()->db->createCommand()
            ->update('main', ['in_club'=>0], 'uid=:uid', [':uid'=>(int)$member['uid']]);
        }
        //delete forum
        $del = Yii::app()->db->createCommand()
            ->delete('forum', 
                'club_id=:club_id', 
                [':club_id'=>$this->_id]
            );
        //delete club
        $del = Yii::app()->db->createCommand()
            ->delete('club', 
                'id=:club_id', 
                [':club_id'=>$this->_id]
            );

        return true;
    }
    public function switchCompete() {
        $compete = (int)$this->would_compete ? 0 : 1;
        $cmd = Yii::app()->db->createCommand()
            ->update('club', ['would_compete'=>$compete], 'id=:id', [':id'=>$this->_id]);
        $this->would_compete = $compete;
    }

    public function fetchChallenges($limit = 15) {
        $res = Yii::app()->db->createCommand()
            ->select('id, caller, opponent, name_caller, name_opponent, winner, created')
            ->from('challenge')
            ->where('caller=:club_id OR opponent=:club_id', [':club_id'=>$this->_id])
            ->order('id DESC')
            ->limit((int)$limit)
            ->queryAll();
        
        foreach ($res as $u) {
            $this->_challenges[$u['id']] = $u;
        }
    }
}