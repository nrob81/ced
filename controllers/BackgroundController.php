<?php
class BackgroundController extends CronController
{
    public function actionFinishChallenges()
    {
        $mc = new MaintenanceChallenge;
        $mc->fetchFinishable();
        $mc->process();

        echo 'ok';
    }

    public function actionReset()
    {
        $mp = new MaintenancePlayer;

        $user = Yii::app()->request->getParam('user', 'x');
        echo $user . "<br/>";
        
        if ($user) {
            $mp->setUid($user);
        }
        $mp->reset();
        
        $this->render('//site/dummy', ['log'=>$mp->log]);
    }

    public function actionContestStart($addPoints = 0)
    {
        $contest = new Contest;
        if ($contest->activeId) {
            return true;
        }
        echo 'started, ';

        $contest->create();
        
        if ($addPoints) {
            for ($i=0; $i<1000; $i++) {
                $contest->addPoints(rand(1981, 2100), Contest::ACT_MISSION, 1, 1, 1); //activity, uid, xp, dollar,
            }
        }
    }
    
    public function actionContestStop()
    {
        $contest = new Contest;
        if (time() > $contest->activeId + CONTEST::LIFETIME) {
            echo 'stopped:'. $contest->activeId;
            $contest->complete();
        }
    }
    
    public function actionContestStartStop($addPoints = 0)
    {
        $this->actionContestStart($addPoints);
        $this->actionContestStop();
    }

    public function actionNewLevelRevards()
    {
        $res = Yii::app()->db->createCommand()
            ->select('uid, routine')
            ->from('visited')
            ->where('routine >= 9')
            ->order('uid')
            ->queryAll();
        $users = [];
        foreach ($res as $d) {
            //pay for gold routine
            @$users[$d['uid']]['gold'] += 30;
            @$users[$d['uid']]['r_gold']++;
            
            //pay for diamand
            if ($d['routine'] >= 81) {
                $users[$d['uid']]['gold'] += 70;
                @$users[$d['uid']]['r_diamant']++;
            }
        }
        
        $log = print_r($users, true);
        $wall = new Wall();
        foreach ($users as $uid => $award) {
            Yii::app()->db->createCommand("UPDATE main SET gold=gold+{$award['gold']} WHERE uid={$uid}")->execute();
            
            $wall->content_type = Wall::TYPE_NEW_AWARD;
            $wall->uid = $uid;
            $wall->add([
                'award'=>$award['gold'],
                'r_gold'=>$award['r_gold'],
                'r_diamant'=>(int)@$award['r_diamant']
                ]);
        }
        $this->render('//site/dummy', ['log'=>$log]);
    }
}
