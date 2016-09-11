<li>
    <h2><?= $data->title ?></h2>
    <p class="success">Hurrá, ez egész jól ment!</p>
    <div class="grid ui-grid-c">
        <div class="ui-block-a">
            <?php if ($data->req_energy): ?>
            <p>- <?= $data->req_energy ?> energia</p>
            <?php endif; ?>
            <?php if ($data->gate): ?>
            <p>100% rutin a megbízásokban</p>
            <?php endif; ?>

            <?php foreach ($data->req_baits as $req): ?>
                <p><?= $req['linkTitle']; ?></p>
            <?php endforeach; ?>
        </div>
        <div class="ui-block-b">
            <?php if ($data->action->gained_xp): ?>
            <p>+ <?= $data->action->gained_xp ?> tp</p>
            <?php endif; ?>
            <?php if ($data->action->gained_dollar): ?>
            <p>+ <?= $data->action->gained_dollar ?>$</p>
            <?php endif; ?>
            <?php if ($data->action->gained_visit): ?>
            <p>utazás: <?= $data->gate_name ?></p>
            <p>+<?= Yii::app()->params['goldPerGateMission'] ?> arany</p>
            <?php endif; ?>
            <?php if ($data->action->found_setpart): ?>
            <p>tárgy: <?= CHtml::link($data->action->found_setpart->title, ['/shop/makeSets']); ?></p>
            <?php endif; ?>
        </div>
        <div class="ui-block-c">
            <?php if (!$data->gate): ?>
            <p<?php if ($data['routine']>99) echo ' class="muted"'; ?>>rutin: <?= $data['routine'] < 100 ? $data['routine'] : 100; ?>%</p>
            <?php endif; ?>
        </div>
        <div class="ui-block-d"><p class="btn-cell">
        <form action="<?= $this->createUrl('missions/list', ['id'=>$data->water_id]); ?>" method="post">
        <div class="ui-mini" data-role="controlgroup" data-type="horizontal">
            <input type="hidden" name="mission_id" value="<?= $data->id; ?>">
            <a href="#popupInfo<?= $data->id ?>" data-rel="popup" class="ui-btn ui-icon-nodisc ui-icon-alt" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="e" data-iconpos="notext">Teljes szöveg</a>
            <input type="submit" value="<?= $data['chance']==100 ? 'megint' : 'új próba' ?>" data-inline="true">
        </div>
        </form>
        </p></div>
    </div><!-- /grid-c -->
</li>
