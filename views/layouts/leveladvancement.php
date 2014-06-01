<div data-role="popup" id="popupLevelAdvancement" class="ui-content" style="max-width:280px">
    <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
    <h3>Fejlődtél!</h3>
    <p>A sok pecázás meghozta gyümölcsét!<br/>
    Szereztél pár státuszpontot, amit felhasználhatsz a karaktered fejlesztésére.</p>
    <?= CHtml::link('Fejlesztés', ['/player'], ['data-role'=>'button', 'data-theme'=>'e', 'data-ajax'=>'false']); ?>
</div>

