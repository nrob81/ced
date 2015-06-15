<li><h2>Üdvözöllek a játékban!</h2>
    <p>Kedves <?= Yii::app()->player->model->user; ?>!<br/>
    Örömmel látom, hogy úgy döntöttél, megtanulsz horgászni és részt veszel egy izgalmas kiránduláson. Pár barátoddal együtt indultok el Magyarország egyik pontjáról és megyéről megyére utaztok majd. Az út során kifogott halakat gyakran eladjátok, aminek az árát 'frissítőkbe', ételbe és felszerelésbe fektetitek. :)</p>
    <p class="ui-li-aside"><strong><?= date('H:i', strtotime(Yii::app()->player->model->registered)); ?></strong></p>
</li>
<li>
    <p> </p>
    <p>Egyre jobb feleszereléseket és csalikat vehetsz majd, ahogy sikerül kisebb összegeket összegyűjtened. Ennek hatására növekszik a szakértelmed is. Később pecapárbajokon mérheted össze tudásodat, ahol másokat hívhatsz ki egy jó kis versenyre.<br/>
    Ahogy tovább fejlődsz, csatlakozhatsz klubokba, sőt sajátot is alapíthatsz és ez még csak a kezdet!</p>
    <p>Jó szórakozást kívánok!</p>
    <p class="ui-li-aside"><strong><?= date('H:i', strtotime(Yii::app()->player->model->registered)); ?></strong></p>
</li>
