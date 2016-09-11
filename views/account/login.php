<?php
$this->contentClass='water water-guest';
$this->pageTitle=Yii::app()->name;
?>

<div class="form login-form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableAjaxValidation'=>false,
    'htmlOptions'=>['data-ajax'=>'false'],
));
echo $form->errorSummary($model, '');
?>

<div data-role="fieldcontain">
	<?= $form->labelEx($model,'email'); ?>
	<?= $form->textField($model,'email'); ?>
</div>
<div data-role="fieldcontain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->passwordField($model,'password'); ?>
</div>

<fieldset class="ui-grid-a">
    <div class="ui-block-b"><?= CHtml::submitButton('Bejelentkezés'); ?></div>
    <div class="ui-block-b"><?= CHtml::link('Regisztráció', ['account/signup'], ['class'=>'ui-btn', 'data-theme'=>'e']); ?></div>
</fieldset>

<?php $this->endWidget(); ?>

</div><!-- form -->
