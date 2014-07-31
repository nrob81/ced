<li>
    <?php $paramPage = $page ? ['page'=>$page] : []; ?>
    <form action="<?= $this->createUrl('', $paramPage) ?>" method="post" data-ajax="false">
    <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
    <input type="hidden" name="item_id" value="<?= $item->id ?>" />
    <?php foreach ($item->sell_amount as $amount => $enabled): ?>
        <?php if ($enabled): ?>
        <button name="amount" value="<?= $amount ?>"><?= $mode.$amount ?></button>
        <?php else: ?>
        <button disabled="disabled"><?= $mode.$amount ?></button>
        <?php endif; ?>
    <?php endforeach; ?>
    </fieldset>
    </form>

    <h3><?= $item->title ?></h3>

    <?php if (isset($notify) && $notify): ?>
        <?php if ($item->errors['amount']): ?><p class="error">Egynél kevesebb csalit nem adhatsz el.</p><?php endif; ?>
        <?php if ($item->errors['dollar']): ?><p class="error">Nincs ennyi eladnivalód.</p><?php endif; ?>
        <?php if ($item->errors['isLast']): ?><p class="error">Az utolsó felszerelést és csalit nem adhatod el.</p><?php endif; ?>
        <?php if ($item->success): ?><p class="success">Köszönöm, hogy nálam üzleteltél!</p><?php endif; ?>
    <?php endif; ?>

    <p>+<?= $item->skill ?> SzP, <?= $item->price_sell ?>$</p>
    <?php if ($item->owned): ?>
    <p>saját: <?= $item->owned ?>db</p>
    <?php endif; ?>
</li>
