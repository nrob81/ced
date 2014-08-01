<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'password-form',
	'enableClientValidation'=>true,
	'enableAjaxValidation'=>false,
));
echo $form->errorSummary($model, '');
?>

<div data-role="fieldcontain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->textField($model,'password'); ?>
</div>
<div data-role="fieldcontain">
	<?= $form->labelEx($model,'confirmPassword'); ?>
	<?= $form->textField($model,'confirmPassword'); ?>
</div>
<?= CHtml::submitButton('Jelszó mentése'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
