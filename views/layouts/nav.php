<div data-role="panel" id="left-panel">
    <ul data-role="listview">
        <li><?= CHtml::link('Főoldal', ['/site']); ?></li>
        <li><?= CHtml::link('Megbízások', ['/missions']); ?></li>
        <li><?= CHtml::link('Pecapárbaj', ['/duel']); ?></li>
    </ul>
    
    <div data-role="collapsible" data-inset="false" data-iconpos="right" data-content-theme="e">
      <h3>Áron bá boltja</h3>
      <ul data-role="listview">
        <li><?= CHtml::link('Felsz. vásárlása', ['/shop']); ?></li>
        <li><?= CHtml::link('Csalik vásárlása', ['/shop/buyBaits']); ?></li>
        <li><?= CHtml::link('Felsz. eladása', ['/shop/sellItems']); ?></li>
        <li><?= CHtml::link('Csalik eladása', ['/shop/sellBaits']); ?></li>
        <li><?= CHtml::link('Szett elemek', ['/shop/makeSets']); ?></li>
      </ul>
    </div><!-- /collapsible -->

    <ul data-role="listview">
        <li<?= $clubsAttr; ?>><?= CHtml::link('Klubok'.$clubsBubble, ['/club']); ?></li>
        <li<?= $contestAttr; ?>><?= CHtml::link('Horgászverseny'.$contestBubble, ['/contest']); ?></li>
        <li><?= CHtml::link('Ranglisták', ['/leaderboard']); ?></li>
        <li><?= CHtml::link('Profil', ['/player']); ?></li>
        <li><?= CHtml::link('Súgó', ['/site/help']); ?></li>
    </ul>
        
    <?php
        if (Yii::app()->params['isPartOfWline']) {
            echo CHtml::link('wline.hu', ['/gate/logout'], ['data-role'=>'button', 'data-theme'=>'d', 'data-mini'=>'true']);
        } else {
            echo CHtml::link('kilépés', ['/site/logout'], ['data-role'=>'button', 'data-theme'=>'d', 'data-mini'=>'true']);
        }
    ?>
</div><!-- /panel -->
