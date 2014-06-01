<li><a href="<?= $this->createUrl('/player/badges'); ?>">
<h2>Új 
<?php 
    switch ($data['level']) {
    case 3: $b = 'arany'; break;
    case 2: $b = 'ezüst'; break;
    default: $b = 'bronz'; break;
    }
    echo $b;
?>érmét szereztél!</h2>
    <p>Gratulálok a legújabb érmédhez! Ezt azért kaptad, mert teljesítetted a következő feladatot:<br/><?= $data['body']; ?></p>
    <p class="ui-li-aside"><strong><?= $created ?></strong></p>
</a></li>
