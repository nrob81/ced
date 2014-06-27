<?php
$this->pageTitle='Megbízások: ' . $name['county'] . ' megye';
?>

<?php 
Yii::app()->controller->renderPartial('_navigation', ['name'=>$name, 'nav'=>$nav, 'widget' => $this]);
?>

<?php 
if ($tutorialToShow) $this->renderPartial('_tutorial', ['id'=>$tutorialToShow]);
?>

<?php if ($completedId): ?>
<ul class="missions missions-active" data-role="listview" data-inset="true">
    <?php
    $tpl = $missions[$completedId]->action->success ? '_actual' : '_mission';
    Yii::app()->controller->renderPartial($tpl, ['data'=>$missions[$completedId], 'widget' => $this, 'notify'=>true]);
    ?>
</ul>
<?php endif; ?>

<ul class="missions" data-role="listview" data-inset="true">
    <li data-role="list-divider"><p class="spr mission">Megbízások</p></li>
        <?php
        foreach($missionTypeList['simple'] as $id) {
            if ($id == $completedId) continue;
            Yii::app()->controller->renderPartial('_mission', ['data' => $missions[$id], 'widget' => $this, 'notify'=>false]);
        }
        ?>
    <li data-role="list-divider"><p class="spr mission">Fő megbízás</p></li>
        <?php
        foreach($missionTypeList['gate'] as $id) {
            if ($id == $completedId) continue;
            Yii::app()->controller->renderPartial('_mission', ['data' => $missions[$id], 'widget' => $this, 'notify'=>false]);
        }
        ?>
    <li class="routine">
        <p>Ezen a helyszínen megszerzett rutinod:</p>
        <p class="stars"><?= $location->getRoutineImages($routine); ?></p>
    </li>
</ul>

<?php $this->widget('HelpWidget', ['topic'=>'mission']); ?>

<?php
    foreach($missionTypeList['simple'] as $id) {
        Yii::app()->controller->renderPartial('_popup', ['data' => $missions[$id], 'widget' => $this]);
    }
    foreach($missionTypeList['gate'] as $id) {
        Yii::app()->controller->renderPartial('_popup', ['data' => $missions[$id], 'widget' => $this]);
    }
?>
