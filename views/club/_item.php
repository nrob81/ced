<li>
    <h3><?= $item['name'] ?></h3>
    <p class="form"><?= CHtml::link('megnézem', ['club/details', 'id'=>$item['id']], ['data-role'=>'button', 'data-mini'=>'true']); ?></p>
    <p><?= $item['would_compete']?'versenyezne':'' ?></p>
    <p class="ui-li-aside muted">alapítva: <strong><?= date('Y. m. d.', strtotime($item['created'])); ?></strong></p>
</li>
