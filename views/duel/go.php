<?php
$this->pageTitle='Pecapárbaj';
$this->renderPartial('/duel/nav', ['title'=>'A párbaj eredménye', 'opponent'=>$duel->opponent->uid]);
?>

<?php if ($duel->played): ?>
    <div class="duel-grid ui-grid-a">
        <div class="ui-block-a">
            <?php $this->renderPartial('_parameters', [
                'user'=>$duel->caller->user, 
                'p'=>$duel->competitors[0], 
                'isChallenge'=>$duel->isChallenge,
                ]
            ); ?> 
        </div>
        <div class="ui-block-b">
            <?php $this->renderPartial('_parameters', [
                'user'=>$duel->opponent->user, 
                'p'=>$duel->competitors[1], 
                'isChallenge'=>$duel->isChallenge,
                ]
            ); ?> 
        </div>
    </div><!-- /grid-a -->
<?php else: ?>
    <p>Próbálkozz később, esetleg másik ellenféllel.</p>
<?php endif; ?>

<?php $this->widget('HelpWidget', ['topic'=>'duel']); ?>
