<?php

/**
 * This is the model class for table "answer".
 *
 * The followings are the available columns in table 'answer':
 * @property integer $id
 * @property integer $random_key
 * @property integer $active
 * @property integer $questionId
 * @property integer $interviewId
 * @property integer $alterId1
 * @property integer $alterId2
 * @property string $valueText
 * @property string $value
 * @property string $otherSpecifyText
 * @property integer $skipReason
 * @property integer $studyId
 * @property integer $questionType
 * @property integer $answerType
 */
class Answer extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Answer the static model class
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
		return 'answer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('questionId, interviewId, studyId, questionType, answerType', 'required'),
			array('active, questionId, interviewId, alterId1, alterId2, value, otherSpecifyText, skipReason, studyId, questionType, answerType', 'length', 'max'=>4096),
			array('value, otherSpecifyText', 'filter', 'filter'=>function($param) {return CHtml::encode(strip_tags($param));}),
			array('active, questionId, interviewId, studyId', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, questionId, interviewId, alterId1, alterId2, value, otherSpecifyText, skipReason, studyId, questionType, answerType', 'safe', 'on'=>'search'),
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

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'active' => 'Active',
			'questionId' => 'Question',
			'interviewId' => 'Interview',
			'alterId1' => 'Alter Id1',
			'alterId2' => 'Alter Id2',
			'value' => 'Value',
			'otherSpecifyText' => 'Other Specify Text',
			'skipReason' => 'Skip Reason',
			'studyId' => 'Study',
			'questionType' => 'Question Type',
			'answerType' => 'Answer Type',
		);
	}

	/**
	 * Encrypts "value" and "otherSpecifyText" attributes before they're saved.
	 * @return bool|void
	 */
	public function beforeSave() {
		if($this->value != "")
			$this->value = encrypt( $this->value );
		if($this->otherSpecifyText != "")
			$this->otherSpecifyText = encrypt( $this->otherSpecifyText );

		return parent::beforeSave();
	}

	/**
	 * Decrypts "value" and "otherSpecifyText" attributes after they're found.
	 */
	protected function afterFind() {
		if(strlen($this->value) >= 8)
			$this->value = decrypt( $this->value );
		if(strlen($this->otherSpecifyText) >= 8)
			$this->otherSpecifyText = decrypt ($this->otherSpecifyText );

		return parent::afterFind();
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
		$criteria->compare('questionId',$this->questionId);
		$criteria->compare('interviewId',$this->interviewId);
		$criteria->compare('alterId1',$this->alterId1);
		$criteria->compare('alterId2',$this->alterId2);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('otherSpecifyText',$this->otherSpecifyText,true);
		$criteria->compare('skipReason',$this->skipReason);
		$criteria->compare('studyId',$this->studyId);
		$criteria->compare('questionType',$this->questionType);
		$criteria->compare('answerType',$this->answerType);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
