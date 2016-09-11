<div class="nav">
    <?= CHtml::link('vissza', ['/contest'], ['class'=>'right ui-btn ui-mini']); ?>
    <h1>Horgászversenyek</h1>
</div>

<ul class="forum-list" data-role="listview" data-theme="d">
    <li data-role="list-divider">Legutóbbi horgászversenyek</li>
    <?php foreach ($contestList->history as $item): ?>
    <li><?= CHtml::link(date('Y. M. j. H:i', $item), ['contest/view', 'id'=>$item]); ?></li>
    <?php endforeach; ?>
</ul>
