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
                $attr = ['id'=>'nav', 'data-icon'=>'bars', 'data-iconpos'=>'notext'];
                $clubsAttr = $clubsBubble = '';
                $contestAttr = $contestBubble = '';
                //check active contest
                if (Yii::app()->player->newContest) {
                    $attr['data-icon'] = 'alert';                
                    $attr['class'] = 'nav-alert';

                    $contestAttr = ' data-icon="alert"';
                    $contestBubble = '<span class="menu-club-challenge"> !!</span>';
                }

                //check clubchallenge
                if (Yii::app()->player->clubChallenge) {
                    $attr['data-icon'] = 'alert';                
                    $attr['class'] = 'nav-alert';

                    $clubsAttr = ' data-icon="alert"';
                    $clubsBubble = '<span class="menu-club-challenge"> - verseny</span>';
                }

                echo CHtml::link('Navigáció', ['#left-panel'], $attr); 
                if(Yii::app()->player->uid) {
                    $this->renderPartial('/layouts/attributes');
                } else {
                    $this->renderPartial('/layouts/empty');
                }
                ?>
        </div><!-- header -->

        <div data-role="content" class="game-content <?= $this->contentClass; ?>">
            <?php
                foreach(Yii::app()->user->getFlashes() as $key => $message) {
                    echo '<div class="alert alert-' . $key . '">' . $message . "</div>\n";
                }
                echo $content; 
            ?>
        </div><!-- content -->
    
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
    </div><!-- page -->

</body>
</html>
