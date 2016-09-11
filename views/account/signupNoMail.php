<?php
$this->pageTitle = 'Regisztráció';
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'complete-form',
	'enableAjaxValidation'=>false,
    'htmlOptions'=>['data-ajax'=>'false'],
));
echo $form->errorSummary($model, '');
?>

<div class="ui-field-contain">
	<?= $form->labelEx($model,'username'); ?>
	<?= $form->textField($model,'username'); ?>
</div>
<div class="ui-field-contain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->passwordField($model,'password'); ?>
</div>
<div class="ui-field-contain">
	<?= $form->labelEx($model,'confirmPassword'); ?>
	<?= $form->passwordField($model,'confirmPassword'); ?>
</div>
<div class="ui-field-contain">
	<?= $form->labelEx($model,'acceptTerms'); ?>
	<?= $form->dropdownList($model,'acceptTerms', ['nem', 'igen'], ['data-role'=>'flipswitch']); ?>
</div>
<?= CHtml::submitButton('Regisztráció'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->

<div class="c">
Már regisztráltál?
<?= CHtml::link('Belépés', '/', ['class'=>'ui-btn ui-btn-inline', 'data-theme'=>'e']); ?>
</div>
