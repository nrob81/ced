<div class="nav subhead">
    <?= CHtml::link('térkép', ['map'], ['id'=>'map', 'class'=>'right spr', 'rel'=>'external']); ?>

    <div data-role="popup" id="popupMenu">
        <ul data-role="listview" data-inset="true" style="min-width:210px;">
            <li data-role="divider">Merre szeretnél utazni?</li>
            <?php
                foreach ($nav as $link) {
                    $disabled = !$link['active'] ? ' class="ui-disabled"' : '';
                    $data_icon = $link['type']=='prev' ? ' data-icon="carat-l"' : '';
                    $url = $link['active'] ? $widget->createUrl('missions/list', ['id'=>$link['id']]) : '#';

                    echo '<li' . $disabled . $data_icon . '>';
                    echo CHtml::link($link['title'], $url);
                    echo '</li>';
                }
            ?>
        </ul>
    </div>
    <h1><?= CHtml::link($name['location'], '#popupMenu', ['data-rel'=>'popup']); ?></h1>
    <h2><?= $name['county']; ?> megye</h2>
</div>
