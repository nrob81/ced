<?php $this->beginContent('//layouts/main'); ?>
<div role="main" class="ui-content game-content <?= $this->contentClass; ?>">
    <div id="content">
    <?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="alert alert-' . $key . '">' . $message . "</div>\n";
    }
    echo $content;
    ?>
    </div><!-- content -->
</div><!-- content -->
<?php $this->endContent(); ?>
