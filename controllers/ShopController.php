<?php
class ShopController extends GameController
{
    public function actionIndex($page = 0)
    {
        $this->room(Shop::TYPE_ITEM, $page, 'buy');
    }

    public function actionBuyBaits($page = 0)
    {
        $this->room(Shop::TYPE_BAIT, $page, 'buy');
    }

    public function actionSellItems($page = 0)
    {
        $this->room(Shop::TYPE_ITEM, $page, 'sell');
    }

    public function actionSellBaits($page = 0)
    {
        $this->room(Shop::TYPE_BAIT, $page, 'sell');
    }

    public function actionMakeSets()
    {
        $item_id = Yii::app()->request->getPost('item_id', 0);

        $shop = new Shop;
        $shop->item_type = Shop::TYPE_PART;
        $shop->fetchSets();

        try {
            if ($item_id) {
                $shop->constructItem($item_id);
            }

            if ($shop->success['setSold']) {
                Yii::app()->user->setFlash('success', 'A felszerelés elkészült!');
            }
        } catch (CFlashException $e) {
            Yii::app()->user->setFlash('error', $e->getMessage());
        }

        $this->render('makesets', [
            'list' => $shop->items,
            ]);
    }

    /**
     * @param integer $page
     * @param string $transaction
     */
    private function room($type, $page, $transaction)
    {
        $item_id = Yii::app()->request->getPost('item_id', 0);
        $amount = Yii::app()->request->getPost('amount', 0);

        $shop = new Shop;
        $shop->item_type = $type;
        $shop->page = $page;

        if ($transaction == 'buy') {
            $shop->fetchItems();
            //buy selected item
            $shop->buyItem($item_id, $amount);
        } elseif ($transaction == 'sell') {
            $shop->fetchPlayersItems();
            //sell selected item
            $shop->sellItem($item_id, $amount);
        }

        $this->render($transaction.$type.'s', [
            'list' => $shop->items,
            'pagination' => $shop->pagination,
            'count' => $shop->count,
            'page_size' => Yii::app()->params['shopItemsPerPage'],
            'owned_baits' => $shop->owned_baits,
            'owned_items' => $shop->owned_items,
            'nextItemsLevel' => $shop->nextItemsLevel,
            'page'=>$page,
            'transactionId'=>$shop->transactionId,
            ]);
    }
}
