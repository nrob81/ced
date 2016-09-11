<li data-role="list-divider"><?=  $item['title'] ?></li>
<li>
    <?php if ($item['sold']): ?><p class="success">Az elkészített szett bekerült a felszereléseid közé.</p><?php endif; ?>
    <p>Az összeszerelt felszerelés szakértelempontja <strong> <?= $item['skill_multiplicator'] ?>x nagyobb </strong> lesz a most megvásárolható legjobb felszerelésnél.</p>

    <form action="<?= $this->createUrl(''); ?>" method="post" class="ui-mini">
        <?php if ($item['constructable']): ?>
        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
        <input type="submit" value="elkészítés">
        <?php else: ?>
        <input type="submit" value="elkészítés" disabled="disabled">
        <?php endif; ?>
    </form>

    <h3>A szett elemei</h3>
    <p>
        <?php
        foreach ($item['items'] as $i) {
            echo $i->owned . 'x ' . $i->title . '<br/>';
        }
        ?>
    </p>
</li>
