<ul class="missions" data-role="listview" data-inset="true">
    <li class="c" data-role="list-divider"><?= CHtml::link($user, ['/player/profile', 'uid'=>$p->uid]); ?></li>
    <?php if($isChallenge): ?><li><p><?= $p->club ?></p></li><?php endif; ?>
    <li>
        <?php if ($p->winner): ?><p class="success">győztes</p><?php else: ?><p class="error">vesztes</p><?php endif; ?>
        <p><?= $p->skill; ?> SzP<br/>
        <?= $p->chance; ?>% nyerési esély</p>

        <?php if (!$p->energy && !$p->winner): ?>
            <p class="error">Nincs energiája, ezért pénzt nem veszíthet.</p>
        <?php endif; ?>
    </li>


    <?php if ($p->awardXp || $p->awardDollar || $p->awardPoints): ?>
        <li>
            <h5>jutalmak</h5>
            <p>
            <?php 
                echo $p->awardXp ? $p->awardXp . 'tp<br/>' : '';
                if (!$isChallenge) {
                    echo $p->awardDollar ? $p->awardDollar . '$<br/>' : '';
                    echo $p->awardPoints ? $p->awardPoints . ' pont a ranglistán' : '';
                }
            ?>
            </p>
        </li>

        <?php if ($p->winner && $isChallenge): ?>
        <li>
            <h5>jutalmak a klubnak</h5>
            <p>
            +1 nyeremény<br/>
            <?php 
                echo $p->awardDollar ? $p->awardDollar . '$ zsákmány<br/>' : '';
                echo $p->awardPoints ? $p->awardPoints . ' pont' : '';
            ?>
            </p>
        </li>
        <?php endif; ?>
    <?php endif; ?>

    <li>
        <h5>követelmények</h5>
        <p>
        <?php echo $p->reqEnergy ? $p->reqEnergy . ' energia<br/>' : '' ?>
        <?php echo $p->reqDollar ? $p->reqDollar . '$' : '' ?>
        </p>
    </li>
</ul>
