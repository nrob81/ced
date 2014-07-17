<?php
class SiteController extends GameController
{
    public function actionIndex()
    {
        $wall = new Wall;
        $wall->uid = Yii::app()->player->uid;
        $wall->fetchPosts();

        if (Yii::app()->player->model->level < 2) {
            Yii::app()->user->setFlash('info', 'Kezdetnek teljesíts pár megbízást. :) A megbízásokat a '. CHtml::link('Navigáció', ['#left-panel'], ['data-role'=>'button', 'data-inline'=>'true', 'id'=>'nav', 'data-icon'=>'bars', 'data-iconpos'=>'notext']) . ' ikon lenyomásával érheted el.');
        }

        $this->render('index', [
            'posts'=>$wall->posts,
            ]);        
    }

    public function actionCredits()
    {
        $this->render('credits');
    }

    public function actionStory()
    {
        $this->render('story');
    }

    public function actionHelp()
    {
        $help = new Help;
        $news = [];
        foreach ($help->topics as $topic => $name) {
            $help->topic = $topic;
            $help->fetchItems(1);
            $items = $help->items;

            $news[$topic] = [
                'title'=>$name,
                'body'=>array_shift($items)
                ];
        }

        $this->render('help', [
            'news'=>$news,
            ]);
    }

    public function actionHelpTopic($t = '')
    {
        $help = new Help;
        $topics = $help->topics;
        if (!array_key_exists($t, $topics)) {
            Yii::app()->user->setFlash('error', 'A választott súgótémakör nem létezik.');
            $this->redirect(['site/help']);
        }

        $help->topic = $t;
        $help->fetchItems();

        $this->render('helptopic', [
            'title'=>$topics[$t],
            'items'=>$help->items,
            ]);
    }

    public function actionSms($id = 1)
    {
        $store = new Store;
        $store->uid = Yii::app()->player->model->uid;
        if (!array_key_exists($id, $store->packagesSms)) {
            $id = 1;
        }

        $this->render('sms', [
            'package'=>$store->packagesSms[$id],
            ]);
    }

    public function actionStore()
    {
        $store = new Store;
        $store->uid = Yii::app()->player->model->uid;
        $store->fetch();

        $r = Yii::app()->request;

        //energy drink
        $energy = (int)$r->getPost('energy', 0);
        try {
            if ($energy) {
                if ($store->refillEnergy()) {
                    Yii::app()->user->setFlash('success', "Megittál egy energiaitalt, ezáltal teljesen feltöltődtél. Remélem ízlett.");
                    $this->redirect(['']);
                }
            }
        } catch (CFlashException $e) {
            Yii::app()->user->setFlash('error', $e->getMessage());
        }

        //black market
        $blackMarket = (int)$r->getPost('blackMarket', 0);
        try {
            if ($blackMarket) {
                if ($store->activateBlackMarket()) {
                    Yii::app()->user->setFlash('success', "10 percig pult alól vásárolhatsz csalit Áron bá boltjában.");
                    $this->redirect(['/shop/buyBaits']);
                }
            }
        } catch (CFlashException $e) {
            Yii::app()->user->setFlash('error', $e->getMessage());
        }


        $this->render('store', [
            'store'=>$store,
            ]);
    }
}
