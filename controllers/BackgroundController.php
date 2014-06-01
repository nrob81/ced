<?php
class BackgroundController extends CronController
{
    public function actionFinishChallenges() {
        $mc = new MaintenanceChallenge;
        $mc->fetchFinishable();
        $mc->process();

        //$this->render('//site/dummy');
        echo 'ok';
    }

    public function actionReset() {
        $mp = new MaintenancePlayer;

        $user = Yii::app()->request->getParam('user', 'x');
        echo $user . "<br/>";
        
        if ($user) $mp->setUid($user);
        $mp->reset();
        
        $this->render('//site/dummy', ['log'=>$mp->log]);
    }

    public function actionContestStart($addPoints = 0) {
        $contest = new Contest;
        if ($contest->activeId) return true;
        echo 'started, ';

        //Yii::app()->redis->getClient()->set('contest:r_collect', 'xp'); //set recommended collection type for testing
        $contest->create();
        
        if ($addPoints) {
            for ($i=0; $i<1000; $i++) {
                $contest->addPoints(rand(1981, 2100), Contest::ACT_MISSION, 1, 1, 1); //activity, uid, xp, dollar,
            }
        }
    }
    
    public function actionContestStop() {
        $contest = new Contest;
        
        if (time() > $contest->activeId + CONTEST::LIFETIME) {
            echo 'stopped:'. $contest->activeId;
            $contest->complete();
        }
    }
    
    public function actionContestStartStop($addPoints = 0) {
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
        
        $log = print_r($users, 1);
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

    public function actionLevels() {
        $log = '';

        $xpAll = 10;
        $xpRecommended = 10;
        $rec = 10;
            $log .= "0. xpRec:0, -------, [{$rec}]<br/>";

        for ($l=0; $l<=200; $l++) {
            $rec = $this->nextXpRecommended($l);
            $log .= "{$l}. xpRec:{$xpRecommended}, all:{$xpAll}, [{$rec}]<br/>";
            //$log .= "{$l} => {$xpAll},<br/>";
            $xpRecommended += $rec;
            $xpAll += $xpRecommended;
        }
    
        $this->render('//site/dummy', ['log'=>$log]);
    }
    private function nextXpRecommended($level) {
        $recommendations = [
            //fromLevel => recommended xp gain to the NEXT level
            1 => 3,
            5 => 5,
            10 => 10,
            20 => 15,
            30 => 20,
            40 => 30,
            50 => 10,
            80 => 15,
            90 => 20,
            100 => 20,
            120 => 35,
            140 => 40,
            160 => 30,
            180 => 45,
            200 => 50,
            ];
        $search = $level+1;
        for ($i=$search; $i>0; $i--) {
            if (isset($recommendations[$i])) {
                return $recommendations[$i];
            }
        }

        return 0;
    }
}
