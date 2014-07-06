<div class="responsive-a info ui-grid-a ui-responsive">
    <div class="block ui-block-a">
        alapító: <span><?= $club->ownerName; ?></span><br/>
        <span><?= CHtml::link('klubtagok', '#popupUsers', ['data-rel'=>'popup']); ?></span><br/>
        alapítva: <span><?= date('Y.m.d. H:i', strtotime($club->created)); ?></span><br/>
        versenyezne: <span>
        <?php if (isset($editable) and $editable): ?>
            <?= CHtml::link($club->would_compete?'igen':'nem', ['', 'switch'=>'compete'], ['data-ajax'=>'false']);?></span><br/>
        <?php else: ?>
            <?= $club->would_compete?'igen':'nem';?></span><br/>
        <?php endif;?>
    </div>
    <div class="block ui-block-b">
        aktuális ranglistás helyezés : <span><?= $club->getRank(true) ? '#'.$club->getRank(true) : '-' ?></span><br/>
        összesített rangl. helyezés: <span><?= $club->getRank() ? '#'.$club->getRank() : '-' ?></span><br/>
        legutóbbi versenyek: <span><?= CHtml::link('lista', ['/club/history', 'id'=>$club->id]); ?></span><br/>
    </div>
</div><!-- /info -->

<div data-role="popup" id="popupUsers">
    <ul data-role="listview" data-split-icon="<?= $editable ? 'delete' : 'fishing-reel'; ?>" data-inset="true" style="min-width:210px;">
        <li data-role="divider">Klubtagok</li>
        <li>
            <?php
            echo CHtml::link($club->ownerName, ['/player/profile', 'uid'=>$club->owner]);
            if (!$editable) {
                echo CHtml::link('Párbaj', ['duel/go', 'opponentId'=>$club->owner]);
            }
            ?>
        </li>
        <?php foreach ($club->members as $member): ?>
        <li>
            <?php
            echo CHtml::link($member['user'], ['/player/profile', 'uid'=>$member['uid']]);
            if ($editable) {
                echo CHtml::link('Kirúgás', ['', 'fire'=>$member['uid']]);
            } else {
                echo CHtml::link('Párbaj', ['duel/go', 'opponentId'=>$member['uid']]);
            }
            ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
