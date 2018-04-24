<?php

/**
 * This is the model class for table "alterPrompt".
 *
 * The followings are the available columns in table 'alterPrompt':
 * @property integer $id
 * @property integer $studyId
 * @property integer $afterAltersEntered
 * @property string $display
 */
class AlterPrompt extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AlterPrompt the static model class
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
		return 'alterPrompt';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('studyId, afterAltersEntered, display', 'required'),
			array('studyId, questionId', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, studyId, afterAltersEntered, display', 'safe', 'on'=>'search'),
		);
	}

	public static function getPrompt($studyId, $alters){
        $criteria = array(
            "condition"=>"studyId = $studyId AND afterAltersEntered <= $alters",
            "order"=>"afterAltersEntered DESC",
        );
        $alterPrompt = AlterPrompt::model()->find($criteria);
        if($alterPrompt)
    	    return $alterPrompt->display;
        else
            return false;
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
			'afterAltersEntered' => 'After Alters Entered',
			'display' => 'Display',
            'questionId' => 'Question',
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
		$criteria->compare('afterAltersEntered',$this->afterAltersEntered);
		$criteria->compare('display',$this->display,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
