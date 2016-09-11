<li>
    <?php $paramPage = $page ? ['page'=>$page] : []; ?>
    <form action="<?= $this->createUrl('', $paramPage) ?>" method="post" data-ajax="false">
    <fieldset class="ui-mini" data-role="controlgroup" data-type="horizontal">
    <input type="hidden" name="item_id" value="<?= $item->id ?>" />
    <?php foreach ($item->buy_amount as $amount => $enabled): ?>
        <?php if ($enabled): ?>
        <button name="amount" value="<?= $amount ?>"><?= $mode.$amount ?></button>
        <?php else: ?>
        <button disabled="disabled"><?= $mode.$amount ?></button>
        <?php endif; ?>
    <?php endforeach; ?>
    </fieldset>
    </form>

    <h3>
    <?php if ($item->level > Yii::app()->player->model->level): ?><span class="success">pult alól:</span> <?php endif; ?>
    <?= $item->title ?>
    </h3>

    <?php if (isset($notify) && $notify): ?>
        <?php if ($item->errors['amount']): ?><p class="error">Egynél kevesebb csalit nem vehetsz.</p><?php endif; ?>
        <?php if ($item->errors['dollar']): ?><p class="error">Nincs elég pénzed.</p><?php endif; ?>
        <?php if ($item->errors['isLast']): ?><p class="error">Az utolsó felszerelést és csalit nem adhatod el.</p><?php endif; ?>
        <?php if ($item->errors['freeSlots']): ?><p class="error">Csak annyi felszerelést és csalit vásárolhatsz, amekkora a teherbírásod.</p><?php endif; ?>
        <?php if ($item->success): ?><p class="success">Köszönöm, hogy nálam vásároltál!</p><?php endif; ?>
    <?php endif; ?>

    <p>+<?= $item->skill ?> SzP, <?= $item->price ?>$</p>
    <?php if ($item->owned): ?>
    <p>saját: <?= $item->owned ?>db</p>
    <?php endif; ?>
</li>
