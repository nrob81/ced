<div class="nav">
    <div class="right">
        <?php if (isset($opponent)): ?>
            <a href="<?= $this->createUrl('duel/go', ['opponentId'=>$opponent]); ?>" data-role="button" data-inline="true" data-mini="true">újra</a>
        <?php endif; ?>

        <?php if (isset($replay)): ?>
            <a href="<?= $this->createUrl('duel/go', ['opponentId'=>$replay['uid']]); ?>" data-role="button" data-inline="true" data-mini="true"><?= $replay['title'] ?></a>
        <?php endif; ?>
        
        <a href="#popupMenu" data-rel="popup" data-role="button" data-inline="true" data-mini="true">Szűrők</a>
        <div data-role="popup" id="popupMenu">
            <ul data-role="listview" data-inset="true" style="min-width:210px;">
                <li data-role="divider">Mire vagy kíváncsi?</li>
                <li><?= CHtml::link('Jelentkezők', ['/duel']); ?></li>
                <li><?= CHtml::link('Gyakori ellenfelek', ['/duel/common']); ?></li>
                <li><?= CHtml::link('Legutóbbi ellenfelek', ['/duel/history']); ?></li>
            </ul>
        </div>    
    </div>
    
    <h1><?php echo  isset($title) ? $title : 'Pecapárbaj'; ?></h1>
</div>
