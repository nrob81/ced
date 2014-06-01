<?php
class Forum extends CModel
{
    private $_id;
    private $_items = [];
    private $_page = 0;
    private $_pagination;
    private $_count;
    private $_private = false;

    public function attributeNames() {
        return [];
    }
    
    public function getId() { return (int)$this->_id; }
    public function getPagination() { return $this->_pagination; }
    public function getCount() { return $this->_count; }
    public function getItems() { return $this->_items; }
      
    public function setId($id) {
        $this->_id = (int)$id;
    }
    public function setPage($page) {
        $this->_page = $page;
    }
    public function setPrivate($private) {
        $this->_private = (bool)$private;
    }


    public function fetchItems() {
        $player = Yii::app()->player->model;
        $limit = Yii::app()->params['listPerPage'];
        
        $fetchPrivates = $this->_id !== $player->in_club ? ' AND private=0' : '';

        $this->_count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from('forum')
            ->where('club_id = :id'.$fetchPrivates, [':id'=>$this->_id])
            ->queryScalar();
        
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('forum')
            ->where('club_id = :id'.$fetchPrivates, [':id'=>$this->_id])
            ->order('id DESC')
            ->limit($limit, ($this->_page * $limit) - $limit) // the trick is here!
            ->queryAll();
        
        $this->_pagination = new CPagination($this->_count);
        $this->_pagination->setPageSize(Yii::app()->params['listPerPage']);

        $this->_items = $res;
    }
    public function save($post, $isMentor = false) {
        if (!$post) return false;

        if (!$isMentor) {
            $post = trim($post);
            $post = strip_tags($post);
            $post = htmlspecialchars($post);
            $post = substr($post, 0, 800);
        }
        if (!$post) return false;

        $player = Yii::app()->player->model;
        $uid = $isMentor ? 1 : $player->uid;
        $user = $isMentor ? 'Áron bá' : $player->user;
        if ($uid > 1 and $player->in_club != $this->_id) return false; //nem klubtag, nem mentor

        $parameters = [
            'club_id'=>$this->_id,
            'uid'=>$uid,
            'user'=>$user,
            'body'=>$post,
            'private'=>$this->_private
            ];

        Yii::app()->db->createCommand()
                ->insert('forum', $parameters);

        $parameters['created'] = 'most';
        array_unshift($this->_items, $parameters);
        return true;
    }
    public function delete($id) {
        if (!$id) return false;
        $player = Yii::app()->player->model;
        if ($player->in_club != $this->_id) return false; //nem klubtag, nem mentor

        Yii::app()->db->createCommand()->delete('forum', 
            'id=:id AND uid=:uid',
            [':id'=>(int)$id, ':uid'=>Yii::app()->player->model->uid]
        );

        return true;
    }
}
