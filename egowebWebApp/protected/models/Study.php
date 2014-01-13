<?php

/**
 * This is the model class for table "study".
 *
 * The followings are the available columns in table 'study':
 * @property integer $id
 * @property string $random_key
 * @property integer $active
 * @property string $name
 * @property string $introduction
 * @property string $egoIdPrompt
 * @property string $alterPrompt
 * @property string $conclusion
 * @property string $minAlters
 * @property string $maxAlters
 * @property string $adjacencyExpressionId
 * @property integer $valueRefusal
 * @property integer $valueDontKnow
 * @property integer $valueLogicalSkip
 */
class Study extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Study the static model class
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
		return 'study';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('id, active, name, introduction, egoIdPrompt, alterPrompt, conclusion, minAlters, maxAlters, adjacencyExpressionId, multiSessionEgoId', 'length', 'max'=>2048),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, name, introduction, egoIdPrompt, alterPrompt, conclusion, minAlters, maxAlters, adjacencyExpressionId, valueRefusal, valueDontKnow, valueLogicalSkip, valueNotYetAnswered', 'safe', 'on'=>'search'),
			array('modified','default',
				'value'=>new CDbExpression('NOW()'),
				'setOnEmpty'=>true,'on'=>'insert'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
			return array(
			);
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
	}

	public function getName($id){
		$model = Study::model()->findByPk($id);
		if($model)
			return $model->name;
		else
			return "<deleted>";
	}

	public function updated($id){
		if(!$id)
			return false;
		$study = Study::model()->findByPk($id);
		if($study){
			$study->modified = new CDbExpression('NOW()');
			$study->save();
		}
	}

	public function nav($id, $pageNumber, $interviewId = null){
		$i = 0;
		$pages = array();
		$study = Study::model()->findByPk($id);
		if($study->introduction != ""){
			$pages[$i] = Study::checkPage($i, $pageNumber, "INTRODUCTION");
			$i++;
		}
		$pages[$i] = Study::checkPage($i, $pageNumber, "EGO ID");
		$i++;
		if(!$interviewId)
			return json_encode($pages);
		$ego_qs = q("SELECT * FROM question WHERE studyId = $id AND subjectType ='EGO' order by ordering")->queryAll();
		$prompt = "";
		$ego_question_list = array();
		foreach($ego_qs as $question){
			if($interviewId){
				$expression = new Expression;
				if(!$expression->evalExpression($question['answerReasonExpressionId'], $interviewId))
					continue;
			}
			if(($question['askingStyleList'] != 1 || $prompt != trim(preg_replace('/<\/*[^>]*>/', '', $question['prompt']))) && count($ego_question_list) > 0){
				    $pages[$i] = Study::checkPage($i, $pageNumber, $ego_question_list['title']);
					$prompt = "";
				    $ego_question_list = array();
				    $i++;
			}
			if($question['preface'] != ""){
				$pages[$i] = Study::checkPage($i, $pageNumber, "PREFACE");
				$i++;
			}
			if($question['askingStyleList'] == 1){
			    $prompt = trim(preg_replace('/<\/*[^>]*>/', '', $question['prompt']));
			    $ego_question_list = $question;
			}else{
			    $pages[$i] = Study::checkPage($i, $pageNumber, $question['title']);
			    $i++;
			}

		}
		if(count($ego_question_list) > 0){
			$pages[$i] = Study::checkPage($i, $pageNumber, $ego_question_list['title']);
			$i++;
		}
		$pages[$i] = Study::checkPage($i, $pageNumber, "ALTER_PROMPT");
		$i++;
		$alters = Alters::model()->findAllByAttributes(array('interviewId'=>$interviewId));
		$answers = q("SELECT count(id) FROM answer WHERE interviewId = " . $interviewId . " AND (questionType =  'ALTER' OR questionType = 'ALTER_PAIR') ")->queryScalar();
		if(count($alters) > 0 && $answers > 0){
			$alter_qs = q("SELECT * FROM question WHERE studyId = $id AND subjectType ='ALTER' order by ordering")->queryAll();
			$prompt = "";
			$alter_question_list = array();
			foreach($alter_qs as $question){
				$expression = new Expression;
				foreach($alters as $alter){
				    if(!$expression->evalExpression($question['answerReasonExpressionId'], $interviewId, $alter->id)){
				    	continue;
				    }
				    if($question['askingStyleList']){
				    	$alter_question_list=$question;
				    }else{
				    	if($question['preface'] != ""){
				    		$pages[$i] = Study::checkPage($i, $pageNumber, "PREFACE");
				    		$i++;
				    	}
				    	$pages[$i] = Study::checkPage($i, $pageNumber, $question['title']);
				    	$i++;
				    }
				}
				if($question['askingStyleList']){
				    if(count($alter_question_list) > 0){
				    	if($question['preface'] != ""){
				    		$pages[$i] = Study::checkPage($i, $pageNumber, "PREFACE");
				    		$i++;
				    	}
				    	$pages[$i] = Study::checkPage($i, $pageNumber, $alter_question_list['title']);
				    	$i++;
				    }
				}
			}
			$alter_pair_qs = q("SELECT * FROM question WHERE studyId = $id AND subjectType ='ALTER_PAIR' order by ordering")->queryAll();
			$prompt = "";
			$alter_pair_question_list = array();
			foreach ($alter_pair_qs as $question){
			    $alters2 = $alters;
			    $expression = new Expression;
			    foreach($alters as $alter){
			    	if($question['symmetric'])
			    		array_shift($alters2);
			    	$alter_pair_question_list = array();
			    	foreach($alters2 as $alter2){
			    		if($alter->id == $alter2->id)
			    			continue;
			    		if(!$expression->evalExpression($question['answerReasonExpressionId'], $interviewId, $alter->id, $alter2->id))
			    			continue;
			    		$alter_pair_question_list = $question;
			    	}
			    	if(count($alter_pair_question_list) > 0){
				    	if($question['preface'] != ""){
				    		$pages[$i] = Study::checkPage($i, $pageNumber, "PREFACE");
				    		$question['preface'] = "";
				    		$i++;
				    	}
				    	$pages[$i] = Study::checkPage($i, $pageNumber, $alter_pair_question_list['title'] . " - " . $alter->name);
				    	$i++;
					}
				}
			}
			$network_qs = q("SELECT * FROM question WHERE studyId = $id AND subjectType ='NETWORK' order by ordering")->queryAll();
			foreach($network_qs as $question){
			    if($interviewId){
			    	$expression = new Expression;
			    	if(!$expression->evalExpression($question['answerReasonExpressionId'], $interviewId))
			    		continue;
			    }
			    if($question['preface'] != ""){
			    	$pages[$i] = Study::checkPage($i, $pageNumber, "PREFACE");
			    	$i++;
			    }
			    $pages[$i] = Study::checkPage($i, $pageNumber, $question['title']);
			    $i++;
			}
		}
		$pages[$i] = Study::checkPage($i, $pageNumber, "CONCLUSION");
		return json_encode($pages);
	}

	private function checkPage($currentPage, $pageNumber, $text){
		if($currentPage == $pageNumber)
			$text = "<b>".$text."</b>";
		return $text;
	}

	/**
	 * CORE FUNCTION
	 * @return array pages of questions
	 */
	public function buildQuestions($id, $pageNumber = null, $interviewId = null){
		$page = array();
		$i = 0;
		$study = Study::model()->findByPk($id);
		if($study->introduction != ""){
			if($i == $pageNumber){
				$introduction = new Question;
				$introduction->answerType = "INTRODUCTION";
				$introduction->prompt = $study->introduction;
				$page[$i] = array('0'=>$introduction);
				return $page[$i];
			}
			$i++;
		}
		if($pageNumber == $i){
			$questions = Question::model()->findAllByAttributes(array('studyId'=>$id, 'subjectType'=>'EGO_ID'), $params=array('order'=>'ordering'));
			foreach($questions as $question){
				$ego_id_questions[$question->id] = $question;
			}
			$page[$i] = $ego_id_questions;
			return $page[$i];
		}
		if(is_numeric($interviewId)){
			$i++;
			$result = q("SELECT id, preface,answerReasonExpressionId FROM question WHERE subjectType = 'EGO' AND studyId = $id ORDER BY ordering")->queryAll();
			$egoQuestionIds = array();
			$egoPrefaces = array();
			$egoQuestionExpressions = array();
			foreach($result as $question){
				$egoQuestionIds[] = $question['id'];
				$egoPrefaces[$question['id']] = $question['preface'];
				$egoQuestionExpressions[$question['id']] = $question['answerReasonExpressionId'];
			}
			if(count($egoQuestionIds) > 0)
				$result = q("SELECT id, questionId, value FROM answer WHERE questionId in (" . implode(',', $egoQuestionIds) . ")")->queryAll();
			else
				$result = array();
			$answers = array();
			foreach($result as $answer){
				$answers[$answer['questionId']] = $answer;
			}
			$ego_question_list = array();
			$prompt = "";
			foreach ($egoQuestionIds as $questionId){
				$expression = new Expression;
				if(!$expression->evalExpression($egoQuestionExpressions[$questionId], $interviewId)){
				    $data = array(
				    	'value'=>$study->valueLogicalSkip,
				    );
				    u('answer', $data, "id = " . $answers[$questionId]['id']);
				    continue;
				}
				$question = Question::model()->findByPk($questionId);
				if(($question->askingStyleList != 1 || $prompt != trim(preg_replace('/<\/*[^>]*>/', '', $question->prompt))) && count($ego_question_list) > 0){
				    if($pageNumber == $i){
				    	$page[$i] = $ego_question_list;
				    	return $page[$i];
				    }

				    $prompt = trim(preg_replace('/<\/*[^>]*>/', '', $question->prompt));
				    $ego_question_list = array();
				    $i++;
				}
				if($egoPrefaces[$questionId] != ""){
				    if($pageNumber == $i){
				    	$preface = new Question;
				    	$preface->answerType = "PREFACE";
				    	$preface->prompt = $egoPrefaces[$questionId];
				    	$page[$i] = array('0'=>$preface);
				    	return $page[$i];
				    }
				    $i++;
				}

				if($question->askingStyleList == 1){
				    $prompt = trim(preg_replace('/<\/*[^>]*>/', '', $question->prompt));
				    $ego_question_list[$question->id] = $question;
				}else{
				    if($pageNumber == $i){
				    	$page[$i] = array($question->id=>$question);
				    	return $page[$i];
				    }
				    $i++;
				}
			}
			if(count($ego_question_list) > 0){
				if($pageNumber == $i){
					$page[$i] = $ego_question_list;
					return $page[$i];
				}
				$i++;
			}

			if($pageNumber == $i){
				$alter_prompt = new Question;
				$alter_prompt->answerType = "ALTER_PROMPT";
				$alter_prompt->prompt = $study->alterPrompt;
				$alter_prompt->studyId = $id;
				$page[$i] = array('0'=>$alter_prompt);
				return $page[$i];
			}
			$i++;
			$alters = Alters::model()->findAllByAttributes(array('interviewId'=>$interviewId));
			if(count($alters) > 0){
				$result = q("SELECT id, preface, askingStyleList,answerReasonExpressionId FROM question WHERE subjectType = 'ALTER' AND studyId = $id ORDER BY ordering")->queryAll();
				$alterQuestionIds = array();
				$alterQuestionPrefaces = array();
				$alterAskingStyles = array();
				$alterQuestionExpressions = array();
				foreach($result as $question){
					$alterQuestionIds[] = $question['id'];
					$alterPrefaces[$question['id']] = $question['preface'];
					$alterQuestionExpressions[$question['id']] = $question['answerReasonExpressionId'];
					$alterAskingStyles[$question['id']] = $question['askingStyleList'];
				}
				if(count($alterQuestionIds) > 0)
					$result = q("SELECT id, questionId, alterId1, value FROM answer WHERE questionId in (" . implode(',', $alterQuestionIds) . ")")->queryAll();
				else
					$result = array();
				$answers = array();
				foreach($result as $answer){
				    $answers[$answer['questionId'].'-'.$answer['alterId1']] = $answer;
				}
				foreach ($alterQuestionIds as $questionId){
					$alter_question_list = array();
					$expression = new Expression;
					$question = Question::model()->findByPk($questionId);
					foreach($alters as $alter){
						if(!$expression->evalExpression($alterQuestionExpressions[$questionId], $interviewId, $alter->id)){
							$data = array(
								'value'=>$study->valueLogicalSkip,
							);
							u('answer', $data, "id = " . $answers[$question->id.'-'.$alter->id]['id']);
							continue;
						}
					    if($alterAskingStyles[$questionId]){
					    	$alter_question = new Question;
					    	$alter_question->attributes = $question->attributes;
					    	$alter_question->prompt = str_replace('$$', $alter->name, $alter_question->prompt);
					    	$alter_question->alterId1 = $alter->id;
					    	$alter_question_list[$questionId.'-'.$alter->id]=$alter_question;
					    }else{
					    	if($alterPrefaces[$questionId] != ""){
					    		if($i == $pageNumber){
					    			$preface = new Question;
					    			$preface->answerType = "PREFACE";
					    			$preface->prompt = $alterPrefaces[$questionId];
					    			$page[$i] = array('0'=>$preface);
					    			return $page[$i];
					    		}
					    		$alterPrefaces[$questionId] = "";
					    		$i++;
					    	}
					    	if($i == $pageNumber){
					    		$alter_question = new Question;
					    		$alter_question->attributes = $question->attributes;
					    		$alter_question->prompt = str_replace('$$', $alter->name, $alter_question->prompt);
					    		$alter_question->alterId1 = $alter->id;
					    		$page[$i] = array($question->id.'-'.$alter->id=>$alter_question);
					    		return $page[$i];
					    	}else {
					    		$i++;
					    	}
					    }
					}
					if($alterAskingStyles[$questionId]){
					    if(count($alter_question_list) > 0){
					    	if($alterPrefaces[$questionId] != ""){
					    		if($i == $pageNumber){
					    			$preface = new Question;
					    			$preface->answerType = "PREFACE";
					    			$preface->prompt = $alterPrefaces[$questionId];
					    			$page[$i] = array('0'=>$preface);
					    			return $page[$i];
					    		}
					    		$i++;
					    	}
					    	if($i == $pageNumber){
					    		$page[$i] = $alter_question_list;
					    		return $page[$i];
					    	}
					    	$i++;
					    }

					}
				}

				$result = q("SELECT id, preface, askingStyleList,answerReasonExpressionId, symmetric FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = $id ORDER BY ordering")->queryAll();
				$alterPairQuestionIds = array();
				$alterPairQuestionPrefaces = array();
				$alterPairSymmetry = array();
				$alterPairQuestionExpressions = array();
				foreach($result as $question){
					$alterPairQuestionIds[] = $question['id'];
					$alterPairPrefaces[$question['id']] = $question['preface'];
					$alterPairQuestionExpressions[$question['id']] = $question['answerReasonExpressionId'];
					$alterPairSymmetry[$question['id']] = $question['symmetric'];
				}
				if(count($alterPairQuestionIds) > 0)
					$result = q("SELECT id, questionId, alterId1, alterId2, value FROM answer WHERE questionId in (" . implode(',', $alterPairQuestionIds) . ")")->queryAll();
				else
					$result = array();
				$answers = array();
				foreach($result as $answer){
				    $answers[$answer['questionId'].'-'.$answer['alterId1'].'and'.$answer['alterId2']] = $answer;
				}
				foreach ($alterPairQuestionIds as $questionId){
					$alters2 = $alters;
					$question = Question::model()->findByPk($questionId);
					$expression = new Expression;
					foreach($alters as $alter){
						if($alterPairSymmetry[$questionId])
							array_shift($alters2);
						$alter_pair_question_list = array();
						foreach($alters2 as $alter2){
							if($alter->id == $alter2->id)
								continue;
							if(!$expression->evalExpression($alterPairQuestionExpressions[$questionId], $interviewId, $alter->id, $alter2->id)){
								$data = array(
									'value'=>$study->valueLogicalSkip,
								);
				    			u('answer', $data, "id = " . $answers[$question->id.'-'.$alter->id.'and'.$alter2->id]['id']);
								continue;
							}
							$alter_pair_question = new Question;
							$alter_pair_question->attributes = $question->attributes;
							$alter_pair_question->prompt = str_replace('$$1', $alter->name, $alter_pair_question->prompt);
							$alter_pair_question->prompt = str_replace('$$2', $alter2->name, $alter_pair_question->prompt);
							$alter_pair_question->alterId1 = $alter->id;
							$alter_pair_question->alterId2 = $alter2->id;
							$alter_pair_question_list[$question->id.'-'.$alter->id.'and'.$alter2->id]=$alter_pair_question;
						}
						if(count($alter_pair_question_list) > 0){
						    if($alterPairPrefaces[$questionId] != ""){
						    	if($i == $pageNumber){
						    		$preface = new Question;
						    		$preface->answerType = "PREFACE";
						    		$preface->prompt = $alterPairPrefaces[$questionId];
						    		$page[$i] = array('0'=>$preface);
						    		return $page[$i];
						    	}
						    	$alterPairPrefaces[$questionId] = "";
						    	$i++;
						    }
						    if($i == $pageNumber){
						    	$page[$i] = $alter_pair_question_list;
						    	return $page[$i];
						    }
						    $i++;
						}
					}
				}
				$result = q("SELECT id, preface, answerReasonExpressionId FROM question WHERE subjectType = 'NETWORK' AND studyId = $id ORDER BY ordering")->queryAll();
				$networkQuestionIds = array();
				$networkPrefaces = array();
				$networkExpressions = array();
				foreach($result as $question){
					$networkQuestionIds[] = $question['id'];
					$networkPrefaces[$question['id']] = $question['preface'];
					$networkExpressions[$question['id']] = $question['answerReasonExpressionId'];
				}
				if(count($networkQuestionIds) > 0)
					$result = q("SELECT id, questionId, value FROM answer WHERE questionId in (" . implode(',', $networkQuestionIds) . ")")->queryAll();
				else
					$result = array();
				$answers = array();
				foreach($result as $answer){
				    $answers[$answer['questionId']] = $answer;
				}
				foreach ($networkQuestionIds as $questionId){
				    if($i == $pageNumber){
				    	if(!isset($answers[$questionId]['value']))
				    		$answers[$questionId]['value'] = $study->valueNotYetAnswered;
				        $expression = new Expression;
				        if(!$expression->evalExpression($networkExpressions[$questionId], $interviewId)){
				        	$data = array(
				        		'value'=>$study->valueLogicalSkip,
				        	);
				        	u('answer', $data, "id = " . $answers[$questionId]['id']);
				        	continue;
				        }
				    }
				    if($networkPrefaces[$questionId] != ""){
				        if($pageNumber == $i){
				        	$preface = new Question;
				        	$preface->answerType = "PREFACE";
				        	$preface->prompt = $networkPrefaces[$questionId];
				        	$page[$i] = array('0'=>$preface);
				        	return $page[$i];
				        }
				        $i++;
				    }
				    if($pageNumber == $i){
				    	$question = Question::model()->findByPk($questionId);
				    	$page[$i] = array($question->id=>$question);
				    	return $page[$i];
				    }
				    $i++;
				}
			}
			$conclusion = new Question;
			$conclusion->answerType = "CONCLUSION";
			$conclusion->prompt = $study->conclusion;
			$page[$i] = array('0'=>$conclusion);
			return $page[$i];
		}
		return false;
	}

	public function isMulti($id = null){
		if(!$id && isset($this->id))
			$multi = $this->multiSessionEgoId;
		else
			$multi = q("SELECT multiSessionEgoId FROM study WHERE id = " . $id)->queryScalar();
		if($multi)
			return $multi;
		else
			return false;
	}

	public function replicate($study, $questions, $options, $expressions, $answerLists = array())
	{
		$newQuestionIds = array();
		$newOptionIds = array();
		$newExpressionIds = array();

		$newStudy = new Study;
		$newStudy->attributes = $study->attributes;
		$newStudy->id = null;

		if(!$newStudy->save())
			return false;

		foreach($questions as $question){
			$newQuestion = new Question;
			$newQuestion->attributes = $question->attributes;
			$newQuestion->id = null;
			$newQuestion->studyId = $newStudy->id;
			if(!$newQuestion->save())
				return false;
			$newQuestionIds[$question->id] = $newQuestion->id;
		}
		foreach($questions as $question){
		  $newQuestion = Question::model()->findByPk($newQuestionIds[$question->id]);
		  if($newQuestion){
		      if(is_numeric($newQuestion->minPrevQues) && $newQuestion->minPrevQues != 0)
		          $newQuestion->minPrevQues = $newQuestionIds[$newQuestion->minPrevQues];
		      if(is_numeric($newQuestion->maxPrevQues) && $newQuestion->maxPrevQues != 0)
		          $newQuestion->maxPrevQues = $newQuestionIds[$newQuestion->maxPrevQues];
		      if(is_numeric($newQuestion->networkNShapeQId) && $newQuestion->networkNShapeQId != 0)
		          $newQuestion->networkNShapeQId = $newQuestionIds[$newQuestion->networkNShapeQId];
		      if(is_numeric($newQuestion->networkNColorQId) && $newQuestion->networkNColorQId != 0)
		          $newQuestion->networkNColorQId = $newQuestionIds[$newQuestion->networkNColorQId];
		      if(is_numeric($newQuestion->networkNSizeQId) && $newQuestion->networkNSizeQId != 0)
		          $newQuestion->networkNSizeQId = $newQuestionIds[$newQuestion->networkNSizeQId];
		      if(is_numeric($newQuestion->networkEColorQId) && $newQuestion->networkEColorQId != 0)
		          $newQuestion->networkEColorQId = $newQuestionIds[$newQuestion->networkEColorQId];
		      if(is_numeric($newQuestion->networkESizeQId) && $newQuestion->networkESizeQId != 0)
		          $newQuestion->networkESizeQId = $newQuestionIds[$newQuestion->networkESizeQId];
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
				return false;
			else
				$newOptionIds[$option->id] = $newOption->id;
		}

		foreach($expressions as $expression){
			$newExpression = new Expression;
			$newExpression->attributes = $expression->attributes;
			$newExpression->id = null;
			$newExpression->studyId = $newStudy->id;
			if($newExpression->questionId != "" &&  $newExpression->questionId  != 0 && isset($newQuestionIds[$expression->questionId]))
				$newExpression->questionId = $newQuestionIds[$expression->questionId];
			if(!$newExpression->save())
				return false;
			else
				$newExpressionIds[$expression->id] = $newExpression->id;
		}

		// replace adjacencyExpressionId for study
		if($newStudy->adjacencyExpressionId != ""){
			$newStudy->adjacencyExpressionId = $newExpressionIds[intval($newStudy->adjacencyExpressionId)];
			$newStudy->save();
		}
		// second loop to replace old ids in Expression->value
		foreach($expressions as $expression){
			$oldExpressionId = $expression->id;
			$newExpression = Expression::model()->findByPk($newExpressionIds[$expression->id]);

			if(!$newExpression)
				die('error fetching expression id:' . $expression->id . $newExpressionIds[$expression->id]);

			// replace answerReasonExpressionId for newly uploaded questions with correct expression ids
			$questions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id,'answerReasonExpressionId'=>$oldExpressionId));
			if(count($questions) > 0){
				foreach($questions as $question){
					$question->answerReasonExpressionId = $newExpressionIds[$oldExpressionId];
					$question->save();
				}
			}
			$questions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id,'networkRelationshipExprId'=>$oldExpressionId));
			if(count($questions) > 0){
				foreach($questions as $question){
					$question->networkRelationshipExprId = $newExpressionIds[$oldExpressionId];
					$question->save();
				}
			}
			// reference the correct question, since we're not using old ids
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
			}
			$newExpression->save();
		}

		foreach($answerLists as $answerList){
			$newAnswerList = new answerList;
			$newAnswerList->attributes = $answerList->attributes;
			$newAnswerList->id = null;
			$newAnswerList->studyId = $newStudy->id;
			if(!$newAnswerList->save())
				return false;
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
		$expressions = Expression::model()->findAllByAttributes(array("studyId"=>$this->id));
		foreach($expressions as $expression){
			$expression->delete();
		}
		$questions = Question::model()->findAllByAttributes(array("studyId"=>$this->id));
		foreach($questions as $question){
			$question->delete();
		}
		$options = QuestionOption::model()->findAllByAttributes(array("studyId"=>$this->id));
		foreach($options as $option){
			$option->delete();
		}
		$answerLists = AnswerList::model()->findAllByAttributes(array("studyId"=>$this->id));
		foreach($answerLists as $answerList){
			$answerList->delete();
		}
		return true;
	}

	public function beforeSave(){
		if(trim($this->introduction) == "<br>")
			$this->introduction = "";
		return true;
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
			'introduction' => 'Introduction',
			'egoIdPrompt' => 'Ego Id Prompt',
			'alterPrompt' => 'Alter Prompt',
			'conclusion' => 'Conclusion',
			'minAlters' => 'Min Alters',
			'maxAlters' => 'Max Alters',
			'adjacencyExpressionId' => 'Adjacency Expression',
			'valueRefusal' => 'Value Refusal',
			'valueDontKnow' => 'Value Dont Know',
			'valueLogicalSkip' => 'Value Logical Skip',
			'valueNotYetAnswered' => 'Value Not Yet Answered',
			'multiSessionEgoId' => "Multi-session"
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('introduction',$this->introduction,true);
		$criteria->compare('egoIdPrompt',$this->egoIdPrompt,true);
		$criteria->compare('alterPrompt',$this->alterPrompt,true);
		$criteria->compare('conclusion',$this->conclusion,true);
		$criteria->compare('minAlters',$this->minAlters,true);
		$criteria->compare('maxAlters',$this->maxAlters,true);
		$criteria->compare('adjacencyExpressionId',$this->adjacencyExpressionId,true);
		$criteria->compare('valueRefusal',$this->valueRefusal);
		$criteria->compare('valueDontKnow',$this->valueDontKnow);
		$criteria->compare('valueLogicalSkip',$this->valueLogicalSkip);
		$criteria->compare('valueNotYetAnswered',$this->valueNotYetAnswered);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}