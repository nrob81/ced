<div class="nav">
    <?= CHtml::link('versenyek', ['contest/history'], ['data-role'=>'button', 'data-mini'=>'true', 'data-inline'=>'true', 'class'=>'right']); ?>
    <h1>Horgászverseny</h1>
</div>

<ul data-role="listview" data-inset="true">
    <li data-role="list-divider">Versenyleírás</li>
    <li>
        <?php $this->renderPartial('_descr_' . $contestList->descriptionId); ?>
        <p>Az 1. helyezettnek járó díj: <strong> <?= $contestList->prize; ?>$ és egy aranyérme</strong>, amit a verseny után itt vehet át. Holtverseny esetén a győztesek osztoznak a díjon.</p>
        <p>A verseny kezdete: <strong> <?= date('Y. M. d. H:i', $contestList->id); ?></strong>, az időtartama pedig <strong> 2nap</strong>.</p>
    </li>
</ul>

<div class="grid-board ui-grid-a ui-responsive">
    <div class="ui-block-a">
        <ul data-role="listview" data-inset="true">                        
            <?php $this->renderPartial('_state', ['active'=>$contestList->isActive, 'secUntilEnd'=>$contestList->secUntilEnd]); ?> 
            <?php $this->renderPartial('_trophy', ['contestList'=>$contestList]); ?> 
        </ul>
    </div>
    <div class="block ui-block-b">

        <ul class="board" data-role="listview" data-inset="true">
            <li data-role="list-divider">A verseny állása</li>
            <?php if (count($contestList->list)): ?>
                <?php foreach($contestList->list as $rank => $item): ?>
                <li>
                <p><strong><?= $rank ?>. </strong> 
                <?php
                    if ($item['name']) {
                        echo CHtml::link($item['name'], ['/player/profile', 'uid'=>$item['id']]);
                    } else {
                        echo 'Törölt felhasználó';
                    }
                ?>
                </p>
                <?php if (Yii::app()->player->uid == $item['id']): ?>
                <p class="success"><?= $contestList->rankDescription ? $contestList->rankDescription : 'Szép eredmény!'; ?></p>
                <?php endif; ?>
                <p class="ui-li-aside muted"><strong><?= $item['score'] ?> pont</strong></p>
                </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><p>Még senki sem gyűjtött pontot. Te lehetsz az első!</p></li>
            <?php endif; ?>
        </ul>

    </div>
</div>
