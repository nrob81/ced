<?php
$this->pageTitle = 'Ranglista';
?>

<div class="nav">
    <h1>Ranglista</h1>
</div>

<div class="grid-board ui-grid-a ui-responsive">
    <div class="block ui-block-a">
        <?php $this->renderPartial('_list', ['board'=>$board]); ?>
    </div>
    <div class="block ui-block-b">

    <div data-role="collapsible-set" data-content-theme="d" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d" class="board-menu">
        <div data-role="collapsible" data-inset="false"<?= $board->boardType == 'board_p'?' data-collapsed="false"':'';?>>
            <h2>Játékosok</h2>
            <ul data-role="listview">
                <li><?= CHtml::link('aktuális hónap', ['index'], ['class'=>$board->boardType == 'board_p' & $board->range == 'actual'?'ui-disabled':'']); ?></li>
                <li><?= CHtml::link('előző hónap', ['index', 'range'=>'prev'], ['class'=>$board->boardType == 'board_p' & $board->range == 'prev'?'ui-disabled':'']); ?></li>
                <li><?= CHtml::link('összesített: utolsó 6 hónap', ['index', 'range'=>'last'], ['class'=>$board->boardType == 'board_p' & $board->range == 'last'?'ui-disabled':'']); ?></li>
            </ul>
        </div><!-- /collapsible -->
        <div data-role="collapsible" data-inset="false"<?= $board->boardType == 'board_c'?' data-collapsed="false"':'';?>>
            <h2>Klubok</h2>
            <ul data-role="listview">
                <li><?= CHtml::link('aktuális hónap', ['club'], ['class'=>$board->boardType == 'board_c' & $board->range == 'actual'?'ui-disabled':'']); ?></li>
                <li><?= CHtml::link('előző hónap', ['club', 'range'=>'prev'], ['class'=>$board->boardType == 'board_c' & $board->range == 'prev'?'ui-disabled':'']); ?></li>
                <li><?= CHtml::link('összesített: utolsó 6 hónap', ['club', 'range'=>'last'], ['class'=>$board->boardType == 'board_c' & $board->range == 'last'?'ui-disabled':'']); ?></li>
            </ul>
        </div><!-- /collapsible -->
    </div> 

    </div>
</div><!-- /grid-a -->


