<?php
class ImportExportController extends Controller
{
	public function actionImportstudy()
	{
		if(!is_uploaded_file($_FILES['files']['tmp_name'][0])) //checks that file is uploaded
			die("Error importing study");

        foreach($_FILES['files']['tmp_name'] as $tmp_name){
    		$study = simplexml_load_file($tmp_name);
    		$newStudy = new Study;
    		$newQuestionIds = array();
    		$newOptionIds = array();
    		$newExpressionIds = array();
    		$newInterviewIds = array();
    		$newAnswerIds = array();
    		$newAlterIds = array();
    		$merge = false;
    
    		foreach($study->attributes() as $key=>$value){
    			if($key != "id" && $newStudy->hasAttribute($key))
    				$newStudy->$key = $value;
    			if($key == "name"){
    				$oldStudy = Study::model()->findByAttributes(array("name"=>$value));
    				if($oldStudy && $_POST['newName'] != $oldStudy->name){
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
    				echo "study: " . print_r($newStudy->getErrors());
    				die();
    			}
    
    			if($study->alterPrompts->alterPrompt){
    
    				foreach($study->alterPrompts->alterPrompt as $alterPrompt){
    					$newAlterPrompt = new AlterPrompt;
    					foreach($alterPrompt->attributes() as $key=>$value){
    						if($key != "id")
    							$newAlterPrompt->$key = $value;
    						if($key == "afterAltersEntered")
    							$value = intval($value);
    					}
    					$newAlterPrompt->studyId = $newStudy->id;
    					if(!$newAlterPrompt->save())
    						echo "Alter prompt: $newAlterPrompt->afterAltersEntered :" . print_r($newAlterPrompt->errors);
    				}
    			}
    
    			foreach($study->questions->question as $question){
    				$newQuestion = new Question;
    				$newQuestion->studyId = $newStudy->id;
    				foreach($question->attributes() as $key=>$value){
    					if($key == "id")
    						$oldId = intval($value);
    					if($key == "ordering")
    						$value = intval($value);
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
    					echo "Question: " . print_r($newQuestion->getErrors());
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
    							if($optionkey == "ordering")
    								$val = intval($val);
    							if($optionkey!="key" && $optionkey != "id")
    								$newOption->$optionkey = $val;
    						}
    						if(!$newOption->save())
    							echo "Option: " . print_r($newOption->getErrors());
    						else
    							$newOptionIds[$oldOptionId] = $newOption->id;
    					}
    				}
    			}
    
    			// loop through the questions and correct linked ids
    			$newQuestions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
    			foreach($newQuestions as $newQuestion){
    				if($newQuestion->networkNColorQId != 0)
    					$newQuestion->networkNColorQId = $newQuestionIds[$newQuestion->networkNColorQId];
    				if($newQuestion->networkNSizeQId != 0)
    					$newQuestion->networkNSizeQId = $newQuestionIds[$newQuestion->networkNSizeQId];
    				if($newQuestion->networkEColorQId != 0)
    					$newQuestion->networkEColorQId = $newQuestionIds[$newQuestion->networkEColorQId];
    				if($newQuestion->networkESizeQId != 0)
    					$newQuestion->networkESizeQId = $newQuestionIds[$newQuestion->networkESizeQId];
    				if(is_numeric($newQuestion->listRangeString) && isset($newOptionIds[intval($newQuestion->listRangeString)]))
    					$newQuestion->listRangeString = $newOptionIds[intval($newQuestion->listRangeString)];
    				$newQuestion->save();
    			}
    
    			if($newStudy->multiSessionEgoId != 0 && isset($newQuestionIds[intval($newStudy->multiSessionEgoId)])){
    				$newStudy->multiSessionEgoId = $newQuestionIds[intval($newStudy->multiSessionEgoId)];
    				$newStudy->save();
    			}
    
    			if(count($study->expressions) != 0){
    				foreach($study->expressions->expression as $expression){
    					$newExpression = new Expression;
    					$newExpression->studyId = $newStudy->id;
    					foreach($expression->attributes() as $key=>$value){
    						if($key == "id")
    							$oldExpressionId = intval($value);
    						if($key == "ordering")
    							$value = intval($value);
    						if($key!="key" && $key != "id")
    							$newExpression->$key = $value;
    					}
    					// reference the correct question, since we're not using old ids
    
    					if($newExpression->questionId != "" && isset($newQuestionIds[intval($newExpression->questionId)]))
    						$newExpression->questionId = $newQuestionIds[intval($newExpression->questionId)];
    
    					$newExpression->value = $expression->value;
    					if(!$newExpression->save())
    						echo "Expression: " . print_r($newExpression->getErrors());
    					else
    						$newExpressionIds[$oldExpressionId] = $newExpression->id;
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

        		// loop through questions and relink network params
        		$questions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
        		if (count($questions) > 0) {
        			foreach ($questions as $question) {
            			if ($question->subjectType == "NETWORK") {
            				$params = json_decode(htmlspecialchars_decode($question->networkParams), true);
            				if($params){
            					foreach($params as $k => &$param){
            						if(stristr($param['questionId'], "expression")){
            							list($label, $expressionId) = explode("_", $param['questionId']);
            							if(isset($newExpressionIds[intval($expressionId)]))
            								$expressionId = $newExpressionIds[intval($expressionId)];
            							$param['questionId'] = "expression_" . $expressionId;
            						}else{
            							if(is_numeric($param['questionId']) && isset($newQuestionIds[intval($param['questionId'])]))
            								$param['questionId'] = $newQuestionIds[intval($param['questionId'])];
            							if(count($param['options']) > 0){
            								foreach($param['options'] as &$option){
            									if(isset($newOptionIds[intval($option['id'])]))
            										$option['id'] = $newOptionIds[intval($option['id'])];
            								}
            							}
            						}
            					}
            				}
            				$question->networkParams = json_encode($params);
        				}
                        if(isset($newExpressionIds[$question->answerReasonExpressionId]))
        				    $question->answerReasonExpressionId = $newExpressionIds[$question->answerReasonExpressionId];
        				if(isset($newExpressionIds[$question->networkRelationshipExprId]))
        					$question->networkRelationshipExprId = $newExpressionIds[$question->networkRelationshipExprId];
        				$question->save();
        			}
        		}
    		
    		}else{
                $questions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
                foreach ($questions as $question) {
                    $qIds[$question->title] = $question->id;
                }
        		$options = QuestionOption::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
                foreach ($options as $option) {
                    $oIds[$option->questionId . "-" . $option->name] = $option->id;
                }
        		$expressions = Expression::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
                foreach ($expressions as $expression) {
                    $eIds[$expression->name] = $expression->id;
                }
        		foreach($study->questions->question as $question){
            		$newQuestionIds[intval($question->attributes()['id'])] = $qIds[strval($question->attributes()['title'])];
            		if(isset($question->option)){
                		foreach($question->option as $option){
                            $newOptionIds[intval($option->attributes()['id'])] = $oIds[strval($qIds[strval($question->attributes()['title'])] . "-" .$option->attributes()['name'])];
                        }
                    }
                }
    			if(count($study->expressions) != 0){
    				foreach($study->expressions->expression as $expression){
        				$newExpressionIds[intval($expression->attributes()['id'])] = $eIds[strval($expression->attributes()['name'])];
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
    							"Alter: " . print_r($newAlter->getErrors());
    							die();
    						}else{
    							$newAlterIds[intval($thisAlterId)] = $newAlter->id;
    						}
    					}
    				}
    
    				if(count($interview->notes->note) != 0){
    					foreach($interview->notes->note as $note){
    						$newNote = new Note;
    						foreach($note->attributes() as $key=>$value){
    							if($key!="key" && $key != "id")
    								$newNote->$key = $value;
    						}
    						if(!preg_match("/,/", $newNote->interviewId))
    							$newNote->interviewId = $newInterview->id;
    
    						$newNote->expressionId = $newExpressionIds[intval($newNote->expressionId)];
    						$newNote->alterId = $newAlterIds[intval($newNote->alterId)];
    
    						if(!$newNote->save()){
    							"Note: " . print_r($newNote->errors);
    							die();
    						}
    					}
    				}
    
    				if(count($interview->otherSpecifies->otherSpecify) != 0){
    					foreach($interview->otherSpecifies->otherSpecify as $other){
    						$newOther = new OtherSpecify;
    						foreach($other->attributes() as $key=>$value){
    							if($key != "id")
    								$newOther->$key = $value;
    						}
    						if(!preg_match("/,/", $newOther->interviewId))
    							$newOther->interviewId = $newInterview->id;
    
    						$newOther->optionId = $newOptionIds[intval($newOther->optionId)];
    
    						$newOther->alterId = $newAlterIds[intval($newOther->alterId)];
    
    						if(!$newOther->save()){
    							"OtherSpecify: " . print_r($newOther->errors);
    							die();
    						}
    					}
    				}
    
    				if(count($interview->graphs->graph) != 0){
    					foreach($interview->graphs->graph as $graph){
    						$newGraph = new Graph;
    						foreach($graph->attributes() as $key=>$value){
    							if($key!="key" && $key != "id"){
    								if($key == "params"){
    									$params = json_decode(htmlspecialchars_decode($value), true);
    									if($params){
    										foreach($params as $k => &$param){
    											if(is_numeric($param['questionId']))
    												$param['questionId'] = $newQuestionIds[intval($param['questionId'])];
    											if(count($param['options']) > 0){
    												foreach($param['options'] as &$option){
    													$option['id'] = $newOptionIds[intval($option['id'])];
    												}
    											}
    										}
    									}
    									$value = json_encode($params);
    								}
    								if($key == "nodes"){
    									$nodes = json_decode(htmlspecialchars_decode($value), true);
    									foreach($nodes as $node){
    										$oldNodeId = $node['id'];
    										$node['id'] =  $newAlterIds[intval($node['id'])];
    										$nodes[$node['id']] = $node;
    										unset($nodes[$oldNodeId]);
    									}
    									$value = json_encode($nodes);
    								}
    								$newGraph->$key = $value;
    							}
    						}
    						if(!preg_match("/,/", $newGraph->interviewId))
    							$newGraph->interviewId = $newInterview->id;
    
    						$newGraph->expressionId = $newExpressionIds[intval($newGraph->expressionId)];
    						if(!$newGraph->save()){
    							"Graph: " . print_r($newGraph->errors);
    							die();
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
    
    
    									if($key == "questionId"){
    										$newAnswer->questionId = $newQuestionIds[intval($value)];
    										$oldQId = intval($value);
    									}
    
    									if($key == "answerType")
    										$answerType = $value;
    						}
    
    
							if($answerType == "MULTIPLE_SELECTION" && !in_array($newAnswer->value, array($newStudy->valueRefusal,$newStudy->valueDontKnow,$newStudy->valueLogicalSkip,$newStudy->valueNotYetAnswered))){
								$values = explode(',', $newAnswer->value);
								foreach($values as &$value){
									if(isset($newOptionIds[intval($value)]))
										$value = $newOptionIds[intval($value)];
								}
								$newAnswer->value = implode(',', $values);
							}
    
    
    						$newAnswer->studyId = $newStudy->id;
    						$newAnswer->interviewId = $newInterview->id;
    
    						if(!isset($newQuestionIds[$oldQId]) || !$newQuestionIds[$oldQId])
    							continue;
    
    						if(!$newAnswer->save()){
    							echo $oldQId . "<br>";
    							echo $newQuestionIds[$oldQId]."<br>";
    							print_r($newQuestionIds);
    							print_r($newAnswer);
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
    					echo "AnswerList: " .  print_r($newAnswerList->getErrors());
    			}
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

		header("Content-type: text/xml; charset=utf-8");
		header("Content-Disposition: attachment; filename=".$study->name.".study");
		header("Content-Type: application/force-download");

		echo $study->export($_POST['export']);
	}
}
