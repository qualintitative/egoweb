<?php

/**
 * This is the model class for table "questionOption".
 *
 * The followings are the available columns in table 'questionOption':
 * @property integer $id
 * @property integer $random_key
 * @property integer $active
 * @property integer $studyId
 * @property integer $questionId
 * @property string $name
 * @property string $value
 * @property integer $ordering
 */
class QuestionOption extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return QuestionOption the static model class
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
		return 'questionOption';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('studyId, questionId, name, value, ordering', 'length', 'max'=>255),
			array('id, studyId, questionId, ordering', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, studyId, questionId, name, value, ordering', 'safe', 'on'=>'search'),
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

	public function sortOrder($ordering, $questionId)
	{
		$criteria = new CDbCriteria();
		$criteria=array(
			'condition'=>"questionId = " . $questionId . " AND ordering > ".$ordering ,
			'order'=>'ordering',
		);
		$options = QuestionOption::model()->findAll($criteria);
		foreach($options as $option){
			QuestionOption::moveUp($option->id);
		}
	}

	public function moveUp($optionId)
	{
		$model = QuestionOption::model()->findByPk($optionId);
		if($model && $model->ordering > 0){
			$old_model = QuestionOption::model()->findByAttributes(array('questionId'=>$model->questionId,'ordering'=>$model->ordering-1));
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
			'studyId' => 'Study',
			'questionId' => 'Question',
			'name' => 'Name',
			'value' => 'Value',
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
		$criteria->compare('active',$this->active);
		$criteria->compare('studyId',$this->studyId);
		$criteria->compare('questionId',$this->questionId);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('ordering',$this->ordering);

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
