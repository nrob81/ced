<?php
$this->pageTitle = 'Saját klubom';
$this->pageTitle='Saját klubom: ' . $club->name;
?>

<div class="nav">
    <div class="right">
        <div data-role="controlgroup" data-type="horizontal" data-mini="true">
            <?= CHtml::link('klubok', ['club/list'], ['data-role'=>'button']); ?>
            <?= CHtml::link('bezárás', ['club/close'], ['data-role'=>'button']); ?>
        </div>
    </div>
    <h1><?= $club->name; ?></h1>
</div>

<?php $this->renderPartial('_details', ['club'=>$club, 'challenge'=>$challenge, 'editable'=>true]); ?>
<?php if (count($club->entrants)) $this->renderPartial('_members', ['club'=>$club, 'moderation'=>$moderation, 'editable'=>true]); ?>
<?php if ($challenge->active) $this->renderPartial('_challenge', ['club'=>$club, 'challenge'=>$challenge]); ?>

<?php 
$this->renderPartial('_forum_list', [
    'list'=>$list,
    'page'=>$page,
    'club'=>$club
    ]);
?>

<?php $this->widget('HelpWidget', ['topic'=>'club']); ?>
