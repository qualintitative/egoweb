<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "expression".
 *
 * @property int $id
 * @property int|null $active
 * @property string|null $name
 * @property string|null $type
 * @property string|null $operator
 * @property string|null $value
 * @property int|null $resultForUnanswered
 * @property int|null $studyId
 * @property int|null $questionId
 */
class Expression extends \yii\db\ActiveRecord
{
    public $answers = array();
	public $study = null;
	public $question = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'expression';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'resultForUnanswered', 'studyId', 'questionId'], 'integer'],
            [['name', 'type', 'operator', 'value'], 'string'],
        ];
    }

    /**
	 * CORE FUNCTION
	 * Show logic for the expressions. determines whether or not to display a question
	 * returns either true/false $interviewIdor a number for the Counting expressions
	 */
	public function evalExpression($interviewId, $alterId1 = null, $alterId2 = null, $answers = null)
	{
    	if(!$interviewId)
    	    return false;

		if(!isset($this->id))
			return false;


		if(isset($this->study))
			$study = $this->study;
		else
			$study = Study::findOne($this->studyId);

		if(is_numeric($this->questionId)){
    		if(!$this->question)
    		    $this->question = Question::findOne($this->questionId);
		}

		$comparers = array(
			'Greater'=>'>',
			'GreaterOrEqual'=>'>=',
			'Equals'=>'==',
			'LessOrEqual'=>'<=',
			'Less'=>'<'
		);

		if(is_numeric($this->questionId)){
			if($this->question->subjectType == 'ALTER_PAIR'){
				$array_id = $this->questionId . '-' .  $alterId1 . "and" . $alterId2;

			}else if($this->question->subjectType == 'ALTER'){
				$array_id = $this->questionId . '-' .  $alterId1;
			}else{
    			$array_id = $this->questionId;
			}
			if(isset($answers[$array_id]))
				$answer = $answers[$array_id]->value;
			else
				$answer = "";
		}

		if($this->type == "Text"){
			if(!$answer)
				return $this->resultForUnanswered;
			if($this->operator == "Contains"){
				if(strstr($answer, $this->value))
					return true;
			}else if($this->operator == "Equals"){
				if($answer == $this->value)
					return true;
			}
		}else if($this->type == "Number"){
			if(!$answer || !is_numeric($answer))
				return $this->resultForUnanswered;
			$logic = "return " . $answer . " " . $comparers[$this->operator] . " " . $this->value . ";";
			return eval($logic);
		}else if($this->type == "Selection"){
			if(!$answer)
				return $this->resultForUnanswered;
			$selectedOptions = explode(',', $answer);
			$options = explode(',', $this->value);
			$trues = 0;
			foreach($selectedOptions as $selectedOption){

				if(!$selectedOption)
					continue;
				if($this->operator == "Some" && in_array($selectedOption, $options))
					return true;


				if($this->operator == "None" && in_array($selectedOption, $options))
					return false;
				if(in_array($selectedOption, $options))
					$trues++;
			}
			if($this->operator == "None" || ($this->operator == "All" && $trues >= count($options)))
				return true;
		}else if($this->type == "Counting"){
			list($times, $expressionIds, $questionIds) = preg_split('/:/', $this->value);
			$count = 0;
			if($expressionIds != ""){
				$expressionIds = explode(',', $expressionIds);
				foreach($expressionIds as $expressionId){
					$count = $count + Expression::countExpression($expressionId, $interviewId, $alterId1, $alterId2, $answers);
				}
			}
			if($questionIds != ""){
				$questionIds = explode(',', $questionIds);
				foreach($questionIds as $questionId){
					$count = $count + Expression::countQuestion($questionId, $interviewId, $this->operator, $alterId1, $alterId2, $answers);
				}
			}
			return ($times * $count);
		} else if($this->type == "Comparison"){
			list($value, $expressionId) =  preg_split('/:/', $this->value);
			$newE = Expression::findOne($expressionId);
			$result = $newE->evalExpression($interviewId, $alterId1, $alterId2, $answers);
			$logic = "return " . $result . " " . $comparers[$this->operator] . " " . $value . ";";
			return eval($logic);
		} else if($this->type == "Compound"){
			$subExpressions = explode(',', $this->value);
			$trues[$this->id] = 0;
			foreach($subExpressions as $subId){
				// prevent infinite loops!
				$isTrue[$subId] = false;
				if(!$subId || $subId == $this->id)
					continue;
				$subE[$subId] = Expression::findOne($subId);
				if(!$subE[$subId])
				    return false;
				$isTrue[$subId] = $subE[$subId]->evalExpression($interviewId, $alterId1, $alterId2, $answers);

				if($this->operator == "Some" && $isTrue[$subId])
					return true;
				if($isTrue[$subId])
					$trues[$this->id]++;
			}
			if($this->operator == "None" && $trues[$this->id] == 0)
				return true;
			else if ($this->operator == "All" && $trues[$this->id] == count($subExpressions))
				return true;
		}
		return false;
	}

	public static function countExpression($id, $interviewId, $alterId1, $alterId2, $answers)
	{
		$countE = Expression::findOne($id);
		return $countE->evalExpression($interviewId, $alterId1, $alterId2, $answers);
	}

	public static function countQuestion($questionId, $interviewId, $operator, $alterId1 = null, $alterId2 = null, $answers)
	{
        $question = Question::findOne($questionId);
		if($question->subjectType == 'ALTER_PAIR'){
			$array_id = $question->id . '-' .  $alterId1 . "and" . $alterId2;

		}else if($question->subjectType == 'ALTER'){
			$array_id = $question->id . '-' .  $alterId1;
		}else{
			$array_id = $question->id;
		}
		if(isset($answers[$array_id]))
			$answer = $answers[$array_id]->value;
		else
			$answer = "";

		if(!$answer || !is_numeric($answer)){
			return 0;
		}else{
			if($operator == "Sum")
				return $answer;
			else
				return 1;
		}
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
            'type' => 'Type',
            'operator' => 'Operator',
            'value' => 'Value',
            'resultForUnanswered' => 'Result For Unanswered',
            'studyId' => 'Study ID',
            'questionId' => 'Question ID',
        ];
    }
}
