<?php
$this->pageTitle='Adatlap: ' . $player->user;
?>
<div class="nav">
    <div class="right">
        <div class="ui-mini" data-role="controlgroup" data-type="horizontal">
        <?php
        if ($player->itsMe()) {
            echo CHtml::link('e-mail', ['/setup/email'], ['class'=>'ui-btn']);
            echo CHtml::link('jelszó', ['/setup/password'], ['class'=>'ui-btn']);
        } else {
            $cssClass = '';
            if ($player->level < Yii::app()->params['duelLevelRequirement']) $cssClass .= 'ui-state-disabled';
            echo CHtml::link('párbajra hívom', ['duel/go', 'opponentId'=>$player->uid], ['class'=>'ui-btn ' . $cssClass]);
        }
        ?>
        </div>
    </div>
    <h1>játékos: <?= $player->user ?></h1>
</div>

<?php if ($player->status_points): ?>
<ul id="statuspoints" data-role="listview" data-inset="true">
    <li>
        <p><strong class="success">Fejlődtél!</strong> A státuszpontjaid felhasználásával megnövelheted az itt látható tulajdonságaidat vagy pénzedet.</p>
        <form action="<?= $this->createUrl(''); ?>" method="post">
            <div class="ui-mini" data-role="controlgroup" data-type="horizontal">
                <button name="increment_id" value="1">+1 max. energia</button>
                <button name="increment_id" value="2">+<?= $advancement->skillImprovement ?> szakértelem</button>
                <button name="increment_id" value="3">+2 teherbírás</button>
                <button name="increment_id" value="4">+<?= $advancement->dollarImprovement ?>$</button>
            </div>
        </form>
        <p class="sp">Státuszpontok: <span><?= $player->status_points ?></span></p>
    </li>
</ul>
<?php endif; ?>

<div class="responsive-a info ui-grid-a">
    <div class="ui-block-a">
        <table class="table-stripe strong">
            <tbody>
            <tr><td>szint</td><td><?= $player->level ?></td></tr>
            <?php if ($player->itsMe()): ?>
            <tr><td>max energia</td><td><?= $player->energy_max ?></td></tr>
            <?php else: ?>
            <tr><td>energia/max</td><td><?= $player->energy . '/' . $player->energy_max ?></td></tr>
            <?php endif; ?>
            <tr><td>pénz</td><td><?= $player->dollar ?>$</td></tr>
            <tr><td>teherbírás</td><td><?= $player->strength ?></td></tr>
            <tr><td>alap szakértelem</td><td><?= $player->skill ?></td></tr>
            <tr><td>teljes szakértelem</td><td><?= $player->skill_extended ?></td></tr>
            </tbody>
        </table>


        <h2>Részletek</h2>
        <table class="table-stripe">
            <tbody>
            <tr>
                <td>teljesített küldetések</td>
                <td><?= $playerStats->stats['completed_missions'] ?></td>
            </tr>
            <tr>
                <td>meglátogatott vizek</td>
                <td><?= $playerStats->stats['visited_waters'] ?></td>
            </tr>
            <tr>
                <td>meglátogatott megyék</td>
                <td><?= $playerStats->stats['visited_counties'] ?></td>
            </tr>
            <tr>
                <td>megnyert párbajok</td>
                <td><?= $playerStats->stats['duel_success'] ?></td>
            </tr>
            <tr>
                <td>elveszített párbajok</td>
                <td><?= $playerStats->stats['duel_fail'] ?></td>
            </tr>
            <tr>
                <td>párbajsikerek aránya</td>
                <td><?= $playerStats->stats['duel_rate'] ?>%</td>
            </tr>
            <tr>
                <td>felszerelések</td>
                <td><?= $player->owned_items ?></td>
            </tr>
            <tr>
                <td>csalik</td>
                <td><?= $player->owned_baits ?></td>
            </tr>
            <tr>
                <td>szettek</td>
                <td><?= $playerStats->stats['owned_setitems'] ?></td>
            </tr>
            <tr>
                <td>aktuális rang</td>
                <td><?= $playerStats->stats['rankActual']?'#'.$playerStats->stats['rankActual']:'-' ?></td>
            </tr>
            <tr>
                <td>összesített rang</td>
                <td><?= $playerStats->stats['rank']?'#'.$playerStats->stats['rank']:'-' ?></td>
            </tr>
            <tr>
                <td>klub</td>
                <td><?= $player->in_club ? CHtml::link($player->clubName, ['club/details', 'id'=>$player->in_club]) : '-'; ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="gear ui-block-b">

        <ul data-role="listview" data-inset="true">
            <li data-role="list-divider">3 legjobb felszerelés</li>
            <?php foreach ($playerStats->stats['items'] as $i): ?>
            <li><?= $i ?></li>
            <?php endforeach; ?>
            <li data-role="list-divider">3 legjobb csali</li>
            <?php foreach ($playerStats->stats['baits'] as $i): ?>
            <li><?= $i ?></li>
            <?php endforeach; ?>
            <li data-role="list-divider">3 legjobb szett</li>
            <?php foreach ($playerStats->stats['sets'] as $i): ?>
            <li><?= $i ?></li>
            <?php endforeach; ?>
        </ul>

    </div>
</div><!-- /grid-a -->

<?php if ($player->itsMe()) $this->renderPartial('_badges_own', ['badgeList'=>$badgeList]); ?>

<?php $this->widget('HelpWidget', ['topic'=>'profile']); ?>
