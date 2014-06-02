<?php
/**
 * @property array $posts
 * @property string $content_type
 */
class Wall extends CModel
{
    const TYPE_DUEL = 'duel';
    const TYPE_LEVEL = 'level';
    const TYPE_NEW_COUNTY = 'new_county';
    const TYPE_CLUB_FIRE = 'club_fire';
    const TYPE_CLUB_APPROVE = 'club_approve';
    const TYPE_CLUB_DELETE = 'club_delete';
    const TYPE_CLUB_CLOSE = 'club_close';
    const TYPE_BADGE = 'badge';
    const TYPE_NEW_AWARD = 'new_award';

    private $_uid;
    private $_content_type = '?';
    private $_posts = [];
    
    public function attributeNames() {
        return [];
    }

    public function getPosts() { return $this->_posts; }
    
    public function setUid($uid) {
        $this->_uid = (int)$uid;
    }
    public function setContent_type($type) {
        $this->_content_type = $type;
    }

    public function add($data) {
        $data['type'] = $this->_content_type;
        $body = CJSON::encode($data);
        
        Yii::app()->db->createCommand()
            ->insert('wall', [
                'uid'=>$this->_uid,
                'body'=>$body
            ]);
    }

    public function fetchPosts() {
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('wall')
            ->where('uid=:uid', [':uid'=>$this->_uid])
            ->order('created DESC')
            ->limit(10)
            ->queryAll();

        $lastDay = '';
        foreach ($res as $post) {
            //separator
            $day = date('Y. m. d.', strtotime($post['created']));
            if ($day != $lastDay) {
                $lastDay = $day;
                $this->_posts[$post['created']] = [
                    'content_type' => 'date_separator',
                    'body'=>[],
                    'created'=>$day
                    ];
            }

            $post['body'] = CJSON::decode($post['body']);
            $post['content_type'] = $post['body']['type'];

            $post['created'] = date('H:i', strtotime($post['created']));

            $this->_posts[$post['id']] = $post;
        }
    }
}
