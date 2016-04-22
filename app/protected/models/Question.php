<?php

/**
 * This is the model class for table "question".
 *
 * The followings are the available columns in table 'question':
 * @property integer $id
 * @property integer $active
 * @property string $title
 * @property string $prompt
 * @property string $preface
 * @property string $citation
 * @property integer $subjectType
 * @property integer $answerType
 * @property integer $askingStyleList
 * @property integer $ordering
 * @property integer $otherSpecify
 * @property integer $noneButton
 * @property integer $allButton
 * @property integer $pageLevelDontKnowButton
 * @property integer $pageLevelRefuseButton
 * @property integer $dontKnowButton
 * @property integer $refuseButton
 * @property integer $allOptionString
 * @property integer $uselfExpression
 * @property integer $minLimitType
 * @property integer $minLiteral
 * @property integer $minPrevQues
 * @property integer $maxLimitType
 * @property integer $maxLiteral
 * @property integer $maxPrevQues
 * @property integer $minCheckableBoxes
 * @property integer $maxCheckableBoxes
 * @property integer $withListRange
 * @property integer $listRangeString
 * @property integer $minListRange
 * @property integer $maxListRange
 * @property integer $timeUnits
 * @property integer $symmetric
 * @property integer $keepOnSamePage
 * @property integer $studyId
 * @property integer $answerReasonExpressionId
 * @property integer $networkRelationshipExprId
 * @property integer $networkParams
 * @property integer $networkNColorQId
 * @property integer $networkNSizeQId
 * @property integer $networkEColorQId
 * @property integer $networkESizeQId
 * @property text $useAlterListField
 */
