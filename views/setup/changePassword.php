<div class="nav">
    <?= CHtml::link('vissza', ['/player'], ['class'=>'right ui-btn ui-mini']); ?>
    <h1>Jelszócsere</h1>
</div>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'password-form',
	'enableClientValidation'=>true,
	'enableAjaxValidation'=>false,
));
echo $form->errorSummary($model, '');
?>
A jelszó megváltoztatáshoz először add meg a jelenlegi jelszavadat:
<div class="ui-field-contain">
	<?= $form->labelEx($model,'oldPassword'); ?>
	<?= $form->passwordField($model,'oldPassword'); ?>
</div>
.. majd add meg az új jelszót:
<div class="ui-field-contain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->passwordField($model,'password'); ?>
</div>
<div class="ui-field-contain">
	<?= $form->labelEx($model,'confirmPassword'); ?>
	<?= $form->passwordField($model,'confirmPassword'); ?>
</div>
<?= CHtml::submitButton('Jelszó mentése'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
