<?php

/**
 * This is the model class for table "expression".
 *
 * The followings are the available columns in table 'expression':
 * @property integer $id
 * @property integer $random_key
 * @property integer $active
 * @property integer $name
 * @property integer $type
 * @property integer $operator
 * @property integer $valueText
 * @property integer $value
 * @property integer $resultForUnanswered
 * @property integer $studyId
 * @property integer $questionId
 */
class Expression extends CActiveRecord
{

	public $answers = array();
	public $study = null;
	public $question = null;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Expression the static model class
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
		return 'expression';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, active, name, type, operator, value, resultForUnanswered, studyId, questionId', 'length', 'max'=>255),
			array('id, active, studyId', 'numerical', 'integerOnly'=>true),
                        array('name', 'required','on'=>'insert'),
			array('name', 'filter', 'filter'=>function($param) {return CHtml::encode(strip_tags($param));}),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, name, type, operator, value, resultForUnanswered, studyId, questionId', 'safe', 'on'=>'search'),
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
			'name' => 'Name',
			'type' => 'Expression Type',
			'operator' => 'Operator',
			'value' => 'Value',
			'resultForUnanswered' => 'Result For Unanswered',
			'studyId' => 'Study',
			'questionId' => 'Question ID',
		);
	}

	/**
	 * FUNCTION
	 * fetches all the answers for an alter / alter pair question
	 *
	 */
    public function fetchAlterAnswers($questionId, $interviewId, $multi = false)
    {
        #OK FOR SQL INJECTION
        $params = new stdClass();
        $params->name = ':interviewId';
        $params->value = $interviewId;
        $params->dataType = PDO::PARAM_INT;

        $alters = q("SELECT * FROM alters WHERE interviewId = :interviewId ",array($params))->queryAll();

        #OK FOR SQL INJECTION
        $params2 = new stdClass();
        $params2->name = ':questionId';
        $params2->value = $questionId;
        $params2->dataType = PDO::PARAM_INT;

        $answers = q("SELECT * FROM answer WHERE questionId = :questionId and interviewId = :interviewId",array($params2, $params))->queryAll();
        foreach ($answers as $answer){
            if($answer['questionType'] == "ALTER" && strlen($answer['value']) >= 8){
                $array_id = $answer['questionId'] . '-' . $answer['alterId1'];
                $this->answers[$array_id] = decrypt($answer['value']);
            }else if($answer['questionType'] == "ALTER_PAIR" && strlen($answer['value']) >= 8){
                $array_id = $answer['questionId'] . '-' . $answer['alterId1'] . 'and' . $answer['alterId2'] ;
                $this->answers[$array_id] = decrypt($answer['value']);
            }
        }
    }

	/**
	 * CORE FUNCTION
	 * Show logic for the expressions. determines whether or not to display a question
	 * returns either true/false or a number for the Counting expressions
	 */
	public function evalExpression($id, $interviewId, $alterId1 = null, $alterId2 = null, $answers = null)
	{
		if(isset($this->id))
			$expression = $this;
		else
			$expression = Expression::model()->findByPk($id);

		if(!$expression)
			return true;

		if(isset($this->study))
			$study = $this->study;
		else
			$study = Study::model()->findByPk($expression->studyId);

		if(isset($study->multiSessionEgoId) && $study->multiSessionEgoId){
			if(!stristr($interviewId, ",")){
                #OK FOR SQL INJECTION
				$egoValue = q("SELECT value FROM answer WHERE interviewId = " . $interviewId . " AND questionID = " . $study->multiSessionEgoId)->queryScalar();
                #OK FOR SQL INJECTION
                $multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
                #OK FOR SQL INJECTION
				$interviewIds = q("SELECT interviewId FROM answer WHERE questionId in (" . implode(",", $multiIds) . ") AND value = '" .$egoValue . "'" )->queryColumn();
				$interviewId = implode(",", $interviewIds);
			}
		}
		if(is_numeric($expression->questionId)){
    		if(!$expression->question)
    		    $expression->question = Question::model()->findByPk($expression->questionId);
			$subjectType = $this->question->subjectType;
			$questionId = $this->question->id;
		}else{
			$questionId = "";
			$subjectType = "";
		}

		$comparers = array(
			'Greater'=>'>',
			'GreaterOrEqual'=>'>=',
			'Equals'=>'==',
			'LessOrEqual'=>'<=',
			'Less'=>'<'
		);

		if(is_numeric($questionId)){
			if($subjectType == 'ALTER_PAIR'){
				/*
				if(!$this->answers){
					if(strstr($interviewId, ",")){
						foreach(explode(",", $interviewId) as $id){
                            #OK FOR SQL INJECTION
							$studyId = q("SELECT studyId FROM interview WHERE id = $id")->queryScalar();
                            #OK FOR SQL INJECTION
                            if(q("SELECT id FROM question WHERE id = $questionId and studyId = $studyId")->queryScalar())
								$this->fetchAlterAnswers($questionId, $id);
						}
					}else{
						$this->fetchAlterAnswers($questionId, $interviewId);
					}
				}*/
				$array_id = $questionId . '-' .  $alterId1 . "and" . $alterId2;
				if(isset($answers[$array_id]))
					$answer = $answers[$array_id]->value;
				else
					$answer = "";
			}else if($subjectType == 'ALTER'){
				/*
				if(!$this->answers){
					if(strstr($interviewId, ",")){
						foreach(explode(",", $interviewId) as $id){
                            #OK FOR SQL INJECTION
							$studyId = q("SELECT studyId FROM interview WHERE id = $id")->queryScalar();
                            #OK FOR SQL INJECTION
                            if(q("SELECT id FROM question WHERE id = $questionId and studyId = $studyId")->queryScalar())
								$this->fetchAlterAnswers($questionId, $id);
						}
					}else{
						$this->fetchAlterAnswers($questionId, $interviewId);
					}
				}*/
				$array_id = $questionId . '-' .  $alterId1;
				if(isset($answers[$array_id]))
					$answer = $answers[$array_id]->value;
				else
					$answer = "";
			}else{
				$answer = $answers[$questionId]->value;
                #OK FOR SQL INJECTION
				//$answer = decrypt(q("SELECT value FROM answer WHERE questionId = $questionId AND interviewId in ($interviewId)")->queryScalar());
			}
		}

		if($expression->type == "Text"){
			if(!$answer)
				return $expression->resultForUnanswered;
			if($expression->operator == "Contains"){
				if(strstr($answer, $expression->value))
					return true;
			}else if($expression->operator == "Equals"){
				if($answer == $expression->value)
					return true;
			}
		}else if($expression->type == "Number"){
			if(!$answer || !is_numeric($answer))
				return $expression->resultForUnanswered;
			$logic = "return " . $answer . " " . $comparers[$expression->operator] . " " . $expression->value . ";";
			return eval($logic);
		}else if($expression->type == "Selection"){
			if(!$answer)
				return $expression->resultForUnanswered;
			$selectedOptions = explode(',', $answer);
			$options = explode(',', $expression->value);
			$trues = 0;
			foreach($selectedOptions as $selectedOption){
				if(!$selectedOption)
					continue;
				if($expression->operator == "Some" && in_array($selectedOption, $options))
					return true;
				if($expression->operator == "None" && in_array($selectedOption, $options))
					return false;
				if(in_array($selectedOption, $options))
					$trues++;
			}
			if($expression->operator == "None" || ($expression->operator == "All" && $trues >= count($options)))
				return true;
		}else if($expression->type == "Counting"){
			list($times, $expressionIds, $questionIds) = preg_split('/:/', $expression->value);
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
					$count = $count + Expression::countQuestion($questionId, $interviewId, $expression->operator);
				}
			}
			return ($times * $count);
		} else if($expression->type == "Comparison"){
			list($value, $expressionId) =  preg_split('/:/', $expression->value);
			$newE = new Expression;
			$result = $newE->evalExpression($expressionId, $interviewId, $alterId1, $alterId2, $answers);
			$logic = "return " . $result . " " . $comparers[$expression->operator] . " " . $value . ";";
			return eval($logic);
		} else if($expression->type == "Compound"){
			$subExpressions = explode(',', $expression->value);
			$trues[$id] = 0;
			foreach($subExpressions as $subExpression){
				// prevent infinite loops!
				$isTrue[$subExpression] = false;
				if($subExpression == $id)
					continue;
				$sub[$subExpression] = new Expression;
				$isTrue[$subExpression] = $sub[$subExpression]->evalExpression($subExpression, $interviewId, $alterId1, $alterId2,$answers);
				if($expression->operator == "Some" && $isTrue[$subExpression])
					return true;
				if($isTrue[$subExpression])
					$trues[$id]++;
			}
			if($expression->operator == "None" && $trues[$id] == 0)
				return true;
			else if ($expression->operator == "All" && $trues[$id] == count($subExpressions))
				return true;
		}
		return false;
	}

	public static function countExpression($id, $interviewId, $alterId1, $alterId2, $answers)
	{
		$countE = new Expression;
		if($countE->evalExpression($id, $interviewId, $alterId1, $alterId2, $answers))
			return 1;
		else
			return 0;
	}

	public static function countQuestion($questionId, $interviewId, $operator, $alterId1 = null, $alterId2 = null)
	{
		$alter = ""; $alter2 = "";
		if($alterId1 != null)
			$alter = " AND alterId1 = " . $alterId1;
		if($alterId2 != null)
			$alter2 = " AND alterId2 = " . $alterId2;
        #OK FOR SQL INJECTION
		$answer = q("SELECT value FROM answer WHERE questionId = " . $questionId . " AND interviewId = " . $interviewId . $alter . $alter2)->queryScalar();
		if(!$answer || !is_numeric($answer)){
			return 0;
		}else{
			if($operator == "Sum")
				return $answer;
			else
				return 1;
		}
	}

	public function beforeDelete(){
        #OK FOR SQL INJECTION
		$others = q("SELECT * FROM expression WHERE studyId = " . $this->studyId . " AND (type = 'Counting' OR type = 'Comparison' OR type = 'Compound')")->queryAll();
		foreach($others as $expression){
			$expressionIds = "";
			if($expression['type'] == "Counting"){
				list($times, $expressionIds, $questionIds) = preg_split('/:/', $expression['value']);
				$expressionIds = explode(',', $expressionIds);
				$index = array_search($this->id,$expressionIds);
				if($index){
					array_splice($expressionIds,$index,1);
					$expressionIds = implode(",", $expressionIds);
					$data = array(
						"value"=>$times . ":" . $expressionIds . ":" . $questionIds
					);
					u('expression', $data, "id = " . $expression['id']);
				}
			}else if($expression['type'] == "Comparison"){
				list($value, $expressionId) =  preg_split('/:/', $expression['value']);
				$expressionIds = explode(',', $expressionIds);
				$index = array_search($this->id,$expressionIds);
				if($index){
					array_splice($expressionIds,$index,1);
					$expressionIds = implode(",", $expressionIds);
					$data = array(
						"value"=>$value . ":" . $expressionIds
					);
					u('expression', $data, "id = " . $expression['id']);
				}
			}else if($expression['type'] == "Compound"){
				$expressionIds = explode(',', $expression['value']);
				$index = array_search($this->id,$expressionIds);
				if($index){
					array_splice($expressionIds,$index,1);
					$expressionIds = implode(",", $expressionIds);
					$data = array(
						"value"=>$expressionIds
					);
					u('expression', $data, "id = " . $expression['id']);
				}
			}
		}
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
		$criteria->compare('name',$this->name);
		$criteria->compare('type',$this->type);
		$criteria->compare('operator',$this->operator);
		$criteria->compare('value',$this->value);
		$criteria->compare('resultForUnanswered',$this->resultForUnanswered);
		$criteria->compare('studyId',$this->studyId);
		$criteria->compare('questionId',$this->questionId);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
