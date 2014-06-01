<?php if ($contestList->isActive and count($contestList->leaders)): ?>
<li>
    <img src="/images/trophy2.png" alt="Trófea" />
    <h1><?php echo count($contestList->leaders)>1 ? 'A legesélyesebb játékosok' : 'A legesélyesebb játékos'; ?></h1>
    
    <p>
    <?php
    foreach ($contestList->leaders as $uid => $player) {
            echo CHtml::link($player['name'], ['/player/profile', 'uid'=>$uid]) . ': <strong> '. $player['score'] .' </strong> pont <br/>';
    }
    ?>
    </p>
</li>
<?php endif; ?>

<?php if (!$contestList->isActive and count($contestList->winners)): ?>
<li>
    <img src="/images/trophy.png" alt="Trófea" />
    <h1><?php echo count($contestList->leaders)>1 ? 'A verseny győztesei' : 'A verseny győztese'; ?></h1>
    
    <p>
    <?php 
        foreach ($contestList->winners as $uid => $player) {
            echo CHtml::link($player['name'], ['/player/profile', 'uid'=>$uid]) . ': <strong> '. $player['score'] .' </strong> pont, 
                nyeremény: <strong>'. $contestList->prizePerWinner .'$</strong> <br/>';
        }

    //claim button
    if ($contestList->canClaimPrize()) {
        echo CHtml::link('nyeremény felvétele', ['/contest/view', 'id'=>$contestList->id, 'getPrize'=>1], ['data-role'=>'button']);
    }
    ?>
    </p>
</li>
<?php endif;?>