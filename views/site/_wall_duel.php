<li><a href="<?= $this->createUrl('/duel/replay', ['id'=>$data['duel_id']]); ?>">
<h2><?= $data['caller_user'] ?> párbajra hívott</h2>
    <p>A párbajhoz <?= $data['req_energy'] ?> energiát használtál fel.</p>
    <?php if ($data['winner']): ?>
        <p class="success">Jobb voltál az ellenfelednél, így a következő dolgokat megnyerted: 
        <strong><?= (int)$data['award_xp'] ?>tp + <?= (int)$data['award_dollar'] ?>$</strong></p>
    <?php else: ?>
        <p class="error">Az ellenfeled legyőzött, ennyit veszítettél: 
        <strong><?= (int)$data['req_dollar'] ?>$</strong></p>
    <?php endif; ?>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</a></li>
