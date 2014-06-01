<?php
class PlayerComponent extends CApplicationComponent
{
    private $_model;
    private $_clubChallenge = false;
    private $_newContest = false;

    public function init() {
        $this->_model = new Player();
        $this->_model->setAllAttributes();
        $this->checkClubChallenge();
        $this->checkNewContest();
    }

    public function getModel() {
        return $this->_model;
    }
    public function getUid() { return $this->_model->uid; }
    public function getClubChallenge() { return $this->_clubChallenge; }
    public function getNewContest() { 
        $lastSeen = (int)Yii::app()->redis->getClient()->get('contest:lastcheck:'.$this->_model->uid);
        return $this->_newContest > $lastSeen; 
    }

    public function rest() {
        $this->_model->rest();
    }

    protected function checkClubChallenge() {
        if (!$this->_model->in_club) return false;

        $lastChallenge = Yii::app()->redis->getClient()->get('reminder:challenge:'.$this->_model->in_club);
        if ($lastChallenge >= time()) $this->_clubChallenge = true;
    }
    
    protected function checkNewContest() {
        $this->_newContest = (int)Yii::app()->redis->getClient()->get('contest:active');
    }
}

