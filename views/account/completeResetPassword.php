<?php
$this->pageTitle = 'Elfelejtett jelszó';
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'password-form',
	'enableClientValidation'=>true,
	'enableAjaxValidation'=>false,
));
echo $form->errorSummary($model, '');
?>

<div class="ui-field-contain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->passwordField($model,'password', ['autocomplete'=>'off']); ?>
</div>
<div class="ui-field-contain">
	<?= $form->labelEx($model,'confirmPassword'); ?>
	<?= $form->passwordField($model,'confirmPassword', ['autocomplete'=>'off']); ?>
</div>
<?= CHtml::submitButton('Jelszó mentése'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
