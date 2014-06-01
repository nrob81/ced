<?php
$this->pageTitle='Súgó';
?>

<div class="nav">
    <h1>Súgó témakörök</h1>
</div>

<p>Ha többet szeretnél tudni valamelyik témáról, csak kattints rá. Új súgószövegek <strong> szintlépéskor </strong> kerülnek be.</p>
<ul data-role="listview" data-inset="true" data-theme="d">
<?php foreach($news as $key => $item): ?>
    <li data-role="list-divider"><?= $item['title'] ?></li>
    <li><?= CHtml::link("<p>{$item['body']}</p>", ['/site/helpTopic', 't'=>$key]); ?></li>
<?php endforeach; ?>
</ul>
