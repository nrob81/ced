<li>
    <img src="/images/timer.png" alt="Óra" />
    <?php if ($active): ?>
    <h1>A verseny folyamatban van.</h1>
    <p>Eredményhirdetésig hátralévő idő: <strong id="contEndTime"><?= Time::secondsToDifference($secUntilEnd); ?></strong></p>
    <?php else: ?>
    <h1>A verseny véget ért.</h1>
    <?php endif; ?>
</li>

<?php
if ($secUntilEnd > 0) {
    Yii::app()->clientScript->registerScript('cntr', "
        $('#contEndTime').countdown({
            until: {$secUntilEnd},
            format: 'HMS',
            layout: '{hnn}{sep}{mnn}{sep}{snn}',
            expiryText: 'véget ért',
        })");
}
?>
