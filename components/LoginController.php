<?php
class LoginController extends Controller
{
    /**
     * @return int
     */
    protected function incrementLoginDays($uid)
    {
        if (!$uid) {
            return false;
        }

        $redis = Yii::app()->redis->getClient();
        $key = "counter:login:days:".$uid;
        $yesterday = array(
            'start' => mktime(0, 0, 0, date('m'), date('d')-1, date('Y')),
            'end' => mktime(0, 0, -1, date('m'), date('d'), date('Y')),
        );

        $cnt = 0;
        $last = $redis->hGet($key, 'last');
        if ($last >= $yesterday['start'] && $last <= $yesterday['end']) { //yesterday
            $cnt = $redis->hIncrBy($key, 'cnt', 1);
        } elseif ($last < $yesterday['start']) {	//more than 1 day ago
            $redis->hSet($key, 'cnt', 0);
        }
        $redis->hSet($key, 'last', time());
        return $cnt;
    }
}
