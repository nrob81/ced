<?php
$this->pageTitle = 'Játékos mentése';
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
	<?= $form->textField($model,'password'); ?>
</div>
<?= CHtml::submitButton('Mentés'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
