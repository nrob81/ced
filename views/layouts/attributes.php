<div id="attributes" class="ui-grid-c attributes">
    <div class="block ui-block-a">
        <div class="pb_wrap">
            <span class="pb_txt"><?= Yii::app()->player->model->level ?>. szint</span>
            <div class="progressbar"><div style="width:<?= Yii::app()->player->model->level_percent ?>%"></div></div>
        </div>
        <span class="hint">
<?php 
if (Yii::app()->player->model->status_points) {
    echo CHtml::link('Fejlődhetsz!', ['/player']);
} else {
    echo 'szintlépésig: ' . Yii::app()->player->model->xp_remaining . ' tp';
}
?>
        </span>
    </div>
    <div class="block spr block-energy ui-block-b">
        <p id="energy"><?= Yii::app()->player->model->energy ?>/<?= Yii::app()->player->model->energy_max ?></p>
<?php 
        if (Yii::app()->player->model->energy_missing>0 and Yii::app()->player->model->remainingTimeToRefill > 0): ?>
            <span id="refillTime" class="hint-indent"><?= Time::secondsToDifference(Yii::app()->player->model->remainingTimeToRefill) ?> múlva +<?= Yii::app()->player->model->refillPerInterval ?></span>
        <?php endif; ?>
    </div>
    <div class="block spr block-dollar ui-block-c"><p><?= Yii::app()->player->model->dollar ?>$</p></div>
    <div class="block spr block-gold ui-block-d">
        <p><?= Yii::app()->player->model->gold ?> arany</p>
        <span class="hint-indent"><?= CHtml::link('+energia', ['site/store']); ?></a>
    </div>
</div>
