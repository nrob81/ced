<?php
class BadgeActivator extends Badge
{
    protected function activate($id)
    {
        $redis = Yii::app()->redis->getClient();
        $saved = $redis->sadd('badges:owned:'.$this->uid, $id);
        if ($saved) {
            //save the actual timestamp
            $redis->zadd('badges:added:'.$this->uid, time(), $id);

            $badge = $this->getBadge($id);

            $score = 1;

            if ($badge['level'] == self::LEVEL_SILVER) {
                $score = 3;
            }

            if ($badge['level'] == self::LEVEL_GOLD) {
                $score = 9;
            }

            $redis->zIncrBy("badges:leaderboard", $score, $this->uid);

            $this->postToWall($badge);
        }
        return $saved;
    }

    private function postToWall($badge)
    {
        $wall = new Wall;
        $wall->content_type = Wall::TYPE_BADGE;
        $wall->uid = $this->uid;
        $wall->add($badge);
    }
}
