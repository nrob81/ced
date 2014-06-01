<?php
$this->pageTitle='Áron bá: ' . 'Felszerelés eladása';
$this->renderPartial('/shop/nav', ['title'=>'Felszerelés eladása']);
?>

<?php if ($transactionId): ?>
<ul class="controls-in-right" data-role="listview" data-inset="true">
    <?php Yii::app()->controller->renderPartial('_itemtosell', ['item'=>$list[$transactionId], 'page' => $page, 'mode'=>'-', 'notify'=>true]); ?>
</ul>
<?php endif; ?>

<ul class="controls-in-right" data-role="listview" data-inset="true">
    <li data-role="list-divider"><h2>Saját felszereléseid <span>eladás:</span></h2></li>
    <?php
        foreach($list as $item) {
            Yii::app()->controller->renderPartial('_itemtosell', ['item' => $item, 'page' => $page, 'mode'=>'-']);
        }
    ?>
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

<?php $this->widget('HelpWidget', ['topic'=>'shop']); ?>
