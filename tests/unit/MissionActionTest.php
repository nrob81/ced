<?php
class MissionActionTest extends CTestCase
{
    public function testRequirementsOk() 
    {
        $player = $this->getPlayer();
    }

    private function getPlayer()
    {
        $model = new Player();
        $model->setAllAttributes();
        return $model;
    }
}
