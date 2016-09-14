<?php
$this->pageTitle = 'Klubok';
?>

<div class="nav">
    <?= CHtml::link('Saját klub', ['club/create'], ['data-role'=>'button', 'data-mini'=>'true', 'class'=>'right']); ?>
    <h1>Klubok</h1>
</div>

<ul class="list controls-in-right" data-role="listview" data-inset="true">
    <li data-role="list-divider"><?= CHtml::link('Összes klub', ['club/list']); ?> | Versenylista</li>
    <?php
        foreach($list as $item) {
            Yii::app()->controller->renderPartial('_item', ['item' => $item, 'page' => $page]);
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

<?php $this->widget('HelpWidget', ['topic'=>'club']); ?>
