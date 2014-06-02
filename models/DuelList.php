<?php
/**
 * @property array $opponents
 * @property CPagination $pagination
 * @property integer $count
 * @property string $clubName
 * @property integer $page
 */
class DuelList extends CModel
{
    const LIMIT_WEAKER_OPPONENT_LEVEL_DIFF = 5;
    const REQ_LEVEL = 10;

    private $opponents = [];
    private $pagination;
    private $count;
    private $page = 0;

    public function attributeNames() 
    {
        return [];
    }

    public function getOpponents() 
    { 
        return $this->opponents; 
    }
    
    public function getPagination() 
    { 
        return $this->pagination; 
    }

    public function getCount() 
    { 
        return $this->count; 
    }

    public function getClubName($club, $id) 
    {
        if ($id) {
            $club->id = $id;
            $club->fetchName();
            return '<span> | ' .  $club->name . '</span>';
        }
        return '';
    }

    public function setPage($page) 
    {
        $this->page = $page;
    }

    public function fetchOpponents() 
    {
        $player = Yii::app()->player->model;
        $limit = Yii::app()->params['listPerPage'];
        $minLevel = $player->level - self::LIMIT_WEAKER_OPPONENT_LEVEL_DIFF;
        if ($minLevel < self::REQ_LEVEL) {
            $minLevel = self::REQ_LEVEL;
        }
        //$maxLevel = $player->level + (self::LIMIT_WEAKER_OPPONENT_LEVEL_DIFF * 2);

        $this->count = Yii::app()->db->createCommand()
            ->select('COUNT(*) AS count')
            ->from('main')
            ->where('uid <> :uid AND level >= :minLevel', [':uid'=>$player->uid,':minLevel'=>$minLevel])
            ->queryScalar();

        $res = Yii::app()->db->createCommand()
            ->select('uid, user, level, energy_max, energy, dollar, in_club')
            ->from('main')
            ->where('uid <> :uid AND level >= :minLevel', [':uid'=>$player->uid,':minLevel'=>$minLevel])
            ->order('level ASC')
            ->limit($limit, ($this->page * $limit) - $limit) // the trick is here!
            ->queryAll();

        $this->pagination = new CPagination($this->count);
        $this->pagination->setPageSize(Yii::app()->params['listPerPage']);

        $club = new Club();
        foreach ($res as $item) {
            $item['disabled'] = 0;
            if ($item['energy'] <= $item['energy_max'] / 10) $item['disabled'] = 1; //low energy
            $item['prize'] = round($item['dollar'] / 10);
            $item['clubName'] = $this->getClubName($club, $item['in_club']);
            
            $this->opponents[$item['uid']] = $item;
        }
    }
    
    public function fetchCommonRivals() 
    {
        $player = Yii::app()->player->model;
        $limit = Yii::app()->params['listPerPage'];

        $res = Yii::app()->db->createCommand()
            ->select('uid, COUNT(*) AS cnt')
            ->from('duel_player')
            ->where('uid <> :uid AND duel_id IN (SELECT duel_id FROM duel_player WHERE uid=:uid)', [':uid'=>$player->uid])
            ->group('uid')
            ->order('cnt DESC')
            ->limit($limit)
            ->queryAll();
        $club = new Club();
        foreach ($res as $item) {
            $p = Yii::app()->db->createCommand()
                ->select('user, energy, energy_max, dollar, level, in_club')
                ->from('main')
                ->where('uid=:uid', [':uid'=>(int)$item['uid']])
                ->queryRow();
            if (!is_array($p)) continue;

            foreach ($p as $k => $v) {
                $item[$k] = $v;
            }

            $item['disabled'] = 0;
            if ($item['energy'] <= $item['energy_max'] / 10) $item['disabled'] = 1; //low energy
            if ($player->level - $item['level'] > self::LIMIT_WEAKER_OPPONENT_LEVEL_DIFF) $item['disabled'] = 2; //too weak

            $item['prize'] = round($item['dollar'] / 10);
            $item['clubName'] = $this->getClubName($club, $item['in_club']);
            $this->opponents[$item['uid']] = $item;
        }
    }
    
    public function fetchLastRivals() 
    {
        $player = Yii::app()->player->model;
        $limit = Yii::app()->params['listPerPage'];

        $res = Yii::app()->db->createCommand()
            ->select('id, caller, created')
            ->from('duel')
            ->where('opponent=:uid', [':uid'=>$player->uid])
            ->order('id DESC')
            ->limit($limit)
            ->queryAll();
        $club = new Club();
        $cache = [];
        foreach ($res as $item) {
            $item['uid'] = $item['caller'];

            if (!isset($cache[$item['uid']])) {
                $p = Yii::app()->db->createCommand()
                    ->select('user, energy, energy_max, dollar, level, in_club')
                    ->from('main')
                    ->where('uid=:uid', [':uid'=>(int)$item['uid']])
                    ->queryRow();
                if (!is_array($p)) continue;
                $cache[$item['uid']] = $p;
            } else {
                $p = $cache[$item['uid']];
            }

            foreach ($p as $k => $v) {
                $item[$k] = $v;
            }

            $item['disabled'] = 0;
            if ($item['energy'] <= $item['energy_max'] / 10) $item['disabled'] = 1; //low energy

            $item['prize'] = round($item['dollar'] / 10);
            $item['clubName'] = $this->getClubName($club, $item['in_club']);
            $this->opponents[] = $item;
        }
    }
}
