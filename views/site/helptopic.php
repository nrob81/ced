<?php
$this->pageTitle='Súgó: '.$title;
?>

<div class="nav">
    <?= CHtml::link('vissza', ['site/help'], ['class'=>'right ui-btn ui-mini']); ?>
    <h1>Súgó</h1>
</div>

<ul data-role="listview" data-inset="true">
<li data-role="list-divider">témakör: <?= $title ?></li>
<?php foreach($items as $item): ?>
    <li>
        <p><?= $item; ?></p>
    </li>
<?php endforeach; ?>
</ul>
