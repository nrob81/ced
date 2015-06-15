<?php
class CompetitorTest extends CTestCase
{
    private $p = [
        1 => [
        'uid'=>1981,
        'skill_extended'=>90,
        'energy'=>100,
        'dollar'=>1234,
        'chance'=>90,
        'reqDollar'=>123,
        'awardXp'=>10,
        'awardDollar'=>5.5,
        ],
        2 => [
        'uid'=>1989,
        'skill_extended'=>10,
        'energy'=>100,
        'dollar'=>550,
        'chance'=>10,
        'reqDollar'=>55,
        'awardXp'=>90,
        'awardDollar'=>110.7,
        ]
        ];

    public function testCompetitor1()
    {
        $c = new Competitor();

        $c->uid = $this->p[1]['uid'];
        $c->skill = $this->p[1]['skill_extended'];
        
        $sumSkill = $c->skill + $this->p[2]['skill_extended'];
        $c->chance = round($c->skill / ($sumSkill / 100));

        $c->energy = $this->p[1]['energy'];

        $avgEnergy = round(($c->energy + $this->p[2]['energy']) / 2);
        $c->avgEnergy = $avgEnergy;

        $c->dollar = round($this->p[1]['dollar'] / 10);
        $c->opponent = [
            'chance'=>round($this->p[2]['skill_extended'] / ($sumSkill / 100)), 
            'dollar'=>round($this->p[2]['dollar'] / 10)
            ];

        $this->assertEquals($c->uid, $this->p[1]['uid']);
        $this->assertEquals($c->skill, $this->p[1]['skill_extended']);
        $this->assertEquals($c->chance, $this->p[1]['chance']);
        $this->assertEquals($c->dollar, $this->p[1]['reqDollar']);
        $this->assertEquals($c->opponent['dollar'], $this->p[2]['reqDollar']);

        return $c;
    }

    public function testCompetitor2()
    {
        $o = new Competitor();

        $o->uid = $this->p[2]['uid'];
        $o->skill = $this->p[2]['skill_extended'];
        
        $sumSkill = $o->skill + $this->p[1]['skill_extended'];
        $o->chance = round($o->skill / ($sumSkill / 100));

        $o->energy = $this->p[2]['energy'];

        $avgEnergy = round(($o->energy + $this->p[1]['energy']) / 2);
        $o->avgEnergy = $avgEnergy;

        $o->dollar = round($this->p[2]['dollar'] / 10);
        $o->opponent = [
            'chance'=>round($this->p[1]['skill_extended'] / ($sumSkill / 100)), 
            'dollar'=>round($this->p[1]['dollar'] / 10)
            ];

        $this->assertEquals($o->uid, $this->p[2]['uid']);
        $this->assertEquals($o->skill, $this->p[2]['skill_extended']);
        $this->assertEquals($o->chance, $this->p[2]['chance']);
        $this->assertEquals($o->dollar, $this->p[2]['reqDollar']);
        $this->assertEquals($o->opponent['dollar'], $this->p[1]['reqDollar']);

        return $o;
    }
    
    /**
     * @depends testCompetitor1
     * @depends testCompetitor2
     */
    public function testWinCompetitor1($c, $o)
    {
        $c->play(true, new Player());
        $o->play(false, new Player());

        $this->assertEquals($c->reqEnergy, $c->energy);
        $this->assertEquals($c->reqDollar, 0);
        $this->assertEquals($c->awardXp, round($c->avgEnergy * ($this->p[2]['chance'] / 100)));
        $this->assertEquals($c->awardDollar, round($this->p[2]['reqDollar'] * ($this->p[2]['chance'] / 100)));
        $this->assertEquals($c->awardPoints, round($c->avgEnergy * ($c->compensator($this->p[2]['chance']) / 100)));
        
        $this->assertEquals($o->reqEnergy, $o->energy);
        $this->assertEquals($o->reqDollar, round($o->dollar * ($o->chance / 100)));
        $this->assertEquals($o->awardXp, round($o->avgEnergy * ($o->chance / 100) / 5));
        $this->assertEquals($o->awardDollar, 0);
        $this->assertEquals($o->awardPoints, 0);
    }
    
    /**
     * @depends testCompetitor1
     * @depends testCompetitor2
     */
    public function testLoseCompetitor1($c, $o)
    {
        $c->resetAwards();
        $o->resetAwards();

        $c->play(false, new Player());
        $o->play(true, new Player());
        
        $this->assertEquals($c->reqEnergy, $c->energy);
        $this->assertEquals($c->reqDollar, round($c->dollar * ($c->chance / 100)));
        $this->assertEquals($c->awardXp, round($c->avgEnergy * ($c->chance / 100) / 5));
        $this->assertEquals($c->awardDollar, 0);
        $this->assertEquals($c->awardPoints, 0);
        
        $this->assertEquals($o->reqEnergy, $o->energy);
        $this->assertEquals($o->reqDollar, 0);
        $this->assertEquals($o->awardXp, round($o->avgEnergy * ($this->p[1]['chance'] / 100)));
        $this->assertEquals($o->awardDollar, round($this->p[1]['reqDollar'] * ($this->p[1]['chance'] / 100)));
        $this->assertEquals($o->awardPoints, round($o->avgEnergy * ($o->compensator($this->p[1]['chance']) / 100)));
    }
}
