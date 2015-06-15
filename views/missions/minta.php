<?php
$this->pageTitle='Megbízások';
?>

<ul class="missions" data-role="listview" data-inset="true">
    <li>
        <h2>Busás lakoma</h2>
        <p class="success">Hurrá, ez egész jól ment!</p> 
        <div class="grid ui-grid-c">
            <div class="ui-block-a"><p>- 3 energia</p></div>
            <div class="ui-block-b"><p>+ 3 tp</p><p>+ 112$</p></div>
            <div class="ui-block-c"><p>teljesítve: 10%</p></div>
            <div class="ui-block-d"><p class="btn-cell"><a href="#" data-role="button" data-mini="true" data-inline="true" data-theme="e">elvégzem</a></p></div>
        </div><!-- /grid-b -->
    </li>
</ul>
 

<div class="mission-nav">
    <a href="#popupMenu" data-rel="popup" data-role="button" data-inline="true" data-mini="true">Utazás</a>
    <div data-role="popup" id="popupMenu" data-theme="d">
        <ul data-role="listview" data-inset="true" style="min-width:210px;">
            <li data-role="divider" data-theme="e">Merre szeretnél utazni?</li>
            <li data-icon="arrow-l"><a href="#">Lanka-főcsatorna</a></li>
            <li class="ui-disabled"><a href="#">Mrtvica-tó</a></li>
        </ul>
    </div>
    <h1>Baranya megye, Dráva</h1>
</div>

<ul class="missions" data-role="listview" data-inset="true">
    <li data-role="list-divider">Megbízások</li>
    <li>
        <h2>Busás lakoma <a href="#popupInfo1" data-rel="popup" data-role="button" data-inline="true" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="b" data-iconpos="notext" data-mini="true" class="ui-icon-nodisc">Teljes szöveg</a></h2>
        <div class="grid ui-grid-c">
            <div class="ui-block-a"><h3>követelmény</h3><p>3 energia</p></div>
            <div class="ui-block-b"><h3>jutalom</h3><p>3 tp</p><p>100$ - 150$</p></div>
            <div class="ui-block-c"><h3>megbízás</h3><p>teljesítve: 10%</p></div>
            <div class="ui-block-d"><p class="btn-cell"><a href="#" data-role="button" data-mini="true" data-inline="true" data-theme="e">elvégzem</a></p></div>
        </div><!-- /grid-b -->
    </li>
    <li>
        <h2>Jól láttam? <a href="#popupInfo2" data-rel="popup" data-role="button" data-inline="true" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="b" data-iconpos="notext" data-mini="true" class="ui-icon-nodisc">Teljes szöveg</a></h2>
        <div class="grid ui-grid-c">
            <div class="ui-block-a"><h3>követelmény</h3><p>4 energia</p></div>
            <div class="ui-block-b"><h3>jutalom</h3><p>4 tp</p><p>110$ - 180$</p></div>
            <div class="ui-block-c"><h3>megbízás</h3><p>teljesítve: 0%</p></div>
            <div class="ui-block-d"><p class="btn-cell"><a href="#" data-role="button" data-mini="true" data-inline="true" data-theme="e">elvégzem</a></p></div>
        </div><!-- /grid-b -->
    </li>
    <li>
        <h2>Felszökő árak <a href="#popupInfo3" data-rel="popup" data-role="button" data-inline="true" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="b" data-iconpos="notext" data-mini="true" class="ui-icon-nodisc">Teljes szöveg</a></h2>
        <div class="grid ui-grid-c">
            <div class="ui-block-a"><h3>követelmény</h3><p>5 energia</p><p>tejeskukorica: 1/1</p></div>
            <div class="ui-block-b"><h3>jutalom</h3><p>4 tp</p><p>150$ - 200$</p></div>
            <div class="ui-block-c"><h3>megbízás</h3><p>teljesítve: 0%</p><p>siker: 25%</p></div>
            <div class="ui-block-d"><p class="btn-cell"><a href="#" data-role="button" data-mini="true" data-inline="true" data-theme="e">elvégzem</a></p></div>
        </div><!-- /grid-b -->
    </li>
    <li>
        <h2>Az angol angolna <a href="#popupInfo4" data-rel="popup" data-role="button" data-inline="true" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="b" data-iconpos="notext" data-mini="true" class="ui-icon-nodisc">Teljes szöveg</a></h2>
        <div class="grid ui-grid-c">
            <div class="ui-block-a"><h3>követelmény</h3><p>5 energia</p><p><a href="#">csalihal: 0/1</a></p></div>
            <div class="ui-block-b"><h3>jutalom</h3><p>5 tp</p><p>250$ - 300$</p></div>
            <div class="ui-block-c"><h3>megbízás</h3><p>teljesítve: 0%</p><p>siker: 10%</p></div>
            <div class="ui-block-d"><p class="btn-cell"><a href="#" class="ui-disabled" data-role="button" data-mini="true" data-inline="true" data-theme="e">elvégzem</a></p></div>
        </div><!-- /grid-b -->
    </li>
    <li data-role="list-divider">Fő megbízás</li>
    <li>
        <h2>Búcsú a márnáktól <a href="#popupInfo4" data-rel="popup" data-role="button" data-inline="true" data-transition="pop" data-icon="info" data-iconshadow="false" data-theme="b" data-iconpos="notext" data-mini="true" class="ui-icon-nodisc">Teljes szöveg</a></h2>
        <div class="grid ui-grid-c">
            <div class="ui-block-a"><h3>követelmény</h3><p>10 energia</p></div>
            <div class="ui-block-b"><h3>jutalom</h3><p>15 tp</p><p>500$ - 770$</p><p>utazás ide: Mrtvica-tó</p></div>
            <div class="ui-block-c"><h3>megbízás</h3><p>teljesítve: 0%</p><p>siker: 2%</p></div>
            <div class="ui-block-d"><p class="btn-cell"><a href="#" class="ui-disabled" data-role="button" data-mini="true" data-inline="true" data-theme="e">elvégzem</a></p></div>
        </div><!-- /grid-b -->
    </li>
