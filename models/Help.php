<?php
class Help extends CModel
{
    const TYPE_PROFILE = 1;
    const TYPE_MISSION = 2;
    const TYPE_SHOP = 3;
    const TYPE_DUEL = 4;

    private $_items = [];
    private $_topics = [
        'profile'=>'Profil', 
        'mission'=>'Megbízások', 
        'shop'=>'Áron bá', 
        'duel'=>'Párbaj', 
        'club'=>'Klubok'
        ];
    private $_topic = 'profile';

    public function getItems() { return $this->_items; }
    public function getTopics() { return $this->_topics; }
    public function attributeNames() { return []; }

    public function setPage($page) {
        $this->_page = $page;
    }
    public function setTopic($topic) {
        $this->_topic = $topic;
    }

    public function fetchItems($limit = 0) {
        $max = min(Yii::app()->player->model->level, $this->fetchMax());

        $this->_items = []; //reset
        $added = 0;
        for ($i=$max; $i>=0; $i--) {
            //echo $i.':'.$this->_topic."\n";
            $res = Yii::app()->redis->getClient()->get('help:' . $this->_topic . ':'.$i);
            if ($res) {
                $this->_items[$i] = $res;
                $added++;
                if ($limit>0 and $added>=$limit) break;
            }
        }
    }
    public function getRandomItemByType() {
        $this->fetchItems(5);
        $items = $this->_items;
        
        $rnd = array_rand($items);
        $selected = $items[$rnd];
        return $selected;     
    }

    private function fetchMax() {
        return (int)Yii::app()->redis->getClient()
            ->get('help:' . $this->_topic . ':max');
    }
}
