<?php
$this->pageTitle=Yii::app()->name;
$this->pageTitle='Arany felhasználása';
?>

<?php if ($banner): ?>
<ul data-role="listview" data-inset="true" data-theme="d">
<li class="alert-info"><?= $banner; ?></li>
</ul>
<?php endif; ?>

<div id="store" class="responsive-a ui-grid-a ui-responsive">
    <div class="ui-block-a">
        <h2>Aranyad: <?= Yii::app()->player->model->gold; ?>
        <?php if (Yii::app()->params['isPartOfWline']): ?>
        <a href="<?= $this->createUrl('gate/bank'); ?>" class="ui-btn ui-btn-inline ui-mini" data-theme="e">Szerezz aranyat zsetonnal</a></h2>
        <?php endif; ?>

        <h3>újdonság: Hiányzó szett elem vásárlása</h3>

        <form action="<?= $this->createUrl(''); ?>" method="post">
            <fieldset data-role="controlgroup" data-iconpos="right">
                <legend>Áron bá készlete:</legend>
                <?php foreach ($store->listMissingSetItems() as $item): ?>
                <input name="setItem" value="<?= $item->id; ?>" id="radio-<?= $item->id; ?>" type="radio" data-theme="d">
                <label for="radio-<?= $item->id; ?>"><?= $item->title; ?></label>
                <?php endforeach; ?>
            </fieldset>
            <input value="Kiválasztott elem megvásárlása: 100 aranyért" type="submit">
        </form>
    </div>
    <div class="ui-block-b">
        <h3>Párbaj-pajzs</h3>
        <p>Ha nem szeretnéd, hogy párbajra hívjanak, kapcsold be a pajzsot. Nyugi, a többi horgász csak annyit lát majd, hogy nincs energiád és ezért nem hívhat párbajra.</p>
        <?php if ($duelShield->getLifetime() > 0): ?>
        <a href="<?= $this->createUrl(''); ?>" class="ui-btn ui-btn-icon-right ui-icon-refresh" data-theme="d">Jelenleg aktív a pajzsod. Az élettartama: <span id="shieldLifeTime"><?= Time::secondsToDifference($duelShield->lifeTime) ?></span></a>
        <?php
            Yii::app()->clientScript->registerScript('cntr', "
                $('#shieldLifeTime').countdown({
                    until: {$duelShield->getLifetime()},
                    layout: '{hn}{sep}{mnn}{sep}{snn}',
                    expiryText: '00:00:00',
                })");
        ?>
        <?php else: ?>
        <form action="<?= $this->createUrl(''); ?>" method="post">
            <select name="shield" id="shield-time" data-theme="d" data-inline="true">
                <?php foreach($shieldPrices as $interval => $categ): ?>
                <option value="<?= $interval; ?>"><?= $categ['label'] . ': ' . $categ['price'] . ' aranyért'; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Bekapcsolom">
        </form>
        <?php endif; ?>

        <h3>Energiaital</h3>
        <p>Energiaitallal teljesen, vagyis <?= Yii::app()->player->model->energy_max ?>-ig feltöltheted az energiádat.</p>
        <form action="<?= $this->createUrl(''); ?>" method="post">
            <input type="hidden" name="energy" value="1"/>
            <input type="submit" value="Energiaital: 20 aranyért">
        </form>
    </div>
</div>
