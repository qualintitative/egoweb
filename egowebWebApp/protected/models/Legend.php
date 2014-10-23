<?php

/**
 * This is the model class for table "legend".
 *
 * The followings are the available columns in table 'legend':
 * @property integer $id
 * @property integer $studyId
 * @property integer $questionId
 * @property string $shape
 * @property string $label
 * @property string $color
 * @property integer $size
 * @property integer $ordering
 */
class Legend extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Legend the static model class
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
		return 'legend';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('studyId, questionId, shape', 'required'),
			array('studyId, questionId, size, ordering', 'numerical', 'integerOnly'=>true),
			array('shape, label, color', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, studyId, questionId, shape, label, color, size, ordering', 'safe', 'on'=>'search'),
		);
	}

	public function sortOrder($ordering, $questionId)
	{
		$criteria = new CDbCriteria();
		$criteria=array(
			'condition'=>"questionId = " . $questionId . " AND ordering > ".$ordering ,
			'order'=>'ordering',
		);
		$legends = Legend::model()->findAll($criteria);
		foreach($legends as $legend){
			Legend::moveUp($legend->id);
		}
	}

	public function moveUp($id)
	{
		$model = Legend::model()->findByPk($id);
		if($model && $model->ordering > 0){
			$old_model = Legend::model()->findByAttributes(array('questionId'=>$model->questionId,'ordering'=>$model->ordering-1));
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
			'questionId' => 'Question',
			'shape' => 'Shape',
			'label' => 'Label',
			'color' => 'Color',
			'size' => 'Size',
			'ordering' => 'Ordering',
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
		$criteria->compare('questionId',$this->questionId);
		$criteria->compare('shape',$this->shape,true);
		$criteria->compare('label',$this->label,true);
		$criteria->compare('color',$this->color,true);
		$criteria->compare('size',$this->size);
		$criteria->compare('ordering',$this->ordering);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}