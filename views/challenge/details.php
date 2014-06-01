<?php
$this->pageTitle = 'Versenyrészletek';
?>

<h1>Versenyrészletek</h1>
<?php $this->renderPartial('_challenge', ['challenge'=>$challenge]); ?>

<ul class="forum-list" data-role="listview">
    <li data-role="list-divider">Párbajok eredménye</li>
    <?php
    foreach ($challenge->listDuels as $duel) {
        $this->renderPartial('_duellog', ['duel'=>$duel]);
    } 
    ?>
</ul>
