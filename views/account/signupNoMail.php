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

<div data-role="fieldcontain">
	<?= $form->labelEx($model,'username'); ?>
	<?= $form->textField($model,'username'); ?>
</div>
<div data-role="fieldcontain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->passwordField($model,'password'); ?>
</div>
<div data-role="fieldcontain">
	<?= $form->labelEx($model,'confirmPassword'); ?>
	<?= $form->passwordField($model,'confirmPassword'); ?>
</div>
<?= CHtml::submitButton('Regisztráció'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
