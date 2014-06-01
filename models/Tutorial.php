<?php
class Tutorial extends CModel
{
    private $_state;
    private $_location;
    private $_descriptionToShow;

    public function attributeNames() {
        return [];
    }

    public function getState() { return (int)$this->_state; }
    public function getDescriptionToShow() { 
            $this->update();
        return (int)$this->_descriptionToShow; 
    }
    
    public function setState($state) {
        $this->_state = (int)$state;
    }
    public function setLocation($location) {
        $this->_location = $location;
    }

    private function update() {
        $player = Yii::app()->player->model;
        //check requirements of new state
        $advance = false;
        switch ($this->_state) {
            case 0: 
                //teljesits 1 megbizast
                foreach ($this->_location->missions as $mission) {
                    if ($mission->routine > 0) $advance = true;
                }
                break;
            case 1:
                if ($player->dollar >= 10) $advance = true;
                break;
            case 2:
                if ($player->skill_extended - $player->skill > 2) $advance = true;
                break;
            case 3:
                //1 megbizasnal 100% rutin
                foreach ($this->_location->missions as $mission) {
                    if ($mission->routine >= 100) $advance = true;
                }
                break;
            case 4:
                $advance = true;
                foreach ($this->_location->missions as $mission) {
                    if (!$mission->gate and $mission->routine < 100) $advance = false;
                }
                break;
            case 5:
                if ($this->_location->routine > 0) $advance = true;
                break;
            case 6:
                //advance at first travel 
        }
        
        //advance to new state
        if ($advance) {
            Yii::app()->player->model->updateAttributes(['tutorial_mission'=>1], []);
            $this->_state++;
        }

        //set description
        $this->_descriptionToShow = $this->_state < 7 ? $this->_state+1 : 0;
    }
}
