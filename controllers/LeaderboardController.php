<?php
class LeaderboardController extends GameController
{
    public function actionIndex($range = '')
    {
       $this->makeBoard(Leaderboard::TYPE_PLAYER, $range); 
    }
    public function actionClub($range = '')
    {
       $this->makeBoard(Leaderboard::TYPE_CLUB, $range); 
    }

    private function makeBoard($type, $range) {
        $board = new Leaderboard;
        $board->uid = Yii::app()->player->model->uid;
        $board->inClub = Yii::app()->player->model->in_club;
        $board->boardType = $type;

        switch ($range) {
        case 'prev': 
            $board->setRange(Leaderboard::RANGE_PREVIOUS);
            break;
        case 'last': 
            $board->setRange(Leaderboard::RANGE_LAST_SIX);
            break;
        default: 
            $board->setRange(Leaderboard::RANGE_ACTUAL);
            break;
        }
        if ($board->boardType == Leaderboard::TYPE_CLUB) {
            $board->fetchClubs();
        } else {
            $board->fetch();
        }

        $this->render('index', [
            'board'=>$board,
            ]);
    }
}
