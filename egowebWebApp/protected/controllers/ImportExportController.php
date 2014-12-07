<?php
class ImportExportController extends Controller
{
	public function actionImportstudy()
	{

		if(!is_uploaded_file($_FILES['userfile']['tmp_name'])) //checks that file is uploaded
			die("Error importing study");

		$study = simplexml_load_file($_FILES['userfile']['tmp_name']);
		$newStudy = new Study;
		$newQuestionIds = array();
		$newOptionIds = array();
		$newExpressionIds = array();
		$newInterviewIds = array();
		$newAnswerIds = array();
		$newAlterIds = array();
		$merge = false;

		foreach($study->attributes() as $key=>$value){
			if($key!="key" && $key != "id")
				$newStudy->$key = $value;
			if($key == "name"){
				$oldStudy = Study::model()->findByAttributes(array("name"=>$value));

				if($oldStudy){
					$merge = true;
					$newStudy = $oldStudy;
				}
			}
		}


		if(!$merge){

			foreach($study as $key=>$value){
				if(count($value) == 0 && $key != "answerLists" && $key != "expressions")
					$newStudy->$key = html_entity_decode ($value);
			}
			if(isset($_POST['newName']) && $_POST['newName'])
				$newStudy->name = $_POST['newName'];

			if(!$newStudy->save()){
				print_r($newStudy->getErrors());
				die();
			}

			foreach($study->questions->question as $question){
				$newQuestion = new Question;
				$newQuestion->studyId = $newStudy->id;
				foreach($question->attributes() as $key=>$value){
					if($key == "id")
						$oldId = intval($value);
					if($key!="key" && $key != "id" && $key != "networkNShapeQId")
						$newQuestion->$key = $value;
				}
				if($newQuestion->answerType == "SELECTION"){
					$newQuestion->answerType = "MULTIPLE_SELECTION";
					$newQuestion->minCheckableBoxes = 1;
					$newQuestion->maxCheckableBoxes = 1;
				}
				$options = 0;
				foreach($question as $key=>$value){
					if($key == "option"){
						$options++;
					}else if(count($value) == 0 && $key != "option"){
						$newQuestion->$key = html_entity_decode ($value);
					}
				}
				if(!$newQuestion->save())
					print_r($newQuestion->getErrors());
				else
					$newQuestionIds[$oldId] = $newQuestion->id;

				if($options > 0){
					foreach($question->option as $option){
						$newOption = new QuestionOption;
						$newOption->studyId = $newStudy->id;
						$newOption->questionId = $newQuestion->id;
						foreach($option->attributes() as $optionkey=>$val){
							if($optionkey == "id")
								$oldOptionId = intval($val);
							if($optionkey!="key" && $optionkey != "id")
								$newOption->$optionkey = $val;
						}
						if(!$newOption->save())
							print_r($newOption->getErrors());
						else
							$newOptionIds[$oldOptionId] = $newOption->id;
					}
				}
			}

			// loop through the questions and correct linked ids
			$newQuestions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
			foreach($newQuestions as $newQuestion){
				if($newQuestion->networkParams != 0)
					$newQuestion->networkParams = $newQuestionIds[$newQuestion->networkParams];
				if($newQuestion->networkNColorQId != 0)
					$newQuestion->networkNColorQId = $newQuestionIds[$newQuestion->networkNColorQId];
				if($newQuestion->networkNSizeQId != 0)
					$newQuestion->networkNSizeQId = $newQuestionIds[$newQuestion->networkNSizeQId];
				if($newQuestion->networkEColorQId != 0)
					$newQuestion->networkEColorQId = $newQuestionIds[$newQuestion->networkEColorQId];
				if($newQuestion->networkESizeQId != 0)
					$newQuestion->networkESizeQId = $newQuestionIds[$newQuestion->networkESizeQId];
				$newQuestion->save();
			}

			if(count($study->expressions) != 0){
				foreach($study->expressions->expression as $expression){
					$newExpression = new Expression;
					$newExpression->studyId = $newStudy->id;
					foreach($expression->attributes() as $key=>$value){
						if($key == "id")
							$oldExpressionId = intval($value);
						if($key!="key" && $key != "id")
							$newExpression->$key = $value;
					}
					// reference the correct question, since we're not using old ids

					if($newExpression->questionId != "" && isset($newQuestionIds[intval($newExpression->questionId)]))
						$newExpression->questionId = $newQuestionIds[intval($newExpression->questionId)];

					$newExpression->value = $expression->value;
					if(!$newExpression->save())
						print_r($newExpression->getErrors());
					else
						$newExpressionIds[$oldExpressionId] = $newExpression->id;
				}
				// replace adjacencyExpressionId for study
				if($newStudy->adjacencyExpressionId != "" && isset($newExpressionIds[intval($newStudy->adjacencyExpressionId)])){
					$newStudy->adjacencyExpressionId = $newExpressionIds[intval($newStudy->adjacencyExpressionId)];
					$newStudy->save();
				}
				// second loop to replace old ids in Expression->value
				foreach($study->expressions->expression as $expression){
					if(!isset($newExpressionIds[$oldExpressionId]))
						continue;
					foreach($expression->attributes() as $key=>$value){
						if($key == "id"){
							$oldExpressionId = intval($value);
							$newExpression = Expression::model()->findByPk($newExpressionIds[$oldExpressionId]);
						}
					}
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
								else
									$questionId = '';
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
							if(is_numeric($expressionId))
								$expressionId = $newExpressionIds[$expressionId];
						}
						$newExpression->value = implode(',',$expressionIds);
					}
					$newExpression->save();
				}

			}

		}

		if(count($study->interviews) != 0){
			foreach($study->interviews->interview as $interview){
				$newAlterIds = array();
				$newInterview = new Interview;
				$newInterview->studyId = $newStudy->id;
				foreach($interview->attributes() as $key=>$value){
					if($key == "id")
						$oldInterviewId = $value;
					if($key!="key" && $key != "id")
						$newInterview->$key = $value;
				}
				$newInterview->studyId = $newStudy->id;
				if(!$newInterview->save())
					print_r($newInterview->errors);
				else
					$newInterviewIds[intval($oldInterviewId)] = $newInterview->id;

				if(count($interview->alters->alter) != 0){
					foreach($interview->alters->alter as $alter){
						$newAlter = new Alters;
						foreach($alter->attributes() as $key=>$value){
							if($key == "id")
								$thisAlterId = $value;
							if($key!="key" && $key != "id")
								$newAlter->$key = $value;
						}
						if(!preg_match("/,/", $newAlter->interviewId))
							$newAlter->interviewId = $newInterview->id;

						$newAlter->ordering=1;

						if(!$newAlter->save()){
							print_r($newAlter->getErrors());
							die();
						}else{
							$newAlterIds[intval($thisAlterId)] = $newAlter->id;
						}
					}
				}

				if(count($interview->answers->answer) != 0){
					foreach($interview->answers->answer as $answer){
						$newAnswer = new Answer;

						foreach($answer->attributes() as $key=>$value){
							if($key!="key" && $key != "id")
								$newAnswer->$key = $value;
									if($key == "alterId1" && isset($newAlterIds[intval($value)]))
										$newAnswer->alterId1 = $newAlterIds[intval($value)];
									if($key == "alterId2" && isset($newAlterIds[intval($value)]))
										$newAnswer->alterId2 = $newAlterIds[intval($value)];

								if(!$merge){

									if($key == "questionId")
										$newAnswer->questionId = $newQuestionIds[intval($value)];

									if($key == "answerType")
										$answerType = $value;
								}
						}

						if(!$merge){

							if($answerType == "MULTIPLE_SELECTION" && !in_array($newAnswer->value, array($newStudy->valueRefusal,$newStudy->valueDontKnow,$newStudy->valueLogicalSkip,$newStudy->valueNotYetAnswered))){
								$values = explode(',', $newAnswer->value);
								foreach($values as &$value){
									if(isset($newOptionIds[intval($value)]))
										$value = $newOptionIds[intval($value)];
								}
								$newAnswer->value = implode(',', $values);
							}

						}


						$newAnswer->studyId = $newStudy->id;
						$newAnswer->interviewId = $newInterview->id;

						if(!$newAnswer->save()){
							print_r($newAnswer->getErrors());
							die();
						}
					}
				}
			}
		}

		foreach($newAlterIds as $oldId=>$newId){
			$alter = Alters::model()->findByPk($newId);
			if(preg_match("/,/", $alter->interviewId)){
				$values = explode(',', $alter->interviewId);
				foreach($values as &$value){
					if(isset($newInterviewIds[intval($value)]))
						$value = $newInterviewIds[intval($value)];
				}
				$alter->interviewId = implode(',', $values);
				$alter->save();
			}
		}

		if(count($study->answerLists) != 0){
			foreach($study->answerLists->answerList as $answerList){
				$newAnswerList = new AnswerList;
				$newAnswerList->studyId = $newStudy->id;
				foreach($answerList->attributes() as $key=>$value){
					if($key!="key" && $key != "id")
						$newAnswerList->$key = $value;
				}
				if(!$newAnswerList->save())
					print_r($newAnswerList->getErrors());
			}
		}

		$this->redirect(array('/authoring/edit','id'=>$newStudy->id));

	}

	public function actionReplicate(){
		if($_POST['name'] == "" || $_POST['studyId'] == "")
			die("nothing to replicate");
		$study = Study::model()->findByPk((int)$_POST['studyId']);
		$study->name = $_POST['name'];
		$questions = Question::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		$options = QuestionOption::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		$expressions = Expression::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		$answerLists = AnswerList::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));

		$data = Study::replicate($study, $questions, $options, $expressions, $answerLists);
		$this->redirect(array('/authoring/edit','id'=>$data['studyId']));

	}

	public function actionIndex()
	{
		$this->render('index');
	}

	public function actionAjaxInterviews($id)
	{
		$study = Study::model()->findByPk($id);
		$interviews = Interview::model()->findAllByAttributes(array('studyId'=>$id));
        $this->renderPartial('_interviews',
            array(
	            'study'=>$study,
                'interviews'=>$interviews,
            ), false, true
        );
	}

	public function actionExportstudy(){
		if(!isset($_POST['studyId']) || $_POST['studyId'] == "")
			die("nothing to export");

		$study = Study::model()->findByPk((int)$_POST['studyId']);
		$questions = Question::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		$expressions = Expression::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		$answerLists = AnswerList::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		$alterLists = AlterList::model()->findAllByAttributes(array("studyId"=>$_POST['studyId']));
		$study->introduction = htmlspecialchars (trim(preg_replace('/\s+|&nbsp;/', ' ', $study->introduction)), ENT_QUOTES, "UTF-8", false);
		$study->egoIdPrompt = htmlspecialchars (trim(preg_replace('/\s+|&nbsp;/', ' ', $study->egoIdPrompt)), ENT_QUOTES, "UTF-8", false);
		$study->alterPrompt = htmlspecialchars (trim(preg_replace('/\s+|&nbsp;/', ' ', $study->alterPrompt)), ENT_QUOTES, "UTF-8", false);
		$study->alterPrompt = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $study->alterPrompt)), ENT_QUOTES, "UTF-8", false);
		$study->conclusion = htmlspecialchars (trim(preg_replace('/\s+|&nbsp;/', ' ', $study->conclusion)), ENT_QUOTES, "UTF-8", false);


		if(isset($_POST['export']) && count($_POST['export']) > 0){
			$interviews = Interview::model()->findAllByAttributes(array("id"=>$_POST['export']));
			foreach($interviews as $result){
				$interview[$result->id] = $result;
				$answer = Answer::model()->findAllByAttributes(array("interviewId"=>$result->id));
				$answers[$result->id] = $answer;
				$alter = q("SELECT * FROM alters WHERE FIND_IN_SET($result->id, interviewId)")->queryAll();
				foreach($alter as &$a){
					$a['name'] = decrypt($a['name']);
				}
				$alters[$result->id] = $alter;
				$graph = Graph::model()->findAllByAttributes(array("interviewId"=>$result->id));
				$graphs[$result->id] = $graph;
				$note = Note::model()->findAllByAttributes(array("interviewId"=>$result->id));
				$notes[$result->id] = $note;
			}
		}

		header("Content-type: text/xml; charset=utf-8");
		header("Content-Disposition: attachment; filename=".$study->name.".study");
		header("Content-Type: application/force-download");
		echo '<?xml version="1.0" encoding="UTF-8"?>

