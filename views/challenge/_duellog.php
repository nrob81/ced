<li>
    <h2><?= $duel['name_caller'] ?> - <?= $duel['name_opponent'] ?></h2>
    <p>
    győztes: <?= $duel['winner']=='caller' ? $duel['name_caller'] : $duel['name_opponent'] ?> (<?= $duel['awards']['club'] ?>)<br/> 
    jutalmak: <?= $duel['awards']['award_dollar'] ?>$ + <?= $duel['awards']['duel_points'] ?>pont.<br/>
    </p>
    <p class="ui-li-aside"><?= $duel['created'] ?></p>
</li>
