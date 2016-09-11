<div class="nav">
    <?= CHtml::link('vissza', ['/player'], ['class'=>'right ui-btn', 'data-mini'=>'true']); ?>
    <h1>E-mail beállítása</h1>
</div>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'email-form',
	'enableClientValidation'=>true,
	'enableAjaxValidation'=>false,
));
echo $form->errorSummary($model, '');
?>

<ul data-role="listview" data-inset="true" data-theme="d">
    <li>jelenlegi e-mail címed: <strong class="success"> <?= $account->email; ?></strong></li>
    <?php if ($account->emailTemp): ?>
    <li>aktiválásra vár: <strong class="error"> <?= $account->emailTemp; ?></strong></li>
    <?php endif; ?>
</ul>
<p>
Az e-mail megváltoztatáshoz először add meg a jelenlegi jelszavadat:
<div data-role="fieldcontain">
	<?= $form->labelEx($model,'password'); ?>
	<?= $form->passwordField($model,'password'); ?>
</div>
.. majd add meg az új e-mail címet:
<div data-role="fieldcontain">
	<?= $form->labelEx($model,'email'); ?>
	<?= $form->textField($model,'email'); ?>
</div>
<?= CHtml::submitButton('E-mail mentése'); ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
