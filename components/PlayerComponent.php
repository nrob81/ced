<?php
/**
 * @property Player $model
 * @property integer $uid
 * @property boolean $clubChallenge
 * @property boolean $newContest
 */
class PlayerComponent extends CApplicationComponent
{
    private $model;
    private $clubChallenge = false;
    private $newContest = false;

    public function getModel()
    {
        return $this->model;
    }

    public function getUid()
    {
        return $this->model->uid;
    }

    public function getClubChallenge()
    {
        return $this->clubChallenge;
    }

    public function getNewContest()
    { 
        $lastSeen = (int)Yii::app()->redis->getClient()->get('contest:lastcheck:'.$this->model->uid);
        return $this->newContest > $lastSeen;
    }
    
    public function init()
    {
        $this->model = new Player();
        $this->model->setAllAttributes();
        $this->checkClubChallenge();
        $this->checkNewContest();
    }

    public function rest()
    {
        $this->model->rest();
    }

    protected function checkClubChallenge()
    {
        if (!$this->model->in_club) {
            return false;
        }

        $lastChallenge = Yii::app()->redis->getClient()->get('reminder:challenge:'.$this->model->in_club);
        if ($lastChallenge >= time()) $this->clubChallenge = true;
    }
    
    protected function checkNewContest()
    {
        $this->newContest = (int)Yii::app()->redis->getClient()->get('contest:active');
    }
}
