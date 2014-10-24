<?php
/**
 * @property string $title
 * @property string $topic
 */
class HelpWidget extends CWidget
{
    public $title = '';
    public $topic = '';

    public function run()
    {
        $help = new Help;
        $help->topic = $this->topic;
        $item = $help->getRandomItemByType();
        if ($item) {
            echo '<p class="spr help"><strong>tipp:</strong><br/>' . $item . '</p>';
        }
    }
}
