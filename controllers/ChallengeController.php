<?php
class ChallengeController extends GameController
{
    public function actionDetails($id)
    {
        $ch = new Challenge;
        $ch->id = $id;
        $ch->fetch();

        $ch->fetchListDuels();

        $this->render('details', [
            'challenge'=>$ch,
            ]);
    }
}
