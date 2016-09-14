<div class="nav">
    <?= CHtml::link('összes érme', ['player/badges'], ['data-role'=>'button', 'data-inline'=>'true', 'data-mini'=>'true', 'class'=>'right']); ?>
    <h3>Legújabb érméim</h3>
</div>
<div class="prog-bar">
    <div class="ui-slider-track ui-btn-down-e ui-btn-corner-all">
        <div class="ui-slider-bg ui-btn-active ui-btn-corner-all" style="width: <?= $badgeList->percentOwned; ?>%;"></div>
        <p class="desc">megszerzett érmék: <?= $badgeList->percentOwned; ?>%</p>
    </div>
</div>

<div class="badges badges-last">
<?php
$i = 1;
foreach ($badgeList->owned as $k => $v): ?>
    <p><span class="badge badge-<?= $v['level']; ?>"></span><?= isset($v['body']) ? $v['body'] : '...'; ?></p>
<?php 
    if ($i >= 5) break;
    $i++;
endforeach; 
?>
</div>
