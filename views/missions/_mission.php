<li>
    <h2><?= $data->title ?></h2>

    <?php if (isset($error) && $error) echo CHtml::tag('p', ['class'=>'error'], $error); ?>
    <div class="grid ui-grid-c">
        <div class="ui-block-a"><h3>követelmény</h3>
            <p<?php if ($notify && !$data->action->reqPassed['energy']): ?> class="error"<?php endif; ?>><?= $data->req_energy ?> energia</p>
            <?php if ($data->gate): ?>
            <p<?php if ($notify && !$data->action->reqPassed['routinesFull']): ?> class="error"<?php endif; ?>>100% rutin a megbízásokban</p>
            <?php endif; ?>

            <?php foreach ($data->req_baits as $req): ?>
                <p><?php
                if ($req['haveEnought']) {
                    echo $req['linkTitle'];
                } else {
                    $properties = ['data-ajax'=>'false'];
                    if ($notify) $properties['class'] = 'error';
                    echo CHtml::link($req['linkTitle'], '/shop/buyBaits', $properties);
                }
                ?></p>
            <?php endforeach; ?>
        </div>
        <div class="ui-block-b"><h3>jutalom</h3>
            <p><?= $data['award_xp'] ?> tp</p>
            <p><?= $data['award_dollar'] ?></p>
            <?php if (!$data->gate_visited && $data->gate): ?>
            <p>utazás ide: <?= $data->gate_name ?></p>
            <p>10 arany</p>
            <?php endif; ?>
            <?php if ($data->award_setpart): ?>
            <p>szett elemet találhatsz</p>
            <?php endif; ?>
        </div>
        <div class="ui-block-c"><h3>megbízás</h3>
            <?php if (!$data->gate): ?>
            <p<?php if ($data['routine']>99) echo ' class="muted"'; ?>>rutin: <?= $data['routine'] < 100 ? $data['routine'] : 100; ?>%</p>
            <?php endif; ?>

            <?php if ($data['chance'] < 100): ?>
            <p>esély: <?= $data['chance'] ?>%</p>
            <?php endif; ?>
        </div>
        <div class="ui-block-d"><p class="btn-cell">
            <form action="<?= $this->createUrl('missions/list', ['id'=>$data['water_id']]); ?>" method="post">
            <div data-role="controlgroup" data-type="horizontal" data-mini="true">
                <input type="hidden" name="mission_id" value="<?= $data['id']; ?>">

                <a href="#popupInfo<?= $data->id ?>" data-rel="popup" data-role="button" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="e" data-iconpos="notext" data-mini="true" class="ui-icon-nodisc ui-icon-alt">Teljes szöveg</a>
                <input type="submit" value="<?= $data['chance']==100 ? 'mehet' : 'próba' ?>" data-mini="true" data-inline="true"<?= $data['routine'] >= 100 ? ' disabled=""' : '' ?>>
            </div>
            </form>
        </p></div>
    </div><!-- /grid-c -->
</li>
