<li>
<h2><?= $data['clubName'] ?> klub</h2>
    <?php if ($data['moderatorUid'] == Yii::app()->player->model->uid): ?>
    <p>Bezártad a klubot.</p>
    <?php else: ?>
    <p><?= $data['moderator']; ?> bezárta a klubot.</p>
    <?php endif; ?>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</li>
