<?php
$this->pageTitle='Áron bá: ' . 'Csalik vásárlása';
$this->renderPartial('/shop/nav', ['title'=>'Csalik vásárlása']);
?>

<?php if ($transactionId): ?>
<ul class="controls-in-right" data-role="listview" data-inset="true">
    <?php Yii::app()->controller->renderPartial('_item', ['item'=>$list[$transactionId], 'page' => $page, 'mode'=>'+', 'notify'=>true]); ?>
</ul>
<?php endif; ?>

<div class="shop-view">
    <ul class="controls-in-right" data-role="listview" data-inset="true">
        <li data-role="list-divider"><h2>Áron bá kínálata <span>vásárlás:</span></h2></li>
        <?php
            foreach($list as $item) {
                Yii::app()->controller->renderPartial('_item', ['item' => $item, 'page' => $page, 'mode'=>'+']);
            }
        ?>
        <li class="routine">
            <h3>Áron bá</h3>
            <p>
            <?php if($nextItemsLevel): ?>
                Újabb csalikkal akkor szolgálhatok, ha eléred a következő szintet: <strong><?= $nextItemsLevel ?></strong>.
            <?php else: ?>
                Egyelőre nem tudom, mikor kapok új csalikat. Szólok majd, ha változik a helyzet.
            <?php endif; ?>
            </p>
        </li>
    </ul>

    <div class="center-wrapper">
    <?php 
    $this->widget('JqmLinkPager', array(
        'currentPage'=>$pagination->getCurrentPage(),
        'itemCount'=>$count,
        'pageSize'=>$page_size,
    ));
    ?>
    </div>
</div>

<?php $this->widget('HelpWidget', ['topic'=>'shop']); ?>
