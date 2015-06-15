<li><a href="<?= $this->createUrl('/club/details', ['id'=>$data['clubID']]); ?>">
<h2><?= $data['clubName'] ?> klub</h2>
    <p><?= $data['moderator']; ?> elfogadta a felvételi kérelmedet. Üdv a klubban!</p>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</a></li>
