<?php
$this->pageTitle = 'Versenytörténet';
?>

<div class="nav">
    <?= CHtml::link('vissza', ['club/details', 'id'=>$club->id], ['class'=>'right ui-btn ui-mini']); ?>
    <h1><?= $club->name ?> legutóbbi versenyei</h1>
</div>

<ul class="forum-list" data-role="listview" data-theme="d">
    <li data-role="list-divider">A klub utóbbi versenyei</li>
    <?php foreach ($club->challenges as $ch): ?>
        <li><?= CHtml::link($ch['name_caller'] . ' - ' . $ch['name_opponent'], ['/challenge/details', 'id'=>$ch['id']]); ?></li>
    <?php endforeach; ?>
</ul>
