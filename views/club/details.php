<?php
$this->pageTitle = 'Klub részletek';
$this->pageTitle='Klub: ' . $club->name;
?>
<div class="nav">
    <div class="right">
        <div data-role="controlgroup" data-type="horizontal" data-mini="true">
            <?= CHtml::link('klubok', ['club/list'], ['class'=>'ui-btn']); ?>
            <?php
            if (Yii::app()->player->model->in_club) {
                echo CHtml::link('versenyre hívom', ['', 'id'=>$club->id, 'call'=>1], ['class'=>'ui-btn']);
            } else {
                if ($club->joinRequestSent == $clubID) {
                    echo CHtml::link('csatl. visszavonása', ['', 'id'=>$clubID, 'deleteJoin'=>1], ['class'=>'ui-btn']);
                } else {
                    echo CHtml::link('csatlakozás', ['', 'id'=>$clubID, 'join'=>1], ['class'=>'ui-btn']);
                }
            }
            ?>
        </div>
    </div>
    <h1><?= $club->name; ?></h1>
</div>

<?php $this->renderPartial('_details', ['club'=>$club, 'challenge'=>$challenge, 'editable'=>false]); ?>
<?php if (count($club->entrants)) $this->renderPartial('_members', ['club'=>$club, 'moderation'=>$moderation, 'editable'=>false]); ?>
<?php if ($challenge->active) $this->renderPartial('_challenge', ['club'=>$club, 'challenge'=>$challenge]); ?>

<?php
$this->renderPartial('_forum_list', [
    'list'=>$list,
    'page'=>$page,
    'club'=>$club
    ]);
?>

<?php $this->widget('HelpWidget', ['topic'=>'club']); ?>
