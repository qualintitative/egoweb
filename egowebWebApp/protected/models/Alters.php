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
			array('ordering, name, interviewId', 'required'),
			array('ordering', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, ordering, name, interviewId', 'length', 'max'=>1024),
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

	public function sortOrder($ordering, $interviewId)
	{
		$criteria = new CDbCriteria();
		$criteria=array(
			'condition'=>"interviewId in (" . $interviewId . ") AND ordering > ".$ordering ,
			'order'=>'ordering',
		);
		$models = Alters::model()->findAll($criteria);
		foreach($models as $model){
			Alters::moveUp($model->id);
		}
	}

	public function moveUp($id)
	{
		$model = Alters::model()->findByPk($id);
		if($model && $model->ordering > 0){
			$old_model = Alters::model()->findByAttributes(array('interviewId'=>$model->interviewId,'ordering'=>$model->ordering-1));
			if($old_model){
				$old_model->ordering = $model->ordering;
				$old_model->save();
			}
			$model->ordering--;
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
		);
	}

	public function getIsRepeat()
	{
		if(strstr($this->interviewId, ","))
			return "#";
		return "";
	}
	public function getName($id){
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
}