<li>
    <?php $paramPage = $page ? ['page'=>$page] : []; ?>
    <?php if (!$item['disabled']): ?>
    <form class="right ui-mini" action="<?= $this->createUrl('go', $paramPage) ?>" method="get">
        <button name="opponentId" value="<?= $item['uid'] ?>">kihívás</button>
    </form>
    <?php elseif ($item['disabled'] == 1): ?>
    <form class="right ui-mini" action="#" method="get">
        <button name="opponentID" value="0" disabled="disabled">túl fáradt</button>
    </form>
    <?php elseif ($item['disabled'] == 2): ?>
    <form class="right ui-mini" action="#" method="get">
        <button name="opponentID" value="0" disabled="disabled">túl gyenge</button>
    </form>
    <?php endif; ?>

    <h3><?= CHtml::link($item['user'], ['player/profile', 'uid'=>$item['uid']]); ?> <?= $item['clubName']; ?></h3>
    <p>
        <?= $item['level'] ?>. szint,
        <?= $item['energy'] ?> energia,
        <?= $item['prize'] ?>$ tét.
        <?php if (isset($item['cnt'])): ?><strong><?= $item['cnt'] ?>x párbajoztál vele</strong><?php endif; ?>
        <?php if (isset($item['created'])): ?>
            <br/><strong>legutóbb: <?= $item['created'] ?></strong>
            <?= CHtml::link('megnézem', ['duel/replay', 'id'=>$item['id']]); ?>
        <?php endif; ?>
    </p>
</li>