</ul>

<div data-role="popup" id="popupInfo1" class="ui-content" data-theme="e" style="max-width:450px;">
  <p>Összeismerkedsz egy családdal, Kissékkel, akik a közelben nyaralnak. Amikor szóba kerül, hogy mennyire szeretik a busát, de sehol sem lehet kapni, felajánlod, hogy kifogsz nekik párat. Fogj 10 darab busát a családnak!</p>
</div>
<div data-role="popup" id="popupInfo2" class="ui-content" data-theme="e" style="max-width:450px;">
  <p>Csak nem egy márnát láttál a vízben? Még soha életedben nem fogtál ki egyetlen márnát sem! Gyorsan fogj ki 15 halat, hátha az egyik márna lesz!</p>
</div>
<div data-role="popup" id="popupInfo3" class="ui-content" data-theme="e" style="max-width:450px;">
  <p>A piacon most nagyon jól fizetnek a balinért, úgyhogy legjobb lesz, ha megragadod az alkalmat és kifogsz 30 balint, hogy bezsebelj egy rakás pénzt!</p>
</div>
<div data-role="popup" id="popupInfo4" class="ui-content" data-theme="e" style="max-width:450px;">
  <p>Arni, az egyik ivócimborád órákon keresztül azt meséli mindenkinek, hogy akkora angolnát fogott legutóbbi angliai útja során, amelyről itthon álmodni sem mernétek. Fogj ki egy hatalmas angolnát, hogy bebizonyítsd Arninak, hogy idehaza is vannak megtermett példányok ebből a halból!</p>
</div>
<div data-role="popup" id="popupInfo5" class="ui-content" data-theme="e" style="max-width:450px;">
  <p>Cimboráid meggyőznek, hogy menjetek át a Mrtvica-tóhoz, mert ott sokkal több halat fogtok fogni. Beleegyezel, ám előtte még gyorsan kifogsz 5 márnát.</p>
</div>
