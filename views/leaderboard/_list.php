<ul class="board" data-role="listview" data-inset="true">
    <li data-role="list-divider"><?= $board->title; ?></li>
    <?php foreach($board->items as $rank => $item): ?>
    <li>
    <p><strong><?= $rank ?>. </strong> 
    <?php
    if ($board->boardType==Leaderboard::TYPE_CLUB) {
        if ($item['name']) {
            echo CHtml::link($item['name'], ['club/details', 'id'=>$item['id']]);
        } else {
            echo 'Törölt klub';
        }
    } else {
        if ($item['name']) {
            echo CHtml::link($item['name'], ['player/profile', 'uid'=>$item['id']]);
        } else {
            echo 'Törölt felhasználó';
        }
    }
    ?>
    </p>
    <?php if ($board->uid == $item['id'] || $board->inClub == $item['id']): ?>
    <p class="success"><?= $board->rankDescription; ?></p>
    <?php endif; ?>
    <p class="ui-li-aside muted"><strong><?= $item['score'] ?> pont</strong></p>
    </li>
    <?php endforeach; ?>
</ul>
