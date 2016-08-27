<?php
class ContestController extends GameController
{
    public function actionIndex()
    {
        $cl = new ContestList();
        $this->redirect(['/contest/view', 'id'=>$cl->lastId]);
    }

    public function actionView($id)
    {
        $cl = new ContestList();
        $cl->id = $id;
        $cl->uid = Yii::app()->player->uid;

        //redirections
        if (!$cl->isValid) {
            $lastId = $cl->lastId;
            if ($lastId) {
                Yii::app()->user->setFlash('error', 'A keresett verseny nem található, helyette a legújabbat láthatod.');
                $this->redirect(['/contest/view', 'id'=>$lastId]);
            } else {
                Yii::app()->user->setFlash('error', 'Még nem található horgászvarseny a játékban, de ez hamarosan megváltozik.');
                $this->redirect('/site');
            }
        }

        $cl->fetchDetails();
        $cl->fetchList();
        if ($cl->maxScore) {
            $cl->listBestPlayers();
        }
        $cl->seeContest();

        //claim prize
        $r = Yii::app()->request;
        $getPrize = $r->getParam('getPrize', 0);
        if ($getPrize) {
            if ($cl->claimPrize()) {
                Yii::app()->user->setFlash('success', 'A nyereményt jóváírtuk. Gratulálunk!');
                $this->redirect(['/contest/view', 'id'=>$cl->id]);
            }
        }

        $this->render('view', [
            'contestList'=>$cl,
            ]);
    }

    public function actionHistory()
    {
        $cl = new ContestList();

        $this->render('history', [
            'contestList'=>$cl,
            ]);
    }
}
