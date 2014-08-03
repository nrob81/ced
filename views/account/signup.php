<?php
$this->pageTitle = 'Regisztráció';
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'signup-form',
	'enableAjaxValidation'=>false,
    'htmlOptions'=>['data-ajax'=>'false'],
));
echo $form->errorSummary($model, '');
?>

<div data-role="fieldcontain">
	<?= $form->labelEx($model,'email'); ?>
	<?= $form->textField($model,'email'); ?>
</div>
<?= CHtml::submitButton('Regisztráció'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->

<div class="c">
Már regisztráltál?
<?= CHtml::link('Belépés', '/', ['data-role'=>'button', 'data-inline'=>'true', 'data-theme'=>'e']); ?>
</div>
