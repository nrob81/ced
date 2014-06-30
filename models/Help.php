<?php
/**
 * @property array $items
 * @property array $topics
 * @property string $topic
 * @property string $randomItemByType
 */
class Help extends CModel
{
    const TYPE_PROFILE = 1;
    const TYPE_MISSION = 2;
    const TYPE_SHOP = 3;
    const TYPE_DUEL = 4;

    private $items = [];
    private $topics = [
        'profile' => 'Profil',
        'mission' => 'Megbízások',
        'shop' => 'Áron bá',
        'duel' => 'Párbaj',
        'club' => 'Klubok'
        ];
    private $topic = 'profile';

    public function attributeNames()
    {
        return [];
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getTopics()
    {
        return $this->topics;
    }

    public function getRandomItemByType()
    {
        $this->fetchItems(5);
        $items = $this->items;
        
        $rnd = array_rand($items);
        $selected = $items[$rnd];
        return $selected;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    public function fetchItems($limit = 0)
    {
        $max = min(Yii::app()->player->model->level, $this->fetchMax());

        $this->items = []; //reset
        $added = 0;
        for ($i=$max; $i>=0; $i--) {
            //echo $i.':'.$this->topic."\n";
            $res = Yii::app()->redis->getClient()->get('help:' . $this->topic . ':'.$i);
            if ($res) {
                $this->items[$i] = $res;
                $added++;
                if ($limit>0 and $added>=$limit) {
                    break;
                }
            }
        }
    }
    

    private function fetchMax()
    {
        return (int)Yii::app()->redis->getClient()
            ->get('help:' . $this->topic . ':max');
    }
}
