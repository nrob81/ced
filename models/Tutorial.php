<?php
/**
 * @property integer $state
 * @property integer $descriptionToShow
 * @property Location $location
 */
class Tutorial extends CModel
{
    private $state;
    private $location;
    private $descriptionToShow;

    public function attributeNames()
    {
        return [];
    }

    public function getState()
    {
        return (int)$this->state;
    }

    public function getDescriptionToShow()
    {
        $this->update();
        return (int)$this->descriptionToShow;
    }
    
    public function setState($state)
    {
        $this->state = (int)$state;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    private function update()
    {
        $player = Yii::app()->player->model;
        //check requirements of new state
        $advance = false;
        switch ($this->state) {
            case 0:
                //teljesits 1 megbizast
                foreach ($this->location->missions as $mission) {
                    if ($mission->routine > 0) {
                        $advance = true;
                    }
                }
                break;
            case 1:
                if ($player->dollar >= 10) {
                    $advance = true;
                }
                break;
            case 2:
                if ($player->skill_extended - $player->skill > 2) {
                    $advance = true;
                }
                break;
            case 3:
                //1 megbizasnal 100% rutin
                foreach ($this->location->missions as $mission) {
                    if ($mission->routine >= 100) {
                        $advance = true;
                    }
                }
                break;
            case 4:
                $advance = true;
                foreach ($this->location->missions as $mission) {
                    if (!$mission->gate && $mission->routine < 100) {
                        $advance = false;
                    }
                }
                break;
            case 5:
                if ($this->location->routine > 0) {
                    $advance = true;
                }
                break;
            case 6:
                //advance at first travel
        }
        
        //advance to new state
        if ($advance) {
            Yii::app()->player->model->updateAttributes(['tutorial_mission'=>1], []);
            $this->state++;
        }

        //set description
        $this->descriptionToShow = $this->state < 7 ? $this->state+1 : 0;
    }
}
