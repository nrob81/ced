<?php
$this->pageTitle=Yii::app()->name;
?>

<?php if ((int)$code > 1): ?>
<h2>Hibakód: <?php echo $code; ?></h2>
<p>Hoppá! Hiba lépett fel a játék működése során. Utánanézünk és kijavítjuk.</p>
<?php else: ?>
<h2>Jaj ne! :(</h2>
<p><?= $message; ?></p>
<?php endif; ?>
