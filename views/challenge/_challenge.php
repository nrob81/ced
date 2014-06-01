<p>A verseny  
<?php 
$now = time(); 
if ($now < $challenge->startTime): 
?>
még nem indult el. Az indulásig hátralévő idő <strong><?= Time::secondsToDifference($challenge->startTime - $now); ?></strong>.
<?php elseif ($now >= $challenge->startTime and $now <= $challenge->endTime): ?>
folyamatban van. Hátralévő idő <strong><?= Time::secondsToDifference($challenge->endTime - $now); ?></strong>.
<?php else: ?>
lezárult.

    <?php if ($challenge->winner): ?>
        <?php if ($challenge->winner < 3): ?>
        A győztes csapat: <strong><?= $challenge->winner==1 ? $challenge->name_caller : $challenge->name_opponent; ?></strong>
        <?php else: ?>
        Az eredmény: döntetlen.
        <?php endif;?>
    <?php else: ?>
        A kiértékelés pillanatokon belül megtörténik.
    <?php endif;?>
<?php endif; ?>
</p>

<div class="duel-grid ui-grid-a">
    <div class="ui-block-a">
        <ul class="missions" data-role="listview" data-inset="true">
            <li class="c" data-role="list-divider"><?= CHtml::link($challenge->name_caller, ['/club/details', 'id'=>$challenge->caller]); ?></li>
            <li id="details-caller" class="r">
            <p><?= $challenge->point_caller ?> pont</p>
            <p><?= $challenge->cnt_won_caller ?>x győzött</p>
            <p><?= $challenge->loot_caller ?>$ zsákmány</p>
            </li>
        </ul>
    </div>

    <div class="ui-block-b">
        <ul class="missions" data-role="listview" data-inset="true">
            <li class="c" data-role="list-divider"><?= CHtml::link($challenge->name_opponent, ['/club/details', 'id'=>$challenge->opponent]); ?></li>
            <li id="details-opponent">
            <p><?= $challenge->point_opponent ?> pont</p>
            <p><?= $challenge->cnt_won_opponent ?>x győzött</p>
            <p><?= $challenge->loot_opponent ?>$ zsákmány</p>
            </li>
        </ul>
    </div>
    <div class="spr vs"></div>
</div>

<?php
if ($now >= $challenge->startTime and $now <= $challenge->endTime) {
    $ajax = CHtml::ajax(array(
        'url' => Yii::app()->createUrl('ajax/test', ['id'=>$challenge->id]),
        'dataType' => 'html', 
        'type' => 'get', 
        'success' => 'function(result) {
            var details = result.split("|");
            $("#details-caller").html(details[0]); 
            $("#details-opponent").html(details[1]);
        }'
        ) // ajax 
    ); // script

    Yii::app()->clientScript->registerScript('updateChallengeDetails', "
    timeout = 5 * 1000;
    function refresh() {        
        $ajax
    }
    window.setInterval('refresh()', timeout);", CClientScript::POS_END);
}
?>
