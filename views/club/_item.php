<li>
    <h3><?= $item['name'] ?></h3>
    <p class="form"><?= CHtml::link('megnézem', ['club/details', 'id'=>$item['id']], ['class'=>'ui-btn ui-mini']); ?></p>
    <p><?= $item['would_compete']?'versenyezne':'' ?></p>
    <p class="ui-li-aside muted">alapítva: <strong><?= date('Y. m. d.', strtotime($item['created'])); ?></strong></p>
</li>
