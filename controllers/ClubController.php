<?php
class ClubController extends GameController
{
    private $moderation = [
        'fired'=>0,
        'approved'=>0,
        'deleted'=>0
        ];

    public function actionIndex() {
        $controller = Yii::app()->player->model->in_club ? 'own' : 'list';
        $this->redirect('club/' . $controller);
    }

    public function actionList($page = 0)
    {
        $model=new Club;
        
        $model->page = $page;
        $model->fetchItems();

        $this->render('list', [
            'list'=>$model->items,
            'pagination' => $model->pagination,
            'count' => $model->count,
            'page_size' => Yii::app()->params['listPerPage'],
            'page'=>$page,
            ]);
    }
    public function actionListCompete($page = 0)
    {
        $model=new Club;
        
        $model->page = $page;
        $model->fetchItems(true);

        $this->render('listcompete', [
            'list'=>$model->items,
            'pagination' => $model->pagination,
            'count' => $model->count,
            'page_size' => Yii::app()->params['listPerPage'],
            'page'=>$page,
            ]);
    }

    public function actionCreate() {
        $model=new ClubAR;

        if(isset($_POST['ClubAR']))
        {
            $model->attributes=$_POST['ClubAR'];
            if($model->validate())
            {
                if ($model->save()) {
                    $this->redirect(['club/own']);
                }
            }
        }

        $this->render('create', [
            'model'=>$model,
            ]);
    }
    
    public function actionDetails($id) 
    {
        $player = Yii::app()->player->model;

        $clubID = (int)$id;
        $in_club = (int)Yii::app()->player->model->in_club;
        if ($in_club == $id) {
            $this->redirect('/club/own');
        }
        //members
        $club = new Club;
        $club->id = $clubID;
        $club->fetch();
        
        if (!$club->id) {
            throw new CHttpException(404, 'A keresett klub nem található.');
        }
        $club->fetchMembers();
        
        $r = Yii::app()->request;
        $join = $r->getParam('join', 0);
        if ($join) {
            try {
                $club->joinRequest($club->id);
                Yii::app()->user->setFlash('success', 'A csatlakozási kérelmet elküldted. Amint elfogadja valamelyik klubtag, értesítünk.');
            } catch (CFlashException $e) {
                Yii::app()->user->setFlash('error', $e->getMessage());
            }
        }
        $delete = $r->getParam('deleteJoin', 0);
        if ($delete) {
            $club->deleteOwnJoinRequest($club->id);
            Yii::app()->user->setFlash('success', 'A csatlakozási kérelmet visszavontad.');
        }
        
        $forum = new Forum;
        
        //challenge
        $ch = new Challenge;
        $ch->caller = $in_club;
        $ch->opponent = $clubID;
        $ch->fetchActiveChallenge();

        $call = $r->getParam('call', 0);
        if ($call) {
            try {
                $ch->caller = $in_club;
                $ch->opponent = $clubID;

                $ch->callToChallenge($club);
                Yii::app()->user->setFlash('success', 'Versenyre hívtad a klubot!');

                $forum->id = $clubID;
                $message = $player->user . ' a saját klubja nevében versenyre hívta a klubot.';
                $forum->save($message, true);

                $forum->id = $player->in_club;
                $message = $player->user . ' versenyre hívta a következő klubot: ' . $club->name;
                $forum->save($message, true);
            } catch (CFlashException $e) {
                Yii::app()->user->setFlash('error', $e->getMessage());
            }
        }
        

        $forum->id = $clubID;
        $forum->fetchItems();

        $this->render('details', [
            'clubID'=>$clubID,
            'club'=>$club,
            'challenge'=>$ch,
            'list'=>$forum->items,
            'pagination' => $forum->pagination,
            'count' => $forum->count,
            'page_size' => Yii::app()->params['listPerPage'],
            'page'=>0,
            'moderation'=>$this->moderation,
            ]);
    }
    public function actionOwn($page = 0) 
    {
        $player = Yii::app()->player->model;
        if (!$player->in_club) {
            $this->redirect(['club/list']);
        }

        //forum
        $forum = new Forum;
        $forum->id = $player->in_club;
        $forum->fetchItems();

        //members
        $club = new Club;
        $club->id = $player->in_club;
        $club->fetch();
        $club->fetchMembers();
        
        //challenge
        $ch = new Challenge;
        //$ch->caller = $player->in_club;
        $ch->opponent = $club->id;
        $ch->fetchActiveChallenge();

        try {
            $this->fireMember($club, $ch, $forum);
            $this->acceptApproval($club, $forum);
            $this->deleteApproval($club, $forum);
            $this->switchCompete($club, $ch);

        } catch (CFlashException $e) {
            Yii::app()->user->setFlash('error', $e->getMessage());
        }

        $this->render('own', [
            'clubID'=>$player->in_club,
            'club'=>$club,
            'challenge'=>$ch,
            'list'=>$forum->items,
            'pagination' => $forum->pagination,
            'count' => $forum->count,
            'page_size' => Yii::app()->params['listPerPage'],
            'page'=>$page,
            'moderation'=>$this->moderation,
            ]);
    }

