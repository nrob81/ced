<?php
$this->pageTitle='Áron bá: ' . 'Szett elemek';
$this->renderPartial('/shop/nav', ['title'=>'Szett elemek', 'reel'=>'true']);
?>

<ul class="controls-in-right" data-role="listview" data-inset="true">
<?php 
if (count($list)) {
    foreach($list as $item) {
        Yii::app()->controller->renderPartial('_itemset', ['item' => $item]);
    }
} else {
    echo '<li>Még nem találtál egy szett elemet sem.</li>';
}
?>
</ul>

<p class="spr help"><strong>Áron bá:</strong><br/>
Szett tartozékokat a megbízások teljesítése közben találhatsz.</p>
