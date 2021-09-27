<?php

namespace app\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "study".
 *
 * @property int $id
 * @property int $active
 * @property string $name
 * @property string|null $introduction
 * @property string|null $egoIdPrompt
 * @property string|null $alterPrompt
 * @property string|null $conclusion
 * @property int $minAlters
 * @property int $maxAlters
 * @property int|null $valueRefusal
 * @property int|null $valueDontKnow
 * @property int|null $valueLogicalSkip
 * @property int $valueNotYetAnswered
 * @property string|null $modified
 * @property int $multiSessionEgoId
 * @property int $useAsAlters
 * @property int $restrictAlters
 * @property int $fillAlterList
 * @property int|null $created_date
 * @property int|null $closed_date
 * @property int|null $status
 * @property int $userId
 * @property int $hideEgoIdPage
 * @property string|null $style
 * @property string|null $javascript
 * @property string|null $footer
 * @property string|null $header
 */
class Study extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'study';
    }

    private $_multiIdQs;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'minAlters', 'maxAlters', 'valueRefusal', 'valueDontKnow', 'valueLogicalSkip', 'valueNotYetAnswered', 'multiSessionEgoId', 'created_date', 'closed_date', 'status', 'userId'], 'integer'],
            [['name'], 'required'],
            [['name', 'introduction', 'egoIdPrompt', 'alterPrompt', 'conclusion', 'style', 'javascript', 'footer', 'header'], 'string'],
            [['modified'], 'safe'],
            [['hideEgoIdPage','fillAlterList','restrictAlters','useAsAlters'], 'boolean'],
            [['multiSessionEgoId', 'useAsAlters', 'restrictAlters', 'fillAlterList', 'hideEgoIdPage'], 'default', 'value' => 0],
            ['modified','default', 'value'=>time()],
            ['userId','default',
                'value'=>Yii::$app->user->identity->id
            ],
            ['conclusion','default',
                'value'=>"Thank you!",
            ],
        ];
    
    }

    public function multiIdQs()
    {
        if($this->multiSessionEgoId == 0)
            return false;
        $egoIdQ = Question::findOne($this->multiSessionEgoId);
        $multiIdQs = array();
        $studies = Study::find()
        ->where(['<>', 'multiSessionEgoId', '0'])
        ->all();
        foreach($studies as $study){
            $newEgoIdQ = Question::findOne($study->multiSessionEgoId);
            if($newEgoIdQ && $newEgoIdQ->title == $egoIdQ->title)
                $multiIdQs[] = $newEgoIdQ;
        }

        return $multiIdQs;
    }

    public function questionTitles()
    {

        if ($this->multiSessionEgoId){
            $questions = Question::find()
            ->where(new \yii\db\Expression("title = (SELECT title FROM question WHERE id = " . $this->multiSessionEgoId . ")"))
            ->all();
            $multiIds = array();
            foreach($questions as $question){
                $multiIds[] = $question->studyId;
            }
        }else{
            $multiIds = $this->id;
        }
        $studies = Study::findAll(array('id'=>$multiIds));
        foreach($studies as $study){
            $studyNames[$study->id] = $study->name;
        }
        $questions = Question::findAll(array('studyId'=>$multiIds));
        $questionTitles = array();
        foreach ($questions as $question)
        {
            $questionTitles[$studyNames[$question->studyId]][$question->title] = $question->id;
        }
        return $questionTitles;
    }

    public function getInterviews()
    {
        return $this->hasMany(Interview::class, ['studyId' => 'id']);
    }

    public static function replicate($study, $questions, $options, $expressions, $alterPrompts = array(), $alterLists = array())
	{
		$newQuestionIds = array();
		$newOptionIds = array();
		$newExpressionIds = array();

		$newStudy = new Study;
		$newStudy->attributes = $study->attributes;
		$newStudy->id = null;

		if(!$newStudy->save())
			throw new BadRequestHttpException("Study: " . print_r($newStudy->errors));
		
		foreach($questions as $question){
			$newQuestion = new Question;
			$newQuestion->attributes = $question->attributes;
			$newQuestion->id = null;
			$newQuestion->studyId = $newStudy->id;
			if(!$newQuestion->save())
				print_r($newQuestion->errors);
				//throw new BadRequestHttpException("Question: " . print_r($newQuestion->errors));
			if($newStudy->multiSessionEgoId == $question->id){
				$newStudy->multiSessionEgoId = $newQuestion->id;
				$newStudy->save();
			}
			$newQuestionIds[$question->id] = $newQuestion->id;
		}
		foreach($questions as $question){
		  $newQuestion = Question::findOne($newQuestionIds[$question->id]);
		  if($newQuestion){
			  if(is_numeric($newQuestion->minPrevQues) && $newQuestion->minPrevQues != 0 && isset($newQuestionIds[$newQuestion->minPrevQues]))
				  $newQuestion->minPrevQues = $newQuestionIds[$newQuestion->minPrevQues];
			  if(is_numeric($newQuestion->maxPrevQues) && $newQuestion->maxPrevQues != 0 && isset($newQuestionIds[$newQuestion->maxPrevQues]))
				  $newQuestion->maxPrevQues = $newQuestionIds[$newQuestion->maxPrevQues];
			  if(is_numeric($newQuestion->networkParams) && $newQuestion->networkParams != 0 && isset($newQuestionIds[$newQuestion->networkParams]))
				  $newQuestion->networkParams = $newQuestionIds[$newQuestion->networkParams];
			  $newQuestion->save();

		  }
		}
		foreach($options as $option){
			$newOption = new QuestionOption;
			$newOption->attributes = $option->attributes;
			$newOption->id = null;
			$newOption->studyId = $newStudy->id;
			if(isset($newQuestionIds[$option->questionId]))
				 $newOption->questionId = $newQuestionIds[$option->questionId];
			if(!$newOption->save())
				throw new BadRequestHttpException("Option: " . print_r($newOption->errors)); //return false;
			else
				$newOptionIds[$option->id] = $newOption->id;
		}

		foreach($expressions as $expression){
			$newExpression = new Expression;
			$newExpression->attributes = $expression->attributes;
			$newExpression->id = null;
			$newExpression->studyId = $newStudy->id;
			if(!$newExpression->name)
				continue;
			if($newExpression->questionId != "" &&  $newExpression->questionId  != 0 && isset($newQuestionIds[$expression->questionId]))
				$newExpression->questionId = $newQuestionIds[$expression->questionId];
			if(!$newExpression->save())
				throw new BadRequestHttpException("Expression: " . print_r($newExpression->errors)); //return false;
			else
				$newExpressionIds[$expression->id] = $newExpression->id;
		}

		foreach($expressions as $expression){
			$oldExpressionId = $expression->id;
			$newExpression = Expression::findOne($newExpressionIds[$expression->id]);

			if(!$newExpression)
				continue;

			$questions = Question::findAll(array('studyId'=>$newStudy->id,'answerReasonExpressionId'=>$oldExpressionId));
			if(count($questions) > 0){
				foreach($questions as $question){
					$question->answerReasonExpressionId = $newExpressionIds[$oldExpressionId];
					$question->save();
				}
			}
			$questions = Question::findAll(array('studyId'=>$newStudy->id,'networkRelationshipExprId'=>$oldExpressionId));
			if(count($questions) > 0){
				foreach($questions as $question){
					$question->networkRelationshipExprId = $newExpressionIds[$oldExpressionId];
					$question->save();
				}
			}

			if($newExpression->type == "Selection"){
				$optionIds = explode(',', $newExpression->value);
				foreach($optionIds as &$optionId){
					if(is_numeric($optionId) && isset($newOptionIds[$optionId]))
						$optionId = $newOptionIds[$optionId];
				}
				$newExpression->value = implode(',', $optionIds);
			} else if($newExpression->type == "Counting"){
				if(!strstr($newExpression->value, ':'))
					continue;
				list($times, $expressionIds, $questionIds) = explode(':', $newExpression->value);
				if($expressionIds != ""){
					$expressionIds = explode(',', $expressionIds);
					foreach($expressionIds as &$expressionId){
						if(isset($newExpressionIds[$expressionId]))
							$expressionId = $newExpressionIds[$expressionId];
					}
					$expressionIds = implode(',',$expressionIds);
				}
				if($questionIds != ""){
					$questionIds = explode(',', $questionIds);
					foreach($questionIds as &$questionId){
						if(isset($newQuestionIds[$questionId]))
						   $questionId = $newQuestionIds[$questionId];
					}
					$questionIds = implode(',', $questionIds);
				}
				$newExpression->value = $times . ":" .  $expressionIds . ":" . $questionIds;
			} else if($newExpression->type == "Comparison"){
				list($value, $expressionId) = explode(':', $newExpression->value);
				$newExpression->value = $value . ":" . $newExpressionIds[$expressionId];
			} else if($newExpression->type == "Compound"){
				$expressionIds = explode(',', $newExpression->value);
				foreach($expressionIds as &$expressionId){
					if(is_numeric($expressionId) && isset($newExpressionIds[$expressionId]))
						$expressionId = $newExpressionIds[$expressionId];
				}
				$newExpression->value = implode(',',$expressionIds);
			} else if ($newExpression->type == "Name Generator"){
				$questionIds = explode(',', $newExpression->value);
				foreach($questionIds as &$questionId){
				if(isset($newQuestionIds[$questionId]))
					$questionId = $newQuestionIds[$questionId];
				}
				$newExpression->value = implode(',', $questionIds);
			}
			$newExpression->save();
		}
		$questions = Question::findAll(array('studyId'=>$newStudy->id, "subjectType"=>"NETWORK"));
		foreach($questions as $question){
			$params = json_decode(htmlspecialchars_decode($question->networkParams), true);
			if ($params) {
				foreach ($params as $k => &$param) {
					if (isset($param['questionId']) && stristr($param['questionId'], "expression")) {
						list($label, $expressionId) = explode("_", $param['questionId']);
						if (isset($newExpressionIds[intval($expressionId)])) {
							$expressionId = $newExpressionIds[intval($expressionId)];
						}
						$param['questionId'] = "expression_" . $expressionId;
					} else {
						if (isset($param['questionId']) && is_numeric($param['questionId']) && isset($newQuestionIds[intval($param['questionId'])])) {
							$param['questionId'] = $newQuestionIds[intval($param['questionId'])];
						}
						if (isset($param['options']) && count($param['options']) > 0) {
							foreach ($param['options'] as &$option) {
								if (isset($newOptionIds[intval($option['id'])])) {
									$option['id'] = $newOptionIds[intval($option['id'])];
								}
							}
						}
					}
				}
			}
			$question->networkParams = json_encode($params);
			$question->save();
		}
		foreach($alterPrompts as $alterPrompt){
			$newAlterPrompt = new AlterPrompt;
			$newAlterPrompt->attributes = $alterPrompt->attributes;
			$newAlterPrompt->id = null;
            $newAlterPrompt->studyId = $newStudy->id;
            if(isset($newQuestionIds[$newAlterPrompt->questionId]))
                $newAlterPrompt->questionId = $newQuestionIds[$newAlterPrompt->questionId];
            if(!$newAlterPrompt->save()){
                ob_start();
                var_dump($newAlterPrompt->errors);
                $errorMsg = ob_get_clean();
                throw new CHttpException(500, "AlterPrompt: " . $errorMsg);
            }
		}

		foreach($alterLists as $alterList){
			$newAlterList = new AlterList;
			$newAlterList->attributes = $alterList->attributes;
			$newAlterList->id = null;
			$newAlterList->studyId = $newStudy->id;
			if(!$newAlterList->save())
				throw new BadRequestHttpException("AlterPrompt: " . print_r($newAlterList->errors));
		}

		$data = array(
			'studyId'=>$newStudy->id,
			'newQuestionIds'=>$newQuestionIds,
			'newOptionIds'=>$newOptionIds,
			'newExpressionIds'=>$newExpressionIds,
		);

		return $data;
	}

	public function beforeDelete(){
		$expressions = Expression::findAll(array("studyId"=>$this->id));
		foreach($expressions as $expression){
			$expression->delete();
		}
		$questions = Question::findAll(array("studyId"=>$this->id));
		foreach($questions as $question){
			$question->delete();
		}
		$options = QuestionOption::findAll(array("studyId"=>$this->id));
		foreach($options as $option){
			$option->delete();
		}
		$interviewers = Interviewer::findAll(array("studyId"=>$this->id));
		foreach($interviewers as $interviewer){
			$interviewer->delete();
		}
		$alterLists = AlterList::findAll(array("studyId"=>$this->id));
		foreach($alterLists as $alterList){
			$alterList->delete();
		}
		return true;
	}

    public function beforeSave($insert)
    {
        $this->modified = date('Y-m-d h:i:s');
        return parent::beforeSave($insert);
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
            'introduction' => 'Introduction',
            'egoIdPrompt' => 'Ego Id Prompt',
            'alterPrompt' => 'Alter Prompt',
            'conclusion' => 'Conclusion',
            'minAlters' => 'Min Alters',
            'maxAlters' => 'Max Alters',
            'valueRefusal' => 'Value Refusal',
            'valueDontKnow' => 'Value Dont Know',
            'valueLogicalSkip' => 'Value Logical Skip',
            'valueNotYetAnswered' => 'Value Not Yet Answered',
            'modified' => 'Modified',
            'multiSessionEgoId' => 'Multi Session Ego ID',
            'useAsAlters' => 'Use As Alters',
            'restrictAlters' => 'Restrict Alters',
            'fillAlterList' => 'Fill Alter List',
            'created_date' => 'Created Date',
            'closed_date' => 'Closed Date',
            'status' => 'Status',
            'userId' => 'User ID',
            'hideEgoIdPage' => 'Hide Ego Id Page',
            'style' => 'Style',
            'javascript' => 'Javascript',
            'footer' => 'Footer',
            'header' => 'Header',
        ];
    }
}
