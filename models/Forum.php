<?php
/**
 * @property integer $id
 * @property CPagination $pagination
 * @property integer $count
 * @property array $items
 * @property integer $page
 * @property boolean $private
 */
class Forum extends CModel
{
    private $id;
    private $items = [];
    private $page = 0;
    private $pagination;
    private $count;
    private $private = false;

    public function attributeNames()
    {
        return [];
    }
    
    public function getId()
    {
        return (int)$this->id;
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
      
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function setPrivate($private)
    {
        $this->private = (bool)$private;
    }

    public function fetchItems()
    {
        $player = Yii::app()->player->model;
        $limit = Yii::app()->params['listPerPage'];
        
        $fetchPrivates = $this->id !== $player->in_club ? ' AND private=0' : '';

        $this->count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from('forum')
            ->where('club_id = :id'.$fetchPrivates, [':id'=>$this->id])
            ->queryScalar();
        
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('forum')
            ->where('club_id = :id'.$fetchPrivates, [':id'=>$this->id])
            ->order('id DESC')
            ->limit($limit, ($this->page * $limit) - $limit) // the trick is here!
            ->queryAll();
        
        $this->pagination = new CPagination($this->count);
        $this->pagination->setPageSize(Yii::app()->params['listPerPage']);

        $this->items = $res;
    }

    public function save($post, $isMentor = false)
    {
        if (!$isMentor) {
            $post = trim($post);
            $post = strip_tags($post);
            $post = htmlspecialchars($post);
            $post = substr($post, 0, 800);
        }

        if (!$post) {
            return false;
        }

        $player = Yii::app()->player->model;
        $uid = $isMentor ? 1 : $player->uid;
        $user = $isMentor ? 'Áron bá' : $player->user;
        if ($uid > 1 && $player->in_club != $this->id) {
            return false; //nem klubtag, nem mentor
        }

        $parameters = [
            'club_id'=>$this->id,
            'uid'=>$uid,
            'user'=>$user,
            'body'=>$post,
            'private'=>$this->private
            ];

        Yii::app()->db->createCommand()
                ->insert('forum', $parameters);

        $parameters['created'] = 'most';
        array_unshift($this->items, $parameters);
        return true;
    }

    public function delete($id)
    {
        if (!$id) {
            return false;
        }

        $player = Yii::app()->player->model;
        if ($player->in_club != $this->id) {
            return false; //nem klubtag, nem mentor
        }

        Yii::app()->db->createCommand()->delete(
            'forum',
            'id=:id AND uid=:uid',
            [':id'=>(int)$id, ':uid'=>Yii::app()->player->model->uid]
        );

        return true;
    }
}
