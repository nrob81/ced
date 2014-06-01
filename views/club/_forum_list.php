<?php 
$cs = Yii::app()->clientScript;
$cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.timeago.js', CClientScript::POS_HEAD);
$cs->registerScript('timeago', '
    $(document).on("pageinit", function(event){
        $("abbr.timeago").timeago();
    });'
, CClientScript::POS_HEAD);
?>

<ul class="forum-list" data-role="listview">
    <?php if (isset($club)): ?><li data-role="list-divider">Fórum</li><?php endif; ?>
    <?php foreach($list as $item) Yii::app()->controller->renderPartial('_forumpost', ['item' => $item, 'page'=>$page]); ?>
    <?php if (isset($club)): ?>
    <li data-theme="d">
        <?= CHtml::link('tovább a fórumba..', ['/club/forum', 'id'=>$club->id]); ?>
    </li>
    <?php endif; ?>
</ul>

<?php if(isset($pagination)): ?>
    <div class="center-wrapper">
    <?php 
    $this->widget('JqmLinkPager', array(
        'currentPage'=>$pagination->getCurrentPage(),
        'itemCount'=>$count,
        'pageSize'=>$page_size
    ));
    ?>
    </div>
<?php endif; ?>
