<?php
class MaintenanceChallenge extends CModel
{
    private $_finishable = [];

    public function attributeNames() {
        return [];
    }

    public function fetchFinishable() {
        $res = Yii::app()->db->createCommand()
            ->select('*')
            ->from('command_stack')
            ->where('process_time <= NOW()')
            ->queryAll();
        foreach ($res as $d) {
            $d['params'] = CJSON::decode($d['params']);
            $this->_finishable[$d['id']] = $d;
        }
    }

    public function process() {
        if (!count($this->_finishable)) return false;

        foreach ($this->_finishable as $cmd) {
            $command = $cmd['command'];
            if (method_exists('MaintenanceChallenge', $command)) {
                $this->$command($cmd['params']);
                $this->deleteCommand($cmd['id']);
            }
        }
    }

    private function endChallenge($params) {
        $id = (int)$params['id'];


        $ch = Yii::app()->db->createCommand()
            ->select('*')
            ->from('challenge')
            ->where('id=:id', [':id'=>$id])
            ->queryRow();

        if ($ch['winner']) return false;

        if ($ch['point_caller'] <> $ch['point_opponent']) {
            //not equal points
            $winnerTag = $ch['point_caller'] < $ch['point_opponent'] ? 'opponent' : 'caller'; //caller only lose, if she has less points, that opponent.
        } else {
            //equal points
            $winnerTag = $ch['cnt_won_caller'] < $ch['cnt_won_opponent'] ? 'opponent' : 'caller'; //caller only lose, if she has less won games, that opponent.
        }

        $looserTag = $winnerTag == 'caller' ? 'opponent' : 'caller';

        $forum = new Forum;

        //without games
        if (!$ch['cnt_won_caller'] and !$ch['cnt_won_opponent']) {
            //close unplayed challenge, no winners
            $msg = "{$ch['name_caller']} - {$ch['name_opponent']}: ez a verseny párbajok nélkül ért véget, így jutalmat sem kaptok.";
            $forum->id = $ch['caller'];
            $forum->save($msg, true);
            $forum->id = $ch['opponent'];
            $forum->save($msg, true);

            //update the winner-state
            Yii::app()->db->createCommand()
                ->update('challenge', ['winner'=>3], 'id=:id', [':id'=>$id]);
            return false;
        }

        $forum->id = $ch[$winnerTag];

        $club = new Club;
        $club->id = $ch[$winnerTag]; //winner club
        $club->fetch();
        $club->fetchMembers();


        $winnerMsg = "Gratulálok! :) Győztetek a következő versenyben: <b> {$ch['name_caller']} - {$ch['name_opponent']}</b>. ";
        $looserMsg = "Sajnos elbuktátok a következő versenyt: <b> {$ch['name_caller']} - {$ch['name_opponent']}</b>. ";

        //distribute the loot
        $members = [$club->owner=>$club->ownerName];
        foreach ($club->members as $member) {
            $members[$member['uid']] = $member['user'];
        }
        $cntMembers = count($members);
        $loot = (int)$ch['loot_'.$winnerTag];
        $lootPerMember = floor($loot / $cntMembers);

        $forum->id = $ch[$winnerTag];
        if ($lootPerMember > 0) {
            $winnerMsg .= "Minden tag <b> {$lootPerMember}$</b>-t kap a zsákmányból. Név szerint: ";

            $contest = new Contest;
            $listPlayers = [];
            $p = new Player;
            foreach ($members as $uid => $member) {
                $p->setAllAttributes($uid);
                $incr = ['dollar'=>$lootPerMember];
                $p->updateAttributes($incr, []);
                $contest->addPoints($p->uid, Contest::ACT_DUEL, 0, 0, $incr['dollar']);

                $listPlayers[] = $p->user;
            }
            $winnerMsg .= join(', ', $listPlayers) . '. ';
        } else {
            $winnerMsg .= "Sajnos a zsákmány ({$loot}$) túl alacsony ahhoz, hogy osztozzatok rajta. ";
        }
        $lootLosers = (int)$ch['loot_'.$looserTag];
        $looserMsg .= "Mivel nem nyertetek, a zsákmány ({$lootLosers}$) a horgásszövetség tulajdonába kerül. Köszönik szépen!";

        //refresh the toplist
        $winnerPoints = (int)$ch['point_'.$winnerTag];
        if (!$winnerPoints) $winnerPoints = 1; //when result is 0:0

        Yii::app()->redis->getClient()->zIncrBy('board_c:'.date('Ym'), $winnerPoints, $ch[$winnerTag]);

        $winnerMsg .= "A klub <b> {$winnerPoints} pontot </b> erősödött a ranglistán.";

        //report
        $forum->id = $ch[$winnerTag];
        $forum->save($winnerMsg, true);
        $forum->id = $ch[$looserTag];
        $forum->save($looserMsg, true);

        //refresh the winner state of the challenge
        $winner = $winnerTag=='caller' ? 1 : 2;
        Yii::app()->db->createCommand()
            ->update('challenge', ['winner'=>$winner], 'id=:id', [':id'=>$id]);

        return true;
    }

    private function deleteCommand($id) {
        Yii::app()->db->createCommand()
            ->delete('command_stack', 'id=:id', [':id'=>(int)$id]);
    }
}
