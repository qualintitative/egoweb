<?php

/**
 * This is the model class for table "alters".
 *
 * The followings are the available columns in table 'alter':
 * @property integer $id
 * @property integer $random_key
 * @property integer $active
 * @property integer $ordering
 * @property integer $name
 * @property integer $interviewId
 * @property text $nameGenQIds
 */
class Alters extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Alters the static model class
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
		return 'alters';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ordering, name, interviewId, nameGenQIds', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, ordering, name, interviewId, nameGenQIds, ordering', 'length', 'max'=>1024),
			array('id, active, ordering, name, interviewId', 'safe', 'on'=>'search'),
		);
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

	public static function sortOrder($ordering, $interviewId, $nameGenQId)
	{
		$criteria = new CDbCriteria();
		$criteria=array(
			'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId) AND FIND_IN_SET($nameGenQId, nameGenQIds)",
		);
		$models = Alters::model()->findAll($criteria);
		foreach($models as $index=>$model){
			if(is_numeric($model->ordering)){
				$nGorder = array($nameGenQId=>$index);
				$model->ordering = json_encode($nGorder);
				$model->save();
			}
		}
		$criteria=array(
			'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId) AND JSON_EXTRACT(ordering, '$.$nameGenQId') > $ordering",
		);
		$models = Alters::model()->findAll($criteria);
        foreach ($models as $index=>$model) {
            Alters::moveUp($model->id, $nameGenQId);
        }
	}

	public static function moveUp($id, $nameGenQId)
	{
		$model = Alters::model()->findByPk($id);
		$nGorder = json_decode($model->ordering, true);
		if($model && $nGorder[$nameGenQId] > 0){
			$criteria=array(
				'condition'=>"JSON_EXTRACT(ordering, '$.$nameGenQId') = " . $nGorder[$nameGenQId]-1,
			);
			$old_model = Alters::model()->find($criteria);
			if($old_model){
				$oldnGorder = json_decode($old_model->ordering, true);
				$oldnGorder[$nameGenQId] = $nGorder[$nameGenQId];
				$old_model->ordering = json_encode($oldnGorder);
				$old_model->save();
			}
			$nGorder[$nameGenQId]--;
			$model->ordering = json_encode($nGorder);
			$model->save();
		}
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'active' => 'Active',
			'ordering' => 'Ordering',
			'name' => 'Name',
			'interviewId' => 'Interview',
            'nameGenQIds' => 'nameGenQIds',
		);
	}

	public function getIsRepeat()
	{
		if(strstr($this->interviewId, ","))
			return "#";
		return "";
	}
	public static function getName($id){
		$model = Alters::model()->findByPk($id);
		if($model)
			return $model->name;
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
		$criteria->compare('active',$this->active);
		$criteria->compare('ordering',$this->ordering);
		$criteria->compare('name',$this->name);
		$criteria->compare('interviewId',$this->interviewId);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Decrypts "name" attribute after it's found.
     */
    protected function afterFind() {
        $this->name = decrypt( $this->name );
        return parent::afterFind();
    }

    /**
     * Encrypts "name" attribute before it's saved.
     * @return bool|void
     */
    protected function beforeSave() {
        $this->name = encrypt( $this->name );
        return parent::beforeSave();
    }
}
