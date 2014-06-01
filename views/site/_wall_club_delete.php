<li><a href="<?= $this->createUrl('/club/details', ['id'=>$data['clubID']]); ?>">
<h2><?= $data['clubName'] ?> klub</h2>
    <p><?= $data['moderator']; ?> visszautasította a felvételi kérelmedet.</p>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</a></li>
