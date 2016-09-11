<?php
$this->pageTitle = 'Készítők';
?>

<div class="nav">
    <?= CHtml::link('vissza', '/', ['class'=>'right ui-btn ui-mini']); ?>
    <h1>Készítők</h1>
</div>

<div class="credits-grid ui-grid-a">
    <div class="ui-block-a"><?= CHtml::image('/images/me.png', 'A fejlesztő'); ?></div>
    <div class="ui-block-b">A játék készítője:<br/>nrcode s.r.o.<br/>Natkay Róbert</div>
</div>

<ul data-role="listview" data-inset="true">
    <li data-role="list-divider">Akik nélkül a játék nem létezne</li>
    <li><p>Az nrcode köszönettel tartozik az itt felsorolt embereknek önzetlen segítségükért. Köszönjük az ötleteket, grafikai segítséget, történetírást, tesztelést és mindent, amiben közreműködtetek!</p></li>
    <li>A segítők listája abc sorrendben:<br/>
    20090928, Cathy927, Cipike1, CJBOY21, dimmuni, GodHand, Harpia, infinitedreams, PolarGravity, Roomdog, Slityak, Tina25, Zealot</li>
</ul>
