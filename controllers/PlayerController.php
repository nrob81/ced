<?php
class PlayerController extends GameController
{
    public $defaultAction = 'profile';

    public function actionProfile($uid = 0)
    {
        $player = Yii::app()->player->model;
        $playerStats = new PlayerStats;
        $playerStats->uid = $player->uid;

        //other player
        if ($uid) {
            $player = new Player;
            $player->setAllAttributes($uid);
            $playerStats->uid = $uid;

            if (!$player->uid) {
                throw new CHttpException(404, 'A keresett felhasználó nem található.');
            }
            
            $duelShiel = new DuelShield();
            $duelShiel->uid = $uid;
            if ($duelShiel->lifeTime > 0) {
                $player->activateDuelShield();
            }
        }

        $advancement = $this->advancement($player);

        //stats
        $playerStats->fetchStats();

        //badges
        $badgeList = new BadgeList;
        $badgeList->uid = $uid;
        $badgeList->fetchOwned();

        $this->render('profile', [
            'player' => $player,
            'playerStats' => $playerStats,
            'badgeList' => $badgeList,
            'advancement' => $advancement
            ]);
    }

    public function actionBadges()
    {
        $badgeList = new BadgeList;
        $badgeList->fetchOwned();
        $badgeList->fetchAll();

        $this->render('badges', [
            'badgeList' => $badgeList
            ]);
    }

    protected function advancement($player)
    {
        $advancement = null;
        if ($player->status_points) {
            $advancement = new Advancement;
            $advancement->uid = $player->uid;

            $increment_id = Yii::app()->request->getPost('increment_id', 0);
            if ($increment_id) {
                $advancement->incrementForStatuspoint($increment_id);
            }
        }

        return $advancement;
    }
}
