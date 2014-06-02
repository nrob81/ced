<?php
/**
 * @property integer $uid
 * @property string $key
 * @property array $counters
 */
class Logger extends CModel
{
    private $_key = '';
    private $_level;
    private $_uid;

    public function attributeNames() {
        return [];
    }

    public function getCounters($postfix = ':all') {
        if (!$this->_uid) return false;
        
        $redis = Yii::app()->redis->getClient();
        $key = $this->getCounterKey();

        return $redis->hGetAll($key . $postfix);
    }

    public function setKey($key) {
        $this->_key = $key;
    }

    public function setLevel($level) {
        $this->_level = (int)$level;
    }

    public function setUid($uid) {
        $this->_uid = (int)$uid;
    }

    public function addToSet($value) {
        Yii::app()->redis->getClient()->rPush('debug:'.$this->_key, $value);
    }

    public function increment($field, $value) {
        if (!$this->_uid) return false;

        $redis = Yii::app()->redis->getClient();
        $key = $this->getCounterKey();

        //aggregated
        $return = $redis->hIncrBy($key.':all', $field, (int)$value);
        if ($this->_level) {
            //by uid+level
            $redis->hIncrBy($key.':levels:'.$this->_level, $field, (int)$value);
            //by level
            $redis->hIncrBy('counter:levels:'.$this->_level, $field, (int)$value);
        }


        return $return;
    }

    public function log($data) {
        $params = $this->getPlayerParams();
        foreach ($data as $k => $v) {
            $params[$k] = $v;
        }
        //print_r($params);
    }

    private function getCounterKey() {
        $suid = (string)$this->_uid;
        return 'counter:' . $suid[0] . ':' . $suid[1] . ':' .$suid[2] . ':' . $suid;
    }    

    private function getPlayerParams() {
        $params = ['uid','xp_all','xp_delta','level','energy_max','energy','skill','skill_extended','strength','dollar','gold','owned_items','owned_baits'];

        $ret = [];
        foreach ($params as $param) {
            $ret[$param] = Yii::app()->player->$param;
        }

        return $ret;
    }
}
