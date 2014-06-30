<?php
/**
 * @property integer $uid
 * @property string $key
 * @property integer $level
 * @property array $counters
 */
class Logger extends CModel
{
    private $key = '';
    private $level;
    private $uid;

    public function attributeNames()
    {
        return [];
    }

    public function getCounters($postfix = ':all')
    {
        if (!$this->uid) {
            return false;
        }
        
        $redis = Yii::app()->redis->getClient();
        $key = $this->getCounterKey();

        return $redis->hGetAll($key . $postfix);
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setLevel($level)
    {
        $this->level = (int)$level;
    }

    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    public function addToSet($value)
    {
        Yii::app()->redis->getClient()->rPush('debug:'.$this->key, $value);
    }

    public function increment($field, $value)
    {
        if (!$this->uid) {
            return false;
        }

        $redis = Yii::app()->redis->getClient();
        $key = $this->getCounterKey();

        //aggregated
        $return = $redis->hIncrBy($key.':all', $field, (int)$value);
        if ($this->level) {
            //by uid+level
            $redis->hIncrBy($key.':levels:'.$this->level, $field, (int)$value);
            //by level
            $redis->hIncrBy('counter:levels:'.$this->level, $field, (int)$value);
        }


        return $return;
    }

    public function log($data)
    {
        $params = $this->getPlayerParams();
        foreach ($data as $k => $v) {
            $params[$k] = $v;
        }
        //print_r($params);
    }

    private function getCounterKey()
    {
        $suid = (string)$this->uid;
        return 'counter:' . $suid[0] . ':' . $suid[1] . ':' .$suid[2] . ':' . $suid;
    }

    private function getPlayerParams()
    {
        $params = ['uid','xp_all','xp_delta','level','energy_max','energy','skill','skill_extended','strength','dollar','gold','owned_items','owned_baits'];

        $ret = [];
        foreach ($params as $param) {
            $ret[$param] = Yii::app()->player->$param;
        }

        return $ret;
    }
}
