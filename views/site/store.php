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
        <h2>Aranyad: <?= Yii::app()->player->model->gold; ?></h2>

        <h3>újdonság: Párbaj-pajzs</h3>
        <p>Ha nem szeretnéd, hogy párbajra hívjanak, kapcsold be a pajzsot. Nyugi, a többi horgász csak annyit lát majd, hogy nincs energiád és ezért nem hívhat párbajra.</p>
        <?php if ($duelShield->getLifetime() > 0): ?>
        <a href="<?= $this->createUrl(''); ?>" data-role="button" data-theme="d" data-icon="refresh">Jelenleg aktív a pajzsod. Az élettartama: <span id="shieldLifeTime"><?= Time::secondsToDifference($duelShield->lifeTime) ?></span></a>
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

        <?php if ($store->blackBait['id']): ?>
        <h3>Feketepiac</h3>
        <p>A következő 10 percben olyan <?= CHtml::link('csalit vásárolhatsz', ['/shop/buyBaits']); ?> Áron bá boltjában, ami most még nem elérhető számodra.</p>
        <p>Ezt a csalit veheted meg pult alól: <strong> <?= $store->blackBait['title'] . ', ára: ' . $store->blackBait['price'] . '$'; ?></strong></p>
        <form action="<?= $this->createUrl(''); ?>" method="post">
            <input type="hidden" name="blackMarket" value="1"/>
            <input type="submit" value="Feketepiac 10 aranyért">
        </form>
        <?php endif; ?>
    </div>
    <div class="ui-block-b">
        <h2>Arany vásárlása</h2>


        <div data-role="collapsible-set" data-theme="b" data-content-theme="c" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d" class="board-menu">
        <div data-role="collapsible" data-inset="false" data-collapsed="false">
            <h2>SMS</h2>
            <ul data-role="listview" data-theme="d">
                <?php foreach ($store->packagesSms as $id => $package): ?>
                <li><a href="<?= $this->createUrl('sms', ['id'=>$id]); ?>">
                    <img src="/images/sms_icon.png">
                    <h2><?= $package['price']; ?> HUF</h2>
                    <p><?= $package['descr'] ?><?= $package['discount']?" <span class=\"success\">({$package['discount']}% ajándék)</span>":''; ?></p>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </div><!-- /collapsible -->
        </div>

    </div>
</div>
