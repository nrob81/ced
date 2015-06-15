<?php
$this->pageTitle = 'Fórum';
?>

<div class="nav">
    <?= CHtml::link('vissza', ['club/details', 'id'=>$clubID], ['data-role'=>'button', 'data-mini'=>'true', 'class'=>'right']); ?>
    <h1>Fórum</h1>
</div>

<?php 
$this->renderPartial('_forum_form', ['clubID'=>$clubID]); 
$this->renderPartial('_forum_list', [
    'list'=>$list,
    'pagination'=>$pagination,
    'count'=>$count,
    'page_size'=>$page_size,
    'page'=>$page
    ]);
?>
