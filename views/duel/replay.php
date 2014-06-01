<?php
$this->pageTitle='Pecapárbaj: ismétlés';

$this->renderPartial('/duel/nav', [
    'title'=>'A párbaj eredménye', 
    'replay'=>[
        'uid'=>$duel->caller->uid == Yii::app()->player->uid ? $duel->opponent->uid : $duel->caller->uid,
        'title'=>$duel->caller->uid == Yii::app()->player->uid ? 'újra' : 'visszavágó',
        ]
        ]);
?>

<div class="duel-grid ui-grid-a">
    <div class="ui-block-a">
        <?php $this->renderPartial('_parameters', [
            'user'=>$duel->caller->user, 
            'p'=>$duel->competitors[0], 
            'isChallenge'=>$duel->isChallenge,
            ]
        ); ?> 
    </div>
    <div class="ui-block-b">
        <?php $this->renderPartial('_parameters', [
            'user'=>$duel->opponent->user, 
            'p'=>$duel->competitors[1], 
            'isChallenge'=>$duel->isChallenge,
            ]
        ); ?> 
    </div>
</div><!-- /grid-a -->
