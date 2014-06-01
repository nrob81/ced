<?php
class ClubCompetitor extends Competitor
{
    protected $lootDollar = 0;

    public function getLootDollar()
    {
        return $this->lootDollar;
    }

    protected function updateAttributes($player)
    {
        parent::updateAttributes($player);
        
        //put back loot (for view)
        $this->awardDollar = $this->lootDollar;
    }
    protected function winPrize()
    {
        parent::winPrize();

        if (!$this->opponent['energy']) {
            $this->awardDollar = 0;
        }

        //add to loot, instead of player
        $this->lootDollar = $this->awardDollar;
        $this->awardDollar = 0;
    }
    
    protected function losePrize()
    {
        parent::losePrize();

        if (!$this->isCaller) {
            if (!$this->energy) {
                $this->reqDollar = 0;
            }
        }
    }
}
