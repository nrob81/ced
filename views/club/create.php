<?php
$this->pageTitle = 'Klub létrehozása';
?>

<div class="nav">
    <?= CHtml::link('Klubok', ['/club/list'], ['class'=>'right ui-btn ui-mini']); ?>
    <h1>A saját klubod</h1>
</div>

<div class="responsive-a ui-grid-a ui-responsive">
    <div class="ui-block-a">

        <ul data-role="listview" data-inset="true">
            <li data-role="list-divider">klub létrehozása</li>
            <li>
                <div class="form">
                <?php $form=$this->beginWidget('CActiveForm', array(
                    'id'=>'club-form',
                    'enableAjaxValidation'=>false,
                )); ?>

                    <div data-role="fieldcontain">
                        <?= $form->error($model,'name'); ?>
                        <?= $form->labelEx($model,'name'); ?>
                        <?= $form->textField($model,'name'); ?>
                    </div>
                    <?= CHtml::submitButton('Mehet'); ?>

                <?php $this->endWidget(); ?>
                </div><!-- form -->
            </li>
        </ul>

    </div>
    <div class="ui-block-b">

        <ul data-role="listview" data-inset="true">
        <li data-role="list-divider">..miért érdemes?</li>
            <li><p>Mert semmibe sem kerül.</p></li>
            <li><p>Barátokra találhatsz.</p></li>
            <li><p>Megszerezheted a klubbal kapcsolatos érméket.</p></li>
            <li><p>Bizonyíthatod hogy jó vezető vagy, ha a klubod jó helyezést ér el a ranglistákon.</p></li>
            <li><p>Mert egy saját klubban versenyezni más klubokkal igazán nagy élmény.</p></li>
        </ul>

    </div>
</div>
