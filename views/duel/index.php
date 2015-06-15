<?php
$this->pageTitle='Pecapárbaj';
$this->renderPartial('/duel/nav');
?>

<ul class="controls-in-right" data-role="listview" data-inset="true">
    <li data-role="list-divider">Ők versenyeznének</li>
    <?php
        if (count($list)) {
        foreach($list as $item) {
            Yii::app()->controller->renderPartial('_player', ['item' => $item, 'page' => $page]);
        }
        } else {
            echo '<li>Jelenleg nem találtunk megfelelő ellenfelet.</li>';
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

<?php $this->widget('HelpWidget', ['topic'=>'duel']); ?>