';
		echo <<<EOT
<study id="{$study->id}" name="{$study->name}" minAlters="{$study->minAlters}" maxAlters="{$study->maxAlters}" valueDontKnow="{$study->valueDontKnow}" valueLogicalSkip="{$study->valueLogicalSkip}" valueNotYetAnswered="{$study->valueNotYetAnswered}" valueRefusal="{$study->valueRefusal}" adjacencyExpressionId="{$study->adjacencyExpressionId}" modified="{$study->modified}" multiSessionEgoId="{$study->multiSessionEgoId}" useAsAlters="{$study->useAsAlters}" restrictAlters="{$study->restrictAlters}" fillAlterList="{$study->fillAlterList}">
	<introduction>{$study->introduction}</introduction>
	<egoIdPrompt>{$study->egoIdPrompt}</egoIdPrompt>
	<alterPrompt>{$study->alterPrompt}</alterPrompt>
	<conclusion>{$study->conclusion}</conclusion>
EOT;

		if(count($questions) > 0){
			echo '
	<questions>';
			foreach($questions as $question){
				$question->title = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $question->title)), ENT_QUOTES, "UTF-8", false);
				$question->preface = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $question->preface)), ENT_QUOTES, "UTF-8", false);
				$question->prompt = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $question->prompt)), ENT_QUOTES, "UTF-8", false);
				$question->citation = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $question->citation)), ENT_QUOTES, "UTF-8", false);
				$question->networkParams = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $question->networkParams)), ENT_QUOTES, "UTF-8", false);

				echo <<<EOT

		<question id="{$question->id}" title="{$question->title}" answerType="{$question->answerType}" subjectType="{$question->subjectType}" askingStyleList="{$question->askingStyleList}" ordering="{$question->ordering}" answerReasonExpressionId="{$question->answerReasonExpressionId}" otherSpecify="{$question->otherSpecify}" noneButton="{$question->noneButton}" allButton="{$question->allButton}" pageLevelDontKnowButton="{$question->pageLevelDontKnowButton}" pageLevelRefuseButton="{$question->pageLevelRefuseButton}" dontKnowButton="{$question->dontKnowButton}" networkRelationshipExprId="{$question->networkRelationshipExprId}" networkParams="{$question->networkParams}" networkNColorQId="{$question->networkNColorQId}" networkNSizeQId="{$question->networkNSizeQId}" networkEColorQId="{$question->networkEColorQId}" networkESizeQId="{$question->networkESizeQId}" refuseButton="{$question->refuseButton}" allOptionString="{$question->allOptionString}" minLimitType="{$question->minLimitType}" minLiteral="{$question->minLiteral}" minPrevQues="{$question->minPrevQues}" maxLimitType="{$question->maxLimitType}" maxLiteral="{$question->maxLiteral}" maxPrevQues="{$question->maxPrevQues}" minCheckableBoxes="{$question->minCheckableBoxes}" maxCheckableBoxes="{$question->maxCheckableBoxes}" withListRange="{$question->withListRange}" listRangeString="{$question->listRangeString}" minListRange="{$question->minListRange}" maxListRange="{$question->maxListRange}" timeUnits="{$question->timeUnits}" symmetric="{$question->symmetric}" keepOnSamePage="{$question->keepOnSamePage}">
			<preface>{$question->preface}</preface>
			<prompt>{$question->prompt}</prompt>
			<citation>{$question->citation}</citation>
