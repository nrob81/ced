<h2>Jelentkezők</h2>
<?php $cssClass = $editable ? '' : 'ui-disabled'; ?>
<?php foreach ($club->entrants as $member): ?>
    <div class="ui-mini" data-role="controlgroup" data-type="horizontal">
        <?= CHtml::link('Felvétel', ['','approve'=>$member['uid']], ['class'=>'ui-btn ' . $cssClass, 'data-iconpos'=>'notext', 'data-icon'=>'check']); ?>
        <?= CHtml::link($member['user'], ['player/profile', 'uid'=>$member['uid']], ['class'=>'ui-btn', 'data-theme'=>'b']); ?>
        <?= CHtml::link('Kirúgás', ['', 'delete'=>$member['uid']], ['class'=>'ui-btn ' . $cssClass, 'data-iconpos'=>'notext', 'data-icon'=>'delete']); ?>
    </div>
<?php endforeach; ?>
