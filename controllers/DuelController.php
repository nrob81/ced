<?php
class DuelController extends GameController
{
    protected function beforeAction($action)
    {
        parent::beforeAction($action);

        if (Yii::app()->player->model->level < DuelList::REQ_LEVEL) {
            $this->render('lowlevel');
            return false;
        }
        return true;
    }


    public function actionIndex($page = 0)
    {
        $duelList = new DuelList();
        $duelList->page = $page;
        $duelList->fetchOpponents();

        $this->render('index', [
            'list'=>$duelList->opponents,
            'pagination' => $duelList->pagination,
            'count' => $duelList->count,
            'page_size' => Yii::app()->params['listPerPage'],
            'page'=>$page,
            ]);
    }

    public function actionCommon()
    {
        $duelList = new DuelList();
        $duelList->fetchCommonRivals();

        $this->render('commonrivals', [
            'list'=>$duelList->opponents,
            ]);
    }
    
    public function actionHistory()
    {
        $duelList = new DuelList();
        $duelList->fetchLastRivals();

        $this->render('history', [
            'list'=>$duelList->opponents,
            ]);
    }

    public function actionGo($opponentId = 0)
    {
        $duel = new Duel();
        $duel->caller = Yii::app()->player->uid;
        $duel->opponent = $opponentId;
        $duel->fetchClubChallengeState();

        try {
            if ($duel->validate()) {
                $duel->play();
            }
        } catch (CFlashException $e) {
            Yii::app()->user->setFlash('error', $e->getMessage());
        }

        $this->render('go', [
            'duel'=>$duel,
            ]);
    }
    
    public function actionReplay($id)
    {
        $duel = new Duel;
        try {
            $duel->replay((int)$id);
        } catch (CFlashException $e) {
            Yii::app()->user->setFlash('error', $e->getMessage());
            $this->redirect('/duel');
        }

        $this->render('replay', [
            'duel'=>$duel,
            ]);
    }
}
