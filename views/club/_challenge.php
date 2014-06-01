<h3>Verseny <span> 
<?php 
$now = time(); 
$secToDiff = 0;

if ($now < $challenge->startTime):
    $secToDiff = $challenge->startTime - $now;
?>
..még nem indult el. Az indulásig hátralévő idő <strong id="challStartTime"><?= Time::secondsToDifference($secToDiff); ?></strong>. 
<?php elseif ($now >= $challenge->startTime and $now <= $challenge->endTime): 
    $secToDiff = $challenge->endTime - $now;
?>
..folyamatban van. Hátralévő idő <strong id="challStartTime"><?= Time::secondsToDifference($secToDiff); ?></strong>.
<?php else: ?>
..lezárult.
<?php endif; ?>
<?php
if ($secToDiff > 0) {
    Yii::app()->clientScript->registerScript('cntr', "
        $('#challStartTime').countdown({
            until: {$secToDiff},
            layout: '{mnn}{sep}{snn}',
            expiryText: '00:00',
        })");
}
?>
 <?= CHtml::link('Részletek', ['challenge/details', 'id'=>$challenge->id]); ?>
</span></h3>

<!--p>[Az a klub győz, aki több <strong> pontot </strong> szerez a verseny alatt.<br>
A győztes klub zsákmányát egyenlően szétosztjuk a tagjai közt a verseny lezárása után. 
A vesztes klub által zsákmányolt pénz a horgásszövetségé lesz.]</p-->
<div class="duel-grid ui-grid-a">
    <div class="ui-block-a">
        <ul class="missions" data-role="listview" data-inset="true">
            <li class="c" data-role="list-divider"><?= $challenge->caller==$club->id ? $challenge->name_caller : CHtml::link($challenge->name_caller, ['/club/details', 'id'=>$challenge->caller]); ?></li>
            <li id="details-caller" class="r">
            <p><?= $challenge->point_caller ?> pont</p>
            <p><?= $challenge->cnt_won_caller ?>x győzött</p>
            <p><?= $challenge->loot_caller ?>$ zsákmány</p>
            </li>
        </ul>
    </div>

    <div class="ui-block-b">
        <ul class="missions" data-role="listview" data-inset="true">
            <li class="c" data-role="list-divider"><?= $challenge->opponent==$club->id ? $challenge->name_opponent : CHtml::link($challenge->name_opponent, ['/club/details', 'id'=>$challenge->opponent]); ?></li>
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
