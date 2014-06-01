<div class="nav">
    <?php if (isset($reel)): ?>
        <div class="spr reel"></div>
    <?php else: ?>
    <p id="supply">
        készleted:<br/>
        <?= Yii::app()->player->model->owned_items ?> felszerelés<br/>
        <?= Yii::app()->player->model->owned_baits ?> csali<br/>
        <?= Yii::app()->player->model->freeSlots ?> szabad hely
    </p>
    <?php endif; ?>
    <h1><?= $title ?></h1>
</div>
