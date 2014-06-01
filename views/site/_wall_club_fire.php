<li><a href="<?= $this->createUrl('/club/details', ['id'=>$data['clubID']]); ?>">
<h2><?= $data['clubName'] ?> klub</h2>
    <?php if ($data['moderatorUid'] == Yii::app()->player->model->uid): ?>
    <p>Kiléptél a klubból.</p>
    <?php else: ?>
    <p><?= $data['moderator']; ?> visszavonta a klubtagságodat.</p>
    <?php endif; ?>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</a></li>
