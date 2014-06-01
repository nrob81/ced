<?php
class JqmLinkPager extends CLinkPager
{
    public $header = '';
    public $htmlOptions = array('class'=>'', 'data-role'=>'controlgroup', 'data-type'=>'horizontal', 'data-mini'=>'true');
    public $maxButtonCount=5;
    public $nextPageLabel = '>';
    public $prevPageLabel = '<';
    public $firstPageLabel = '<<';
    public $lastPageLabel = '>>';

    protected function createPageButton($label,$page,$class,$hidden,$selected)
    {
        if($hidden) return false;
        if ($selected) {
            $class = 'ui-disabled';
        }
        return CHtml::link($label,$this->createPageUrl($page), ['data-role'=>'button', 'data-ajax'=>'false', 'class'=>$class]);
    }

}

