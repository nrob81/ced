<?php
class ContestTest extends CTestCase
{
    private $_inactiveId = 0;

    public function testAddContest() 
    {
        Yii::app()->redis->getClient()->set('contest:r_collect', 'xp'); //set recommended collection type for testing

        $ct = new Contest();
        $this->assertFalse($ct->activeId);
        
        $saved = $ct->create();
        $this->assertTrue($saved);
        $this->assertTrue(isset($ct->activeId));
    }

    public function testAddPoints()
    {
        $ct = new Contest();
        $this->assertTrue(isset($ct->activeId));

        for ($i=0; $i<50; $i++) {
            $uid = rand(1981, 1988);

            $added = $ct->addPoints($uid, Contest::ACT_MISSION, 1, 1, 1); //uid, activity, energy, xp, dollar,
            $this->assertTrue($added);
        }
    }

    public function testInactiveContest()
    {
        if (!$this->_inactiveId) return false; 

        $cl = new ContestList();
        $cl->id = $this->_inactiveId;
        $this->assertTrue($cl->isValid);
        $this->assertFalse($cl->isActive);

        //$this->assertTrue( $cl->secUntilEnd <= 0 );
        $this->assertTrue( $cl->hasWinner() );
    }
    
    public function testActiveContest()
    {
        $cl = new ContestList();
        $ct = new Contest();
        $cl->id = $ct->activeId;

        $this->assertTrue($cl->isValid);
        $this->assertTrue($cl->isActive);

        $this->assertTrue( $cl->secUntilEnd > 0 );
        $this->assertFalse( $cl->hasWinner() );
    }

    public function testCompleteContest() 
    {
        $ct = new Contest();
        $this->assertTrue(isset($ct->activeId));
        $activeId = $ct->activeId;

        $completed = $ct->complete();
        $this->assertEquals(1, $completed);
        $this->assertFalse($ct->activeId);

        //completed contest
        $cl = new ContestList();
        $cl->id = $activeId;

        $this->assertTrue( $cl->secUntilEnd > 0 );
        $this->assertTrue( $cl->hasWinner() );

        //$cl->printDebug();
    }

}