class Question extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Question the static model class
	 */
	public $alterId1;
	public $alterId2;

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'question';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, active, ordering, studyId', 'numerical', 'integerOnly'=>true),
			array('title', 'filter', 'filter'=>function($param) {return CHtml::encode(strip_tags($param));}),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('minCheckableBoxes, maxCheckableBoxes','default',
				'value'=>1,
				'setOnEmpty'=>true,'on'=>'insert'
			),
            array('prompt, preface, citation, javascript', 'length', 'max'=>4294967295),
			array('id, active, title, subjectType, answerType, askingStyleList, ordering, otherSpecify, noneButton, allButton, pageLevelDontKnowButton, pageLevelRefuseButton, dontKnowButton, refuseButton, allOptionString, uselfExpression, minLimitType, minLiteral, minPrevQues, maxLimitType, maxLiteral, maxPrevQues, minCheckableBoxes, maxCheckableBoxes, withListRange, listRangeString, minListRange, maxListRange, timeUnits, symmetric, keepOnSamePage, studyId, answerReasonExpressionId, networkRelationshipExprId, networkParams, networkNColorQId, networkNSizeQId, networkEColorQId, networkESizeQId, useAlterListField', 'length', 'max'=>4096),
			array('id, active, title, prompt, preface, citation, subjectType, answerType, askingStyleList, ordering, otherSpecify, noneButton, allButton, pageLevelDontKnowButton, pageLevelRefuseButton, dontKnowButton, refuseButton, allOptionString, uselfExpression, minLimitType, minLiteral, minPrevQues, maxLimitType, maxLiteral, maxPrevQues, minCheckableBoxes, maxCheckableBoxes, withListRange, listRangeString, minListRange, maxListRange, timeUnits, symmetric, keepOnSamePage, studyId, answerReasonExpressionId, networkRelationshipExprId, networkParams, networkNColorQId, networkNSizeQId, networkEColorQId, networkESizeQId', 'safe', 'on'=>'search'),
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
            'question'=>array(self::BELONGS_TO, 'Study', 'studyId'),
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
			'title' => 'Title',
			'prompt' => 'Prompt',
			'preface' => 'Preface',
			'citation' => 'Citation',
			'subjectType' => 'Subject Type',
			'answerType' => 'Response Type',
			'askingStyleList' => 'Asking Style List',
			'ordering' => 'Ordering',
			'otherSpecify' => 'Other Specify',
			'noneButton' => 'None Button',
			'allButton' => 'All Button',
			'pageLevelDontKnowButton' => 'Page Level Dont Know Button',
			'pageLevelRefuseButton' => 'Page Level Refuse Button',
			'dontKnowButton' => 'Dont Know Button',
			'refuseButton' => 'Refuse Button',
			'allOptionString' => 'All Option String',
			'uselfExpression' => 'Uself Expression',
			'minLimitType' => 'Min Limit Type',
			'minLiteral' => 'Min Literal',
			'minPrevQues' => 'Min Prev Ques',
			'maxLimitType' => 'Max Limit Type',
			'maxLiteral' => 'Max Literal',
			'maxPrevQues' => 'Max Prev Ques',
			'minCheckableBoxes' => 'Min Checkable Boxes',
			'maxCheckableBoxes' => 'Max Checkable Boxes',
			'withListRange' => 'With List Range',
			'listRangeString' => 'List Range String',
			'minListRange' => 'Min List Range',
			'maxListRange' => 'Max List Range',
			'timeUnits' => 'Time Units',
			'symmetric' => 'symmetric',
			'keepOnSamePage' => 'Keep On Same Page',
			'studyId' => 'Study',
			'answerReasonExpressionId' => 'Answer Reason Expression',
			'networkRelationshipExprId' => 'Network Relationship Expr',
			'networkParams' => 'Network Parameters',
			'networkNColorQId' => 'Network Node Color Q',
			'networkNSizeQId' => 'Network Node Size Q',
			'networkEColorQId' => 'Network Edge Color Q',
			'networkESizeQId' => 'Network Edge Size Q',
			'useAlterListField' => 'Use Participant List Field',

		);
	}

	public static function getTitle($id){
		$question = Question::model()->findByPk($id);
		$study = Study::model()->findByPk($question->studyId);
		return $study->name . ":" . $question->title;
	}

	public static function sortOrder($ordering, $studyId)
	{
		$criteria = new CDbCriteria();
		$criteria=array(
			'condition'=>"studyId = " . $studyId . " AND ordering > ".$ordering ,
			'order'=>'ordering',
		);
		$models = Question::model()->findAll($criteria);
		foreach($models as $model){
			Question::moveUp($model->id);
		}
	}

	public static function moveUp($questionId)
	{
		$model = Question::model()->findByPk($questionId);
		if($model && $model->ordering > 0){
			$old_model = Question::model()->findByAttributes(array('studyId'=>$model->studyId, 'subjectType'=>$model->subjectType, 'ordering'=>$model->ordering-1));
			if($old_model){
				$old_model->ordering = $model->ordering;
				$old_model->save();
			}
			$model->ordering--;
			$model->save();
		}
	}

	public static function timeBits($timeUnits)
	{
		$timeArray = array();
		$bitVals = array(
			'BIT_YEAR' 	=> 1,
			'BIT_MONTH' => 2,
			'BIT_WEEK' 	=> 4,
			'BIT_DAY' 	=> 8,
			'BIT_HOUR' 	=> 16,
			'BIT_MINUTE'  => 32,
		);
		foreach ($bitVals as $key=>$value){
			if($timeUnits & $value){
				$timeArray[] = $key;
			}
		}
		return $timeArray;
	}


	public function beforeSave(){
		if(trim($this->preface) == "<br>" || $this->preface == " ")
			$this->preface = "";
		if(trim($this->citation) == "<br>" ||  $this->citation == " ")
			$this->citation = "";
		return true;
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
		$criteria->compare('title',$this->title,true);
		$criteria->compare('prompt',$this->prompt,true);
		$criteria->compare('preface',$this->preface,true);
		$criteria->compare('citation',$this->citation,true);
		$criteria->compare('subjectType',$this->subjectType);
		$criteria->compare('answerType',$this->answerType);
		$criteria->compare('askingStyleList',$this->askingStyleList);
		$criteria->compare('ordering',$this->ordering);
		$criteria->compare('otherSpecify',$this->otherSpecify);
		$criteria->compare('noneButton',$this->noneButton);
		$criteria->compare('allButton',$this->allButton);
		$criteria->compare('pageLevelDontKnowButton',$this->pageLevelDontKnowButton);
		$criteria->compare('pageLevelRefuseButton',$this->pageLevelRefuseButton);
		$criteria->compare('dontKnowButton',$this->dontKnowButton);
		$criteria->compare('refuseButton',$this->refuseButton);
		$criteria->compare('allOptionString',$this->allOptionString);
		$criteria->compare('uselfExpression',$this->uselfExpression);
		$criteria->compare('minLimitType',$this->minLimitType);
		$criteria->compare('minLiteral',$this->minLiteral);
		$criteria->compare('minPrevQues',$this->minPrevQues);
		$criteria->compare('maxLimitType',$this->maxLimitType);
		$criteria->compare('maxLiteral',$this->maxLiteral);
		$criteria->compare('maxPrevQues',$this->maxPrevQues);
		$criteria->compare('minCheckableBoxes',$this->minCheckableBoxes);
		$criteria->compare('maxCheckableBoxes',$this->maxCheckableBoxes);
		$criteria->compare('withListRange',$this->withListRange);
		$criteria->compare('listRangeString',$this->listRangeString);
		$criteria->compare('minListRange',$this->minListRange);
		$criteria->compare('maxListRange',$this->maxListRange);
		$criteria->compare('timeUnits',$this->timeUnits);
		$criteria->compare('symmetric',$this->symmetric);
		$criteria->compare('keepOnSamePage',$this->keepOnSamePage);
		$criteria->compare('studyId',$this->studyId);
		$criteria->compare('answerReasonExpressionId',$this->answerReasonExpressionId);
		$criteria->compare('networkRelationshipExprId',$this->networkRelationshipExprId);
		$criteria->compare('networkParams',$this->networkParams);
		$criteria->compare('networkNColorQId',$this->networkNColorQId);
		$criteria->compare('networkNSizeQId',$this->networkNSizeQId);
		$criteria->compare('networkEColorQId',$this->networkEColorQId);
		$criteria->compare('networkESizeQId',$this->networkESizeQId);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
