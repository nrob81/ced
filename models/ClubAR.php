<?php

/**
 * This is the model class for table "club".
 *
 * The followings are the available columns in table 'club':
 * @property string $id
 * @property integer $owner
 * @property string $name
 * @property string $created
 */
class ClubAR extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Club the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'club';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            ['name', 'clean'],
			['name', 'required'],
			['name', 'length', 'min'=>6, 'max'=>14],
            ['name', 'levelRequirement'],
            ['name', 'hasOtherClub'],
            ['name', 'nameIsFree'],
			['id, owner', 'safe', 'on'=>'search'],
		);
    }

    public function clean($attribute) {
        $this->$attribute = trim($this->$attribute);
        $this->$attribute = strip_tags($this->$attribute);
        $this->$attribute = htmlspecialchars($this->$attribute);
    }
    
    public function levelRequirement($attribute, $params) {
        if (Yii::app()->player->model->level < 30) {
            $this->addError($attribute, 'Saját klub indításához minimum 30-as szintre kell fejlődnöd.');
        }
    }
    public function hasOtherClub($attribute, $params) {
        if (Yii::app()->player->model->in_club) {
            $this->addError($attribute, 'Egy másik klub tagja vagy. Először lépj ki abból.');
        }
    }
    public function nameIsFree($attribute, $params) {
        $clubWithSameName = Yii::app()->db->createCommand()
                ->select('id')
                ->from('club')
                ->where('name=:name', [':name'=>$this->$attribute])
                ->queryScalar();
        if ($clubWithSameName) {
            $this->addError($attribute, 'Ez a név már foglalt.');
        }
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'owner' => 'Tulajdonos',
			'name' => 'Klub neve',
			'created' => 'Létrehozva',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('owner',$this->owner);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('created',$this->created,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    protected function beforeSave()
    {
        $this->owner = Yii::app()->player->uid;
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        $forum = new Forum;
        $forum->id = $this->id;
        $forum->save(Yii::app()->player->model->user . ' megalapította a klubot.', true);
        
        Yii::app()->badge->model->triggerSimple('club_create');

        //delete inactive join request
        Yii::app()->db->createCommand()->delete('club_members', 'uid=:uid AND approved=0', [':uid'=>Yii::app()->player->model->uid]);
        parent::afterSave();
    }
}
