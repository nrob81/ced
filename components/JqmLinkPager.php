<?php
class JqmLinkPager extends CLinkPager
{
    public $header = '';
    public $htmlOptions = array('class'=>'ui-mini', 'data-role'=>'controlgroup', 'data-type'=>'horizontal');
    public $maxButtonCount=5;
    public $nextPageLabel = '>';
    public $prevPageLabel = '<';
    public $firstPageLabel = '<<';
    public $lastPageLabel = '>>';

    /**
     * @return string
     */
    protected function createPageButton($label, $page, $class, $hidden, $selected)
    {
        if ($hidden) {
            return false;
        }

        if ($selected) {
            $class = 'ui-state-disabled';
        }

        return CHtml::link($label, $this->createPageUrl($page), ['class'=>'ui-btn ' . $class, 'data-ajax'=>'false']);
    }
}
