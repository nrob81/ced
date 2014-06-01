<li><a href="<?= $this->createUrl('/missions'); ?>">
<h2>Nagyobb jutalmak</h2>
    <p>Megnöveltük az arany és gyémánt helyszínrutinokért járó jutalmakat. Mivel már elértél ilyen szinteket, visszamenőleg megkaptad az ezekért járó plusz aranyat.</p>
    <p><?= $data['r_gold']; ?> helyszínen szereztél arany helyszínrutint <?php if ($data['r_diamant']) echo 'és '.$data['r_diamant'].' helyszínen gyémántot.';  ?></p>
    <p class="success">Összesen <strong> <?= $data['award']; ?> </strong> aranyat kaptál.</p>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</a></li>
