<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'club-form',
	'enableAjaxValidation'=>false,
    'htmlOptions'=>['data-ajax'=>'false'],
)); ?>

<div data-role="fieldcontain">
    <?= $form->error($model,'name'); ?>
	<?= $form->labelEx($model,'name'); ?>
	<?= $form->textField($model,'name'); ?>
</div>
<?= CHtml::submitButton('Mehet'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
