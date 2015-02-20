<?php

/**
 * This is the model class for table "alterList".
 *
 * The followings are the available columns in table 'alterList':
 * @property integer $id
 * @property integer $studyId
 * @property string $name
 * @property string $email
 * @property integer $ordering
 */
class AlterList extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AlterList the static model class
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
		return 'alterList';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('studyId, name, ordering', 'required'),
			array('studyId, ordering', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, studyId, name, email, ordering, interviewerId', 'length', 'max'=>1024),
			array('interviewerId','default',
				'value'=>0,
			'setOnEmpty'=>true),
			array('id, studyId, name, email, ordering', 'safe', 'on'=>'search'),
		);
	}

	public static function sortOrder($ordering, $studyId)
	{
		$criteria = new CDbCriteria();
		$criteria=array(
			'condition'=>"studyId = " . $studyId . " AND ordering > ".$ordering ,
			'order'=>'ordering',
		);
		$models = AlterList::model()->findAll($criteria);
		foreach($models as $model){
			AlterList::moveUp($model->id);
		}
	}

	public static function moveUp($id)
	{
		$model = AlterList::model()->findByPk($id);
		if($model && $model->ordering > 0){
			$old_model = AlterList::model()->findByAttributes(array('studyId'=>$model->studyId,'ordering'=>$model->ordering-1));
			if($old_model){
				$old_model->ordering = $model->ordering;
				$old_model->save();
			}
			$model->ordering--;
			$model->save();
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
			'studyId' => 'Study',
			'name' => 'Name',
			'email' => 'Email',
			'ordering' => 'Ordering',
			'interviewId' => 'interview Id',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('studyId',$this->studyId);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('ordering',$this->ordering,true);
		$criteria->compare('interviewId',$this->interviewId,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Encrypts "name" and "email" attributes before they're saved.
     * @return bool|void
     */
    public function beforeSave() {
        $this->name = encrypt( $this->name );
        $this->email = encrypt( $this->email );

        return parent::beforeSave();
    }

    /**
     * Decrypts "name" and "email" attributes after they're found.
     */
    protected function afterFind() {
        $this->name = decrypt( $this->name );
        $this->email = decrypt($this->email );

        return parent::afterFind();
    }

}