EOT;
				if($question->answerType == "SELECTION" || $question->answerType == "MULTIPLE_SELECTION"){
					$options = QuestionOption::model()->findAllByAttributes(
						array("studyId"=>$_POST['studyId'], "questionId"=>$question->id)
					);

					foreach($options as $option){
						$option->name = htmlspecialchars($option->name, ENT_QUOTES, "UTF-8", false);
						echo <<<EOT

			<option id="{$option->id}" name="{$option->name}" value="{$option->value}" ordering="{$option->ordering}"/>
EOT;
					}
				}
				echo "
		</question>";
			}
			echo "
	</questions>";
		}
		if(count($expressions) > 0){
			echo "
	<expressions>";
			foreach($expressions as $expression){
				$expression->name = htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $expression->name)), ENT_QUOTES, "UTF-8", false);

				echo <<<EOT

		<expression id="{$expression->id}" name="{$expression->name}" questionId="{$expression->questionId}" resultForUnanswered="{$expression->resultForUnanswered}" type="{$expression->type}" operator="{$expression->operator}">
			<value>{$expression->value}</value>
		</expression>
EOT;
			}
			echo "
	</expressions>";
		}
		if(count($expressions) > 0){
			echo "
	<answerLists>";
			foreach($answerLists as $answerList){
				echo <<<EOT

		<answerList id="{$answerList->id}" listName="{$answerList->listName}" listOptionNames="{$answerList->listOptionNames}"/>
EOT;
			}
			echo "
	</answerLists>";
		}
		if(isset($_POST['export']) && count($_POST['export']) > 0){
			echo "
	<interviews>";
			foreach($interviews as $interview){
				echo <<<EOT

		<interview id="{$interview->id}" studyId="{$interview->studyId}" completed="{$interview->completed}" start_date="{$interview->start_date}" complete_date="{$interview->complete_date}">
EOT;
				if(isset($answers[$interview->id])){
					echo "
			<answers>";
					foreach($answers[$interview->id] as $answer){
						echo <<<EOT

				<answer id="{$answer->id}" questionId="{$answer->questionId}" interviewId="{$answer->interviewId}" alterId1="{$answer->alterId1}" alterId2="{$answer->alterId2}" value="{$answer->value}" otherSpecifyText="{$answer->otherSpecifyText}" skipReason="{$answer->skipReason}" questionType="{$answer->questionType}" answerType="{$answer->answerType}" />
EOT;
					}
					echo "
			</answers>";
				}

				if(isset($alters[$interview->id])){
					echo "
			<alters>";
					foreach($alters[$interview->id] as $alter){
						echo <<<EOT

				<alter id="{$alter['id']}" name="{$alter['name']}" interviewId="{$alter['interviewId']}" ordering="{$alter['ordering']}" alterListId="{$alter['alterListId']}" />
EOT;
					}
					echo "
			</alters>";
				}

			echo "
		</interview>";
			}
			echo "
	</interviews>";
		}
		echo "
</study>";
	}

}
