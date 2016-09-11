<?php
$this->pageTitle = 'Elfelejtett jelszó';
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'resetpassword-form',
	'enableAjaxValidation'=>false,
    'htmlOptions'=>['data-ajax'=>'false'],
));
echo $form->errorSummary($model, '');
?>

<p>Add meg az felhasználónevedhez tartozó e-mail címet, hogy elküldhessük a jelszó visszaállításához szükséges információkat.</p>

<div data-role="fieldcontain">
	<?= $form->labelEx($model, 'email'); ?>
	<?= $form->textField($model, 'email'); ?>
</div>
<?= CHtml::submitButton('Jelszó visszaállítása'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->

<div class="c">
<?= CHtml::link('vissza', '/', ['class'=>'ui-btn ui-btn-inline', 'data-theme'=>'e']); ?>
</div>
