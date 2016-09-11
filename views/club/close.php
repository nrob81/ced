<?php
$this->pageTitle = 'Klub megszüntetése';
?>

<div class="nav">
    <?= CHtml::link('vissza', ['/club'], ['class'=>'right ui-btn ui-mini']); ?>
    <h1><?= $club->name; ?> bezárása</h1>
</div>

<div class="responsive-a ui-grid-a ui-responsive">
    <div class="ui-block-a">
        <ul data-role="listview" data-inset="true">
            <li>
                <p>
                <?php if (Yii::app()->player->model->uid <> $club->owner): ?>
                    A klubot csak az alapító szüntetheti meg.
                <?php else: ?>
                    Biztos vagy benne, hogy végleg bezárod a klubot?<br/>
                    Ha tényleg ezt szeretnéd, írd be a jelszavad a mezőbe és kattints a gombra.
                <?php endif; ?>
                </p>
            </li>
        </ul>
    </div>
    <div class="ui-block-b">
        <ul data-role="listview" data-inset="true">
            <li>
            <div class="form">
                <?php if (Yii::app()->player->model->uid == $club->owner): ?>
                    <form action="<?= $this->createUrl('club/close');?>" method="post">
                    <input type="password" name="pass" id="pass" placeholder="jelszavad" value="" autocomplete="off">
                    <input type="submit" value="Klub megszüntetése">
                    </form>
                <?php endif; ?>
            </div><!-- form -->
            </li>
        </ul>
    </div>
</div>