    private function fireMember($club, $challenge, $forum)
    {
        $player = Yii::app()->player->model;

        $fire = (int)Yii::app()->request->getParam('fire', false);
        if ($fire) {
            if ($challenge->active) {
                throw new CFlashException('Verseny közben nem lehet tagot kidobni.');
            }

            $fireUser = isset($club->members[$fire]['user']) ? $club->members[$fire]['user'] : '???';
            if ($club->fireMember($fire)) {
                $this->moderation['fired'] = $fire;
                //forum notice
                $message = $fire==$player->uid ? $player->user . ' kilépett a klubból.' : $player->user . ' visszavonta ' . $fireUser . ' tagságát.';
                $forum->save($message, true);
                //wall notice
                $this->wallNotice($club, Wall::TYPE_CLUB_FIRE, $fire);
            }
        }
    }

    private function acceptApproval($club, $forum)
    {
        $player = Yii::app()->player->model;

        $approve = (int)Yii::app()->request->getParam('approve', false);
        if ($approve) {
            if ($club->approveMember($approve)) {
                $this->moderation['approved'] = $approve;
                //forum notice
                $message = $player->user . ' elfogadta ' . $club->members[$approve]['user'] . ' felvételi kérelmét.';
                $forum->save($message, true);
                //wall notice
                $this->wallNotice($club, Wall::TYPE_CLUB_APPROVE, $club->members[$approve]['uid']);
            }
        }
    }

    private function deleteApproval($club, $forum)
    {
        $player = Yii::app()->player->model;

        $delete = (int)Yii::app()->request->getParam('delete', false);
        if ($delete) {
            $deleteUser = isset($club->entrants[$delete]['user']) ? $club->entrants[$delete]['user'] : '???';
            if ($club->deleteJoinRequest($delete)) {
                $this->moderation['deleted'] = $delete;
                //forum notice
                $message = $player->user . ' elutasította ' . $deleteUser . ' felvételi kérelmét.';
                $forum->save($message, true);
                //wall notice
                $this->wallNotice($club, Wall::TYPE_CLUB_DELETE, $delete);
            }
        }
    }

    private function switchCompete($club, $challenge)
    {
        $switch = Yii::app()->request->getParam('switch', '');
        if ($switch == 'compete') {
            if ($club->would_compete & $challenge->underLastCallTimeLimit($club->id)) {
                throw new CFlashException('A \'versenyezne\' beállítást csak akkor kapcsolhatod ki, ha legalább '. Challenge::TIME_LIMIT_LASTCALL_HOURS .' óra eltelt azóta, hogy a klubod valakit versenyre hívott.');
            }

            $club->switchCompete();
        }
    }

    private function wallNotice($club, $type, $uid) {
        $player = Yii::app()->player->model;

        //wall notice
        $wall = new Wall;
        $wall->content_type = $type;
        $wall->uid = $uid;
        $wall->add([
            'clubID'=>$player->in_club,
            'clubName'=>$club->name,
            'moderatorUid'=>$player->uid,
            'moderator'=>$player->user,
            ]);
    }
    
    public function actionForum($id, $page = 0) 
    {
        $clubID = (int)$id;

        $forum = new Forum;
        $forum->id = $clubID;

        $r = Yii::app()->request;
        $post = $r->getPost('post', false);
        if ($post) {
            $forum->private = $r->getPost('private', false);
            $forum->save($post);
        }
        $delete = $r->getParam('delete', 0);
        if ($delete) {
            $forum->delete($delete);
        }

        $forum->page = $page;
        $forum->fetchItems();

        $this->render('forum', [
            'clubID'=>$clubID,
            'list'=>$forum->items,
            'pagination' => $forum->pagination,
            'count' => $forum->count,
            'page_size' => Yii::app()->params['listPerPage'],
            'page'=>$page,
            ]);
    }

    public function actionHistory($id = 0) {
        if (!$id) {
            $this->redirect(['/club/list']);
        }

        $club = new Club;
        $club->id = (int)$id;
        $club->fetch();
        $club->fetchChallenges();
        
        $this->render('history', [
            'club'=>$club,
            ]);
    }

    public function actionClose() {
        $player = Yii::app()->player->model;
        if (!$player->in_club) {
            $this->redirect(['club/list']);
        }
        
        //members
        $club = new Club;
        $club->id = $player->in_club;
        $club->fetch();
        $club->fetchMembers();
        
        $pass = Yii::app()->request->getPost('pass', '');
        if ($pass) {
            try {
                if ($club->close($pass)) {
                    foreach ($club->members as $member) {
                        $this->wallNotice($club, Wall::TYPE_CLUB_CLOSE, $member['uid']);                        
                    }
                    Yii::app()->user->setFlash('success', "A klubot megszüntetted.");
                    $this->redirect(['club/list']);                
                }
            } catch (CFlashException $e) {
                Yii::app()->user->setFlash('error', $e->getMessage());
            }
        }

        $this->render('close', [
            'club'=>$club,
            ]);
    }
}
