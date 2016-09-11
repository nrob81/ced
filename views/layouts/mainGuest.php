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
            <?= CHtml::link('Navigáció', ['#left-panel'], ['id'=>'nav', 'class'=>'ui-btn ui-shadow ui-corner-all ui-icon-bars ui-btn-icon-notext']); ?>
            <div id="attributes" class="attributes">
                <h1>Carp-e Diem :: Élj a halnak!</h1>
            </div>
        </div><!-- header -->

        <div role="main" class="ui-content game-content <?= $this->contentClass; ?>">
            <?php
                foreach(Yii::app()->user->getFlashes() as $key => $message) {
                    echo '<div class="alert alert-' . $key . '">' . $message . "</div>\n";
                }
                echo $content;
            ?>
        </div><!-- content -->

        <div data-role="panel" id="left-panel">
            <ul data-role="listview">
                <li><?= CHtml::link('Főoldal', '/'); ?></li>
                <li><?= CHtml::link('Regisztráció', ['/account/signup']); ?></li>
                <li><?= CHtml::link('Elfelejtett jelszó', ['/account/resetPassword']); ?></li>
                <li><?= CHtml::link('Általános felhasználói feltételek', ['/public/terms']); ?></li>
                <li><?= CHtml::link('Adatvédelmi nyilatkozat', ['/public/privacy']); ?></li>
            </ul>
        </div><!-- /panel -->

    </div><!-- page -->

</body>
</html>
