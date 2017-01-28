<?php

/**
 * This is the model class for table "matchedAlters".
 *
 * The followings are the available columns in table 'matchedAlters':
 * @property integer $id
 * @property integer $studyId
 * @property integer $alterId1
 * @property integer $alterId2
 * @property string $matchedName
 */
class MatchedAlters extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'matchedAlters';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('matchedName', 'required'),
			array('studyId, alterId1, alterId2, interviewId1, interviewId2', 'numerical', 'integerOnly'=>true),
			array('matchedName', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, studyId, alterId1, alterId2, matchedName', 'safe', 'on'=>'search'),
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
			'studyId' => 'Study',
			'alterId1' => 'Alter Id1',
			'alterId2' => 'Alter Id2',
			'matchedName' => 'Matched Name',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('studyId',$this->studyId);
		$criteria->compare('alterId1',$this->alterId1);
		$criteria->compare('alterId2',$this->alterId2);
		$criteria->compare('matchedName',$this->matchedName,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return MatchedAlters the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
