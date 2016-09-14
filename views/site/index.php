<?php
$this->contentClass='water';
$this->pageTitle=Yii::app()->name;
?>

<ul id="game-name" data-role="listview" data-inset="true" data-inline="true">
<li>Carp-e Diem :: Élj a halnak!</li>
</ul>

<?php if ($banner): ?>
<ul data-role="listview" data-inset="true" data-theme="d">
<li class="alert-info"><?= $banner; ?></li>
</ul>
<?php endif; ?>

<h2>Játéknapló</h2>
<ul class="gamelog" data-role="listview" data-inset="true" data-theme="d">
    <?php
    if (count($posts)) {
    foreach ($posts as $post) {
        $this->renderPartial('_wall_'.$post['content_type'], ['data'=>$post['body'], 'created'=>$post['created']]);
    }
    } else {
        if (Yii::app()->player->model->level < 3) {
            $this->renderPartial('_wall_date_separator', ['created'=>date('Y. m. d.', strtotime(Yii::app()->player->model->registered))]);
            $this->renderPartial('_wall_start');
        } else {
            $this->renderPartial('_wall_date_separator', ['created'=>date('Y. m. d.')]);
            $this->renderPartial('_wall_empty');
        }
    }
    ?>
</ul>

<?php $this->renderPartial('_story'); ?>

<p class="r">© 2013 nrcode<?= Yii::app()->params['isPartOfWline'] ? ' | ' . CHtml::link('fórum', ['/gate/forum']) : ''; ?> | <?= CHtml::link('a játék készítői', ['site/credits']); ?></p>
