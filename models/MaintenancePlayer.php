<?php
/**
 * @property string $log
 */
class MaintenancePlayer extends CModel
{
    private $_user;
    private $_uid;
    private $_log;

    public function attributeNames() {
        return [];
    }
    public function getLog() { return $this->_log; }

    public function setUid($player) {
        $res = Yii::app()->db->createCommand()
            ->select('uid, user')
            ->from('main')
            ->where('user LIKE :user', [':user'=>$player])
            ->queryRow();
        $this->_uid = (int)$res['uid'];
        $this->_user = $res['user'];
    }

    public function reset() {
        $this->_log .= "user: {$this->_user}<br/>";
        $this->_log .= "uid: {$this->_uid}<br/>";
        if (!$this->_uid) {
            return false;
        }

        $this->_log .= "cleaning MySQL data:<br/>";

        //get details
        $p = Yii::app()->db->createCommand()
            ->select('*')
            ->from('main')
            ->where('uid = :uid', [':uid'=>$this->_uid])
            ->queryRow();
        //get club ownership
        if ($p['in_club']) {
            $owned = Yii::app()->db->createCommand()
            ->select('id')
            ->from('club')
            ->where('owner = :uid', [':uid'=>$this->_uid])
            ->queryScalar();
            if ($owned) {
                //delete clubs forum
                Yii::app()->db->createCommand("DELETE FROM forum WHERE club_id={$owned}")->execute();
                $this->_log .= " - forum posts<br/>";

                //delete club members
                Yii::app()->db->createCommand("UPDATE main SET in_club=0 WHERE uid IN (SELECT uid FROM club_members WHERE club_id={$owned})")->execute();
                Yii::app()->db->createCommand("DELETE FROM club_members WHERE club_id={$owned}")->execute();
                $this->_log .= " - clubs members<br/>";

                //delete challenges
                Yii::app()->db->createCommand("DELETE FROM challenge WHERE caller={$owned} OR opponent={$owned}")->execute();
                $this->_log .= " - clubs challenges<br/>";

                //delete club
                Yii::app()->db->createCommand("DELETE FROM club WHERE id={$owned}")->execute();
                $this->_log .= " - owned club<br/>";
            }
        }
        
        Yii::app()->db->createCommand("DELETE FROM club_members WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - club membership<br/>";

        Yii::app()->db->createCommand("DELETE FROM duel WHERE caller={$this->_uid} OR opponent={$this->_uid}")->execute();
        $this->_log .= " - duel data<br/>";

        Yii::app()->db->createCommand("DELETE FROM log WHERE uid={$this->_uid}")->execute();
        Yii::app()->db->createCommand("DELETE FROM log_counters WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - logs, counters<br/>";

        Yii::app()->db->createCommand("DELETE FROM users_baits WHERE uid={$this->_uid}")->execute();
        Yii::app()->db->createCommand("DELETE FROM users_items WHERE uid={$this->_uid}")->execute();
        Yii::app()->db->createCommand("DELETE FROM users_parts WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - baits, items, parts<br/>";

        Yii::app()->db->createCommand("DELETE FROM users_missions WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - missions<br/>";

        Yii::app()->db->createCommand("DELETE FROM visited WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - visited waters<br/>";

        Yii::app()->db->createCommand("DELETE FROM wall WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - wall<br/>";

        Yii::app()->db->createCommand("DELETE FROM main WHERE uid={$this->_uid}")->execute();
        $this->_log .= " - main data<br/>";


        $this->_log .= "cleaning REDIS data:<br/>";
        $redis = Yii::app()->redis->getClient();

        $redis->del('badges:added:'.$this->_uid);
        $redis->del('badges:owned:'.$this->_uid);
        $redis->zRem('badges:leaderboard',$this->_uid);
        $this->_log .= " - badges<br/>";

        $redis->zRem('board_p:201312', $this->_uid);
        $redis->zRem('board_p:201311', $this->_uid);
        $redis->zRem('board_p:201310', $this->_uid);
        $redis->zRem('board_p:201309', $this->_uid);
        $redis->zRem('board_p:201308', $this->_uid);
        $redis->zRem('board_p:6month', $this->_uid);
        $this->_log .= " - leaderboard<br/>";


        $suid = (string)$this->_uid;
        $key = 'counter:' . $suid[0] . ':' . $suid[1] . ':' .$suid[2] . ':' . $suid;
        $redis->del($key.':all');
        for ($i=0; $i<100; $i++) {
            $redis->del($key.':levels:'.$i);
        }
        $this->_log .= " - counters<br/>";

        $redis->del('login:days:'.$this->_uid);
        $this->_log .= " - login counter<br/>";

        $redis->del('debug:setitem:'.$this->_uid);
        $this->_log .= " - setitem log<br/>";
    }
}
