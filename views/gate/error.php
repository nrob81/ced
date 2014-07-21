<?php
$this->pageTitle=Yii::app()->name . ' - Hiba';
?>

<?php if ($code == 404): ?>
<h2>Hibakód: <?php echo $code; ?></h2>
<p>Hoppá! A keresett oldal nem található.</p>
<?php elseif ($code > 1): ?>
<h2>Hibakód: <?php echo $code; ?></h2>
<p>Hoppá! Hiba lépett fel a játék működése során. Utánanézünk és kijavítjuk.</p>
<?php else: ?>
<h2>Jaj ne! :(</h2>
<p><?= $message; ?></p>
<?php endif; ?>
