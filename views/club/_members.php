<h2>Jelentkezők</h2>
<?php $cssClass = $editable ? '' : 'ui-disabled'; ?>
<?php foreach ($club->entrants as $member): ?>
    <div data-role="controlgroup" data-type="horizontal" data-mini="true">
        <?= CHtml::link('Felvétel', ['','approve'=>$member['uid']], ['data-role'=>'button', 'data-iconpos'=>'notext', 'data-icon'=>'check', 'class'=>$cssClass]); ?>
        <?= CHtml::link($member['user'], ['player/profile', 'uid'=>$member['uid']], ['data-role'=>'button', 'data-theme'=>'b']); ?>
        <?= CHtml::link('Kirúgás', ['', 'delete'=>$member['uid']], ['data-role'=>'button', 'data-iconpos'=>'notext', 'data-icon'=>'delete', 'class'=>$cssClass]); ?>
    </div>
<?php endforeach; ?>
