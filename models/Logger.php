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

    /**
     * @param string $value
     */
    public function addToSet($value)
    {
        Yii::app()->redis->getClient()->rPush('debug:'.$this->key, $value);
    }

    /**
     * @param string $field
     * @param integer $value
     */
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

    private function getCounterKey()
    {
        $suid = (string)$this->uid;
        return 'counter:' . $suid[0] . ':' . $suid[1] . ':' .$suid[2] . ':' . $suid;
    }
}
