<?php if (Yii::app()->player->model->in_club == $clubID): ?>
<form action="<?= $this->createUrl('club/forum', ['id'=>$clubID]); ?>" method="post">
    <input type="text" name="post" value="" maxlength="800" placeholder="Írj valamit a többieknek.." >
    <fieldset data-role="controlgroup" data-type="horizontal" class="forum-check">
        <input type="checkbox" name="private" id="private">
        <label for="private">csak tagoknak</label>
        <?= CHtml::submitButton('küldés', ['data-theme'=>'c', 'data-inline'=>'false']); ?>
        <?= CHtml::link('Frissítés', ['', 'id'=>$clubID, 't'=>time()], ['data-role'=>'button', 'data-icon'=>'refresh', 'data-iconpos'=>'notext', 'data-inline'=>'false', 'class'=>'refresh']); ?>
    </fieldset>
</form>
<?php endif; ?>
