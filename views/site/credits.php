<?php
$this->pageTitle = 'Készítők';
?>

<div class="nav">
    <?= CHtml::link('vissza', '/', ['data-role'=>'button', 'data-inline'=>'true', 'data-mini'=>'true', 'class'=>'right']); ?>
    <h1>Készítők</h1>
</div>

<p>A játék készítője: nrcode s.r.o.</p>

<ul data-role="listview" data-inset="true">
    <li data-role="list-divider">Akik nélkül a játék nem létezne</li>
    <li><p>Az nrcode köszönettel tartozik az itt felsorolt embereknek önzetlen segítségükért. Köszönjük az ötleteket, grafikai segítséget, történetírást, tesztelést és mindent, amiben közreműködtetek!</p></li>
    <li>A segítők listája abc sorrendben:<br/>
    20090928, Cathy927, Cipike1, CJBOY21, dimmuni, GodHand, Harpia, infinitedreams, PolarGravity, Roomdog, Slityak, Tina25, Zealot</li>
</ul>
