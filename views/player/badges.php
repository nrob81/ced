<?php
$this->pageTitle='Összes érme';
?>

<div class="nav">
    <?= CHtml::link('profilom', ['/player'], ['class'=>'right ui-btn', 'data-inline'=>'true', 'data-mini'=>'true']); ?>
    <h1>Összes érme</h1>
</div>

<div class="coll-badges" data-role="collapsible-set" data-theme="c" data-content-theme="d">
    <div class="badges" data-role="collapsible" data-collapsed="false">
        <h3>Bronz</h3>
        <?php foreach ($badgeList->all['b'] as $k => $v): ?>
            <p>
            <span class="badge badge-<?= $v['level']; ?><?= $v['owned']?'':'x'; ?>"></span>
            <?= isset($v['body']) ? $v['body'] : '...'; ?>
            </p>
        <?php endforeach; ?>
    </div>
    <div class="badges" data-role="collapsible">
        <h3>Ezüst</h3>
        <?php foreach ($badgeList->all['s'] as $k => $v): ?>
            <p>
            <span class="badge badge-<?= $v['level']; ?><?= $v['owned']?'':'x'; ?>"></span>
            <?= isset($v['body']) ? $v['body'] : '...'; ?>
            </p>
        <?php endforeach; ?>
    </div>
    <div class="badges" data-role="collapsible">
        <h3>Arany</h3>
        <?php foreach ($badgeList->all['g'] as $k => $v): ?>
            <p>
            <span class="badge badge-<?= $v['level']; ?><?= $v['owned']?'':'x'; ?>"></span>
            <?= isset($v['body']) ? $v['body'] : '...'; ?>
            </p>
        <?php endforeach; ?>
    </div>
</div>
