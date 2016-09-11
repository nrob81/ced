<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/favicon.ico" type="image/x-icon" />
</head>
<body>

    <div data-role="page" id="index">

        <div data-role="header" id="game-header">
                <?php
                $attr = ['id'=>'nav', 'class'=>'ui-btn ui-shadow ui-corner-all ui-icon-bars ui-btn-icon-notext'];

                $clubsBubble = $contestBubble = '';
                //check active contest
                if (Yii::app()->player->newContest) {
                    $attr['class'] = 'nav-alert ui-btn ui-shadow ui-corner-all ui-icon-alert ui-btn-icon-notext';
                    $contestBubble = '<span class="menu-club-challenge"> !!</span>';
                }

                //check clubchallenge
                if (Yii::app()->player->clubChallenge) {
                    $attr['class'] = 'nav-alert ui-btn ui-shadow ui-corner-all ui-icon-alert ui-btn-icon-notext';
                    $clubsBubble = '<span class="menu-club-challenge"> - verseny</span>';
                }

                echo CHtml::link('Navigáció', ['#left-panel'], $attr);
                if(Yii::app()->player->uid) {
                    $this->renderPartial('/layouts/attributes');
                } else {
                    $this->renderPartial('/layouts/empty');
                }
                ?>
        </div><!-- /header -->

        <!-- content -->
        <?= $content; ?>
        <!-- /content -->

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
                <li><?= CHtml::link('Klubok'.$clubsBubble, ['/club']); ?></li>
                <li><?= CHtml::link('Horgászverseny'.$contestBubble, ['/contest']); ?></li>
                <li><?= CHtml::link('Ranglisták', ['/leaderboard']); ?></li>
                <li><?= CHtml::link('Profil', ['/player']); ?></li>
                <li><?= CHtml::link('Súgó', ['/site/help']); ?></li>
            </ul>

            <?php
                if (Yii::app()->params['isPartOfWline']) {
                    echo CHtml::link('wline.hu', ['/gate/logout'], ['class'=>'ui-btn ui-mini', 'data-theme'=>'d']);
                } else {
                    echo CHtml::link('kilépés', ['/site/logout'], ['class'=>'ui-btn ui-mini', 'data-theme'=>'d']);
                }
            ?>
        </div><!-- /panel -->

    </div><!-- /page -->

</body>
</html>
