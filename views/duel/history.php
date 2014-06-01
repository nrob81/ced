<?php
$this->pageTitle='Pecapárbaj';
$this->renderPartial('/duel/nav');
?>

<ul class="controls-in-right" data-role="listview" data-inset="true">
    <li data-role="list-divider">Legutóbbi ellenfelek</li>
    <?php
        if (count($list)) {
            foreach($list as $item) {
                Yii::app()->controller->renderPartial('_player', ['item' => $item, 'page' => 0]);
            }
        } else {
            echo '<li>Még nem párbajoztál senkivel sem.</li>';
        }
    ?>
</ul>

<?php $this->widget('HelpWidget', ['topic'=>'duel']); ?>
