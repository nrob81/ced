<?php
class MissionsController extends GameController
{
    public function actionIndex() {
        $this->redirect(['missions/list', 'id'=>Yii::app()->player->model->last_location]);
    }

	public function actionList($id = 1) {
        $location = new Location();
        $location->setId($id);
        if (!$location->isVisited()) {
            Yii::app()->player->model->rewriteAttributes(['last_location'=>1]);
            $this->render('not_visited');
            return false;
        }

        $location->setActive();

        //list missions
        $location->fetchRoutine();
        $location->fetchMissions();

        //complete selected mission
        $mission_id = Yii::app()->request->getPost('mission_id', 0);
        $location->completeMission($mission_id);

        //name of location
        $name = [
            'location' => $location->getName(),
            'county' => $location->getCounty(),
            ];

        //navigation from current location
        $nav = $location->getNavigationLinks();

        //tutorial
        $tutorialToShow = 0;
        if ($id==1) {
            //only in first location
            $tutorial = new Tutorial;
            $tutorial->state = Yii::app()->player->model->tutorial_mission;
            $tutorial->location = $location;
            $tutorialToShow = $tutorial->descriptionToShow;
        }
        $this->render('index', [
            'location' => $location,
            'name' => $name,
            'missions'=>$location->missions,
            'missionTypeList'=>$location->missionTypes,
            'nav' => $nav,
            'completedId'=>$location->completedId,
            'routine'=>$location->routineStars,
            'tutorialToShow'=>$tutorialToShow,
            ]);
        
	}
    public function actionMap() {
        $location = new Location();
        $visited = $location->listVisited();

        $center = '45.807504,17.82389'; //id 1
        $last = [];
        $locations = [];
        foreach ($visited as $v) {
            $id = (int)$v['id'];
            $txtRoutine = '';
            if ($v['routine']) {
                $routine = $location->getRoutineStars($v['routine']);
                $txtRoutine = 'Helyszínen megszerzett rutinod: <br/>';
                $txtRoutine .= $location->getRoutineImages($routine);
            } else {
                $last[] = $id;
            }

            $locations[] = "[{$v['position']}, '{$v['title']}', {$id}, '{$txtRoutine}']";
            if (isset($v['last'])) $center = $v['position'];
        }

        $this->render('map', [
            'visited'=>$visited,
            'locations'=>implode(",\n", $locations),
            'center'=>$center,
            'last'=>implode(', ',$last)
            ]);
    }
}