<div class="spr tutorial">
<p>
<?php
$descr = [
1=>'Üdvözöllek a parton! A horgászkarriered beindításához szükséged lesz pár dollárra. Ezt megbízások teljesítésével szerezheted meg.',
'Megy ez, teljesítetted az első megbízásodat. A megbízó elégedett az eredménnyel.',
'Megszerezted a szükséges pénzt. A lenti megbízások közt találsz egyet, amelynél jeleztük, hogy mennyi esélyed van az elvégzésére. Csak ott látsz ilyen jelzést, ahol a sikeres teljesítés esélye kevesebb, mint 100%.',
'Egy megbízás teljesítésekor mindig növekszik kicsit a rutinod.',
'Remek! A főmegbízást csak akkor teljesítheted, ha az itt látható összes megbízásnál eléred a 100% rutint.',
'Most már teljesítheted a főmegbízást, amiért cserébe elérhetővé válik számodra a következő helyszín.',
'Gratulálok! Teljesítetted a főmegbízást, kaptál 10 aranyat és lehetőséget arra, hogy tovább utazz.',
];
if (isset($descr[$id])) echo $descr[$id];
?><br/>
<strong>Feladat: </strong> 
<?php
$tasks = [
1=>'Teljesíts egy megbízást.',
'Keress 10$-t, hogy vehess egy jobb felszerelést és csalit. Egy megbízást többször is teljesíthetsz.',
'Növeld meg az esélyedet azzal, hogy vásárolsz egy felszerelést és csalit is mellé.',
'Érj el 100% rutint bármelyik megbízásnál azzal, hogy újra és újra teljesíted.',
'Érj el 100% rutint az összes megbízásnál.',
'Teljesítsd a főmegbízást.',
'Utazz a következő helyszínre. Kattints a helyszín nevére és válassz úticélt magadnak. Később a térképet is használhatod.',
];
if (isset($tasks[$id])) echo $tasks[$id];
?>
</p>
</div>
