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

<div class="c">
<?= CHtml::submitButton('Bejelentkezés', ['data-inline' => 'true']); ?><br/>
<?= CHtml::link('Regisztráció', ['account/signup'], ['data-inline'=>'true', 'data-role'=>'button', 'data-theme'=>'e']); ?>
</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
