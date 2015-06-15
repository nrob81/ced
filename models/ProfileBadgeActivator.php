<?php
class ProfileBadgeActivator extends BadgeActivator
{
    public function triggerMaxNrg($max)
    {
        if ($max >= 35) {
            $this->activate('max_nrg_35');
        }

        if ($max >= 100) {
            $this->activate('max_nrg_100');
        }
    }

    public function triggerSkill($max)
    {
        if ($max >= 35) {
            $this->activate('skill_35');
        }

        if ($max >= 100) {
            $this->activate('skill_100');
        }
    }

    public function triggerStrength($max)
    {
        if ($max >= 35) {
            $this->activate('strength_35');
        }

        if ($max >= 100) {
            $this->activate('strength_100');
        }
    }

    /**
     * @param integer $uid
     */
    public function triggerDollar($uid, $dollar)
    {
        $this->setUid($uid);
        if ($dollar >= 50) {
            $this->activate('dollar_50');
        }
        if ($dollar >= 5000) {
            $this->activate('dollar_5000');
        }
    }

    /**
     * @param integer $uid
     * @param integer $level
     */
    public function triggerLevel($uid, $level)
    {
        $this->setUid($uid);
        if ($level >= 10) {
            $this->activate('level_10');
        }
        if ($level >= 100) {
            $this->activate('level_100');
        }
    }
}
