<li<?= $item['private'] ? ' class="private"' : ''; ?>>
    <h2><?php 
    if ($item['uid'] > 1) { 
            echo CHtml::link($item['user'], ['player/profile', 'uid'=>$item['uid']], ['data-ajax'=>'false']); 
    } else { 
        echo $item['user']; 
    } 
    ?></h2>
    <p><?= $item['body']; ?></p>
    <p class="ui-li-aside muted">
        <abbr class="timeago" title="<?= date(DATE_ISO8601, strtotime($item['created'])); ?>"><?= $item['created']; ?></abbr>
    <?php 
    if (Yii::app()->player->model->in_club==$item['club_id'] && Yii::app()->player->uid==$item['uid']) {
        echo CHtml::link('törlés', ['club/forum', 'id'=>$item['club_id'], 'delete'=>$item['id'], 'page'=>$page], ['data-ajax'=>'false']);
    } 
    ?>
    </p>    
</li>
