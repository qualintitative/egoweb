<?php
class ImportExportController extends Controller
{

  /**
   * @return array action filters
   */
    public function filters()
    {
        return array(
      'accessControl', // perform access control for CRUD operations
      //'postOnly + delete', // we only allow deletion via POST request
    );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
      array('allow', // allow authenticated user to perform 'create' and 'update' actions
        'users'=>array('@'),
      ),
      array('deny',  // deny all users
        'users'=>array('*'),
      ),
    );
    }

    public function actionImportstudy()
    {
        switch ($_FILES['files']['error'][0]) {
            case UPLOAD_ERR_OK:
                $message = false;;
                break;
            case UPLOAD_ERR_INI_SIZE:
                $message .= ' - file(s) too large.  upload size defined in php.ini exceeded';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message .= ' - file(s) too large.  upload size defined in html exceeded';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message .= ' - file upload was not completed.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message .= ' - zero-length file uploaded.';
                break;
            default:
                $e = print_r($_FILES['files']['error']);
                $message .= ' - internal error #'. $e;
                break;
        }
        if ($message) {
            Yii::log($message);
        }
        if (!is_uploaded_file($_FILES['files']['tmp_name'][0])) { //checks that file is uploaded
            die("Error importing study: " . $message);
		}

        $newInterviewIds = array();
        $newAlterIds = array();
        $newQuestionIds = array();
        $result = User::model()->findAll();
        $users = array();
        foreach($result as $u){
            $users[$u->email] = intval($u->id);
        }
        foreach ($_FILES['files']['tmp_name'] as $tmp_name) {
            $study = simplexml_load_file($tmp_name);
            if (!$study) {
                echo "Improperly formated XML study file";
                die();
            }
            $newStudy = new Study;
            $newOptionIds = array();
            $newExpressionIds = array();
            $newAnswerIds = array();
            $newUserIds = array();
            $merge = false;

            foreach ($study->attributes() as $key=>$value) {
                //		if($newStudy->hasAttribute($key)){
                if ($key == "active") {
                    $value = intval($value);
                }
                if ($key == "id") {
                    $value = null;
                }
                if (in_array($key, array_keys($newStudy->attributes))) {
                    $newStudy->$key = html_entity_decode($value);
                }

                if ($key == "name") {
                    $oldStudy = Study::model()->findByAttributes(array("name"=>$value));
                    if ($oldStudy && !$_POST['newName']) {
                        $merge = true;
                        $newStudy = $oldStudy;
                    }
                }
                //  }
            }
            if (!$merge) {
                foreach ($study as $key=>$value) {
                    if (count($value) == 0 && $key != "answerLists" && $key != "expressions") {
                        $newStudy->$key = html_entity_decode($value);
                    }
                }
                if (isset($_POST['newName']) && $_POST['newName']) {
                    $newStudy->name = $_POST['newName'];
                }

                $newStudy->userId = Yii::app()->user->id;

                if ($newStudy->save()) {
                    $newStudyId = Yii::app()->db->getLastInsertID();
                    $newStudy->id = $newStudyId;
                } else {
                    echo "study: " . print_r($newStudy->attributes);
                    die();
                }

                if ($study->alterLists->alterList) {
                    foreach ($study->alterLists->alterList as $alterList) {
                        $newAlterList = new AlterList;
                        foreach ($alterList->attributes() as $key=>$value) {
                            if (in_array($key, array("ordering", "studyId", "interviewerId"))) {
                                $value = intval($value);
                            }
                            if ($key != "id") {
                                $newAlterList->$key = $value;
                            }
                        }
                        $newAlterList->studyId = $newStudy->id;
                        if (!$newAlterList->save()) {
                            echo "Alter list: $newAlterList->name :" . print_r($newAlterList->errors);
                            die();
                        }
                    }
                }

                foreach ($study->questions->question as $question) {
                    $newQuestion = new Question;
                    $newQuestion->studyId = $newStudy->id;
                    foreach ($question->attributes() as $key=>$value) {
                        if ($key == "id") {
                            $oldId = intval($value);
                        }
                        if ($key == "ordering") {
                            $value = intval($value);
                        }
                        if ($key!="key" && $key != "id" && $key != "networkNShapeQId") {
                            $newQuestion->$key = $value;
                        }
                    }
                    if ($newQuestion->answerType == "SELECTION") {
                        $newQuestion->answerType = "MULTIPLE_SELECTION";
                        $newQuestion->minCheckableBoxes = 1;
                        $newQuestion->maxCheckableBoxes = 1;
                    }
                    $options = 0;
                    foreach ($question as $key=>$value) {
                        if ($key == "option") {
                            $options++;
                        } elseif (count($value) == 0 && $key != "option") {
                            $newQuestion->$key = html_entity_decode($value);
                        }
                    }
                    if (!$newQuestion->save()) {
                        echo "Question: " . print_r($newQuestion->getErrors());
                    } else {
                        $newQuestionIds[$oldId] = $newQuestion->id;
                    }

                    if ($options > 0) {
                        foreach ($question->option as $option) {
                            $newOption = new QuestionOption;
                            $newOption->studyId = $newStudy->id;
                            foreach ($option->attributes() as $optionkey=>$val) {
                                if ($optionkey == "id") {
                                    $oldOptionId = intval($val);
                                }
                                if ($optionkey == "ordering") {
                                    $val = intval($val);
                                }
                                if ($optionkey!="key" && $optionkey != "id") {
                                    $newOption->$optionkey = $val;
                                }
                            }
                            $newOption->questionId = $newQuestion->id;
                            if (!$newOption->save()) {
                                echo "Option: " . print_r($newOption->getErrors());
                            } else {
                                $newOptionIds[$oldOptionId] = $newOption->id;
                            }
                        }
                    }
                }

                // loop through the questions and correct linked ids
                $newQuestions = Question::model()->findAllByAttributes(array('studyId'=>$newStudy->id));
                foreach ($newQuestions as $newQuestion) {
                    if (is_numeric($newQuestion->listRangeString) && isset($newOptionIds[intval($newQuestion->listRangeString)])) {
                        $newQuestion->listRangeString = $newOptionIds[intval($newQuestion->listRangeString)];
                    }
                    $newQuestion->save();
                }


                $newStudy = Study::model()->findbyPk($newStudy->id);
                if ($newStudy->multiSessionEgoId != 0 && isset($newQuestionIds[intval($newStudy->multiSessionEgoId)])) {
                    $newStudy->multiSessionEgoId = $newQuestionIds[intval($newStudy->multiSessionEgoId)];
                    if ($newStudy->save()) {
                        echo $newStudy->multiSessionEgoId;
                        echo "<br>";
                    } else {
                        echo "Multi-ssssion: ";
                        print_r($newStudy->getErrors());
                    }
                }

                if ($study->alterPrompts->alterPrompt) {
                    foreach ($study->alterPrompts->alterPrompt as $alterPrompt) {
                        $newAlterPrompt = new AlterPrompt;
                        foreach ($alterPrompt->attributes() as $key=>$value) {
                            if ($key == "afterAltersEntered") {
                                $value = intval($value);
                            }
                            if ($key != "id") {
                                $newAlterPrompt->$key = $value;
                            }
                            if ($key == "questionId") {
                                if (isset($newQuestionIds[intval($value)])) {
                                    $newAlterPrompt->questionId = $newQuestionIds[intval($value)];
                                } else {
                                    $newAlterPrompt->questionId = 0;
                                }
                            }
                        }
                        $newAlterPrompt->studyId = $newStudy->id;
                        if (!$newAlterPrompt->save()) {
                            print_r($newAlterPrompt->attributes);
                            echo "Alter prompt: $newStudy->id : $newAlterPrompt->afterAltersEntered :" . print_r($newAlterPrompt->errors);
                        }
                    }
                }

                if (count($study->expressions) != 0) {
                    foreach ($study->expressions->expression as $expression) {
                        $newExpression = new Expression;
                        $newExpression->studyId = $newStudy->id;
                        foreach ($expression->attributes() as $key=>$value) {
                            if ($key == "id") {
                                $oldExpressionId = intval($value);
                            }
                            if ($key == "ordering") {
                                $value = intval($value);
                            }
                            if ($key!="key" && $key != "id") {
                                $newExpression->$key = $value;
                            }
                        }
                        // reference the correct question, since we're not using old ids

                        if ($newExpression->questionId != "" && isset($newQuestionIds[intval($newExpression->questionId)])) {
                            $newExpression->questionId = $newQuestionIds[intval($newExpression->questionId)];
                        }

                        //$newExpression->value = $expression->value;
                        if (!$newExpression->save()) {
                            echo "Expression: " . print_r($newExpression->getErrors());
                        } else {
                            $newExpressionIds[$oldExpressionId] = $newExpression->id;
                        }
                    }

                    // second loop to replace old ids in Expression->value
                    foreach ($study->expressions->expression as $expression) {
                        if (!isset($newExpressionIds[$oldExpressionId])) {
                            continue;
                        }
                        foreach ($expression->attributes() as $key=>$value) {
                            if ($key == "id") {
                                $oldExpressionId = intval($value);
                                $newExpression = Expression::model()->findByPk($newExpressionIds[$oldExpressionId]);
                            }
                        }

                        // reference the correct question, since we're not using old ids
                        if ($newExpression->type == "Selection") {
                            $optionIds = explode(',', $newExpression->value);

                            foreach ($optionIds as &$optionId) {
                                if (is_numeric($optionId) && isset($newOptionIds[$optionId])) {
                                    //  echo $newOptionIds[$optionId];
                                    $optionId = $newOptionIds[$optionId];
                                }
                            }
                            $newExpression->value = implode(',', $optionIds);
                        } elseif ($newExpression->type == "Counting") {
                            if (!strstr($newExpression->value, ':')) {
                                continue;
                            }
                            list($times, $expressionIds, $questionIds) = explode(':', $newExpression->value);
                            if ($expressionIds != "") {
                                $expressionIds = explode(',', $expressionIds);
                                foreach ($expressionIds as &$expressionId) {
                                    $expressionId = $newExpressionIds[$expressionId];
                                }
                                $expressionIds = implode(',', $expressionIds);
                            }
                            if ($questionIds != "") {
                                $questionIds = explode(',', $questionIds);
                                foreach ($questionIds as &$questionId) {
                                    if (isset($newQuestionIds[$questionId])) {
                                        $questionId = $newQuestionIds[$questionId];
                                    } else {
                                        $questionId = '';
                                    }
                                }
                                $questionIds = implode(',', $questionIds);
                            }
                            $newExpression->value = $times . ":" .  $expressionIds . ":" . $questionIds;
                        } elseif ($newExpression->type == "Comparison") {
                            list($value, $expressionId) = explode(':', $newExpression->value);
                            $newExpression->value = $value . ":" . $newExpressionIds[$expressionId];
                        } elseif ($newExpression->type == "Compound") {
                            $expressionIds = explode(',', $newExpression->value);
                            foreach ($expressionIds as &$expressionId) {
                                if (is_numeric($expressionId)) {
                                    $expressionId = $newExpressionIds[$expressionId];
                                }
                            }
                            $newExpression->value = implode(',', $expressionIds);
                        } elseif ($newExpression->type == "Name Generator") {
                            if ($newExpression->value != "") {
                                $questionIds = explode(',', $newExpression->value);
                                foreach ($questionIds as &$questionId) {
                                    if (isset($newQuestionIds[$questionId])) {
                                        $questionId = $newQuestionIds[$questionId];
                                    } else {
                                        $questionId = '';
                                    }
                                }
                                $newExpression->value = implode(',', $questionIds);
                            }
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
                        }

                        if (isset($newExpressionIds[$question->answerReasonExpressionId])) {
                            $question->answerReasonExpressionId = $newExpressionIds[$question->answerReasonExpressionId];
                        } else {
                            $question->answerReasonExpressionId = "";
                        }

                        if (isset($newExpressionIds[$question->networkRelationshipExprId])) {
                            $question->networkRelationshipExprId = $newExpressionIds[$question->networkRelationshipExprId];
                        } else {
                            $question->networkRelationshipExprId = "";
                        }

                        if (isset($newExpressionIds[$question->uselfExpression])) {
                            $question->uselfExpression = $newExpressionIds[$question->uselfExpression];
                        } else {
                            $question->uselfExpression = "";
                        }

                        $question->save();
                    }
                }
            } else {
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
                foreach ($study->questions->question as $question) {
                    $q_attributes = $question->attributes();
                    $newQuestionIds[intval($q_attributes['id'])] = $qIds[strval($q_attributes['title'])];
                    if (isset($question->option)) {
                        foreach ($question->option as $option) {
                            $o_attributes = $option->attributes();
                            $newOptionIds[intval($o_attributes['id'])] = $oIds[strval($qIds[strval($q_attributes['title'])] . "-" .$o_attributes['name'])];
                        }
                    }
                }
                if (count($study->expressions) != 0) {
                    foreach ($study->expressions->expression as $expression) {
                        $e_attributes = $expression->attributes();
                        $newExpressionIds[intval($e_attributes['id'])] = $eIds[strval($e_attributes['name'])];
                    }
                }
            }



            if (count($study->interviews) != 0) {
                echo "new study $newStudy->id : ";
                echo $newStudy->multiSessionEgoId;
                foreach ($study->interviews->interview as $interview) {
                    $newInterview = new Interview;
                    foreach ($interview->attributes() as $key=>$value) {
                        if ($key == "id") {
                            $oldInterviewId = $value;
                        }
                        if ($key!="key" && $key != "id") {
                            $newInterview->$key = $value;
                        }
                    }
                    $newInterview->studyId = $newStudy->id;
                    if (!$newInterview->save()) {
                        echo "New interview error: " .  print_r($newInterview->errors);
                    } else {
                        $newInterviewId = Yii::app()->db->getLastInsertID();
                        $newInterviewIds[intval($oldInterviewId)] =  $newInterviewId;
                    }

					if (count($interview->matchedAlters->matchedAlter) > 0) {
						foreach ($interview->matchedAlters->matchedAlter as $match) {
							$newMatch = new MatchedAlters;
							foreach ($match->attributes() as $key=>$value) {
								if (in_array($key, array("ordering", "studyId", "interviewId1", "interviewId2", "alterId1", "alterId2", "userId"))) {
									$value = intval($value);
								}
								if ($key != "id") {
									$newMatch->$key = $value;
								}
							}
							$newMatch->studyId = $newStudy->id;
							if (!$newMatch->save()) {
								echo "Matched Alter Error: $newMatch->interviewId1 :" . $newMatch->interviewId2 .":" .print_r($newMatch->errors) . "end";
								die();
							}
						}
					}
                    if (count($interview->users->user) > 0) {
                        foreach ($interview->users->user as $u) {
                            $userExists = false;
                            foreach ($u->attributes() as $key=>$value) {
                                if($key == "email" && in_array(strval($value), array_keys($users)))
                                    $userExists = strval($value);
                                if ($key == "id")
                                    $oldUserId = intval($value);
                            }
                            if ($userExists != false) {
                                $newUserIds[$oldUserId] = $users[$userExists];
                                echo $users[$userExists];
                                continue;
                            }
							$newUser = new User;
							foreach ($u->attributes() as $key=>$value) {
								if (in_array($key, array("ordering", "studyId", "interviewId1", "interviewId2", "alterId1", "alterId2", "userId"))) {
									$value = intval($value);
								}
								if ($key != "id") {
									$newUser->$key = $value;
                                }
                                if ($key == "email") {
                                    if(strval($value) == "")
                                        $email = false;
                                    else 
                                        $email = strval($value);
                                }
                            }
                            $newUser->confirm = $newUser->password;
                            if($email == false)
                                continue;
							if (!$newUser->save()) {
                                print_r($users);
                                echo $newUser->email;
								echo "User Error: $newUser->id :" . print_r($newUser->errors);
								die();
							}else{
                                $newUserId = Yii::app()->db->getLastInsertID();
                                $newUserIds[$oldUserId] = $newUserId;
                                $users[$email] = $newUserId;
                            }
						}
                    }
                    if (count($interview->alters->alter) != 0) {
                        foreach ($interview->alters->alter as $alter) {
                            $newAlter = new Alters;
                            foreach ($alter->attributes() as $key=>$value) {
                                if ($key == "id") {
                                    $thisAlterId = $value;
                                }
                                if ($key!="key" && $key != "id") {
                                    $newAlter->$key = $value;
                                }
                            }
                            //skip alter if it's already exists in array
                            if(in_array(intval($thisAlterId), array_keys($newAlterIds)))
                                continue;
                            if (!$newAlter->nameGenQIds) {
                                $newAlter->nameGenQIds = 0;
                            }
                            if (!preg_match("/,/", $newAlter->interviewId)) {
                                $newAlter->interviewId = $newInterviewId;
                            }

                            if (!$newAlter->save()) {
                                echo "Alter: ";
                                print_r($newAlter->getErrors());
                                die();
                            } else {
                                $newAlterIds[intval($thisAlterId)] = $newAlter->id;
                            }
                        }
                    }

                    if (count($interview->notes->note) != 0) {
                        foreach ($interview->notes->note as $note) {
                            $newNote = new Note;
                            foreach ($note->attributes() as $key=>$value) {
                                if ($key!="key" && $key != "id") {
                                    $newNote->$key = $value;
                                }
                            }
                            if (!preg_match("/,/", $newNote->interviewId)) {
                                $newNote->interviewId = $newInterviewId;
                            }

                            if (!isset($newExpressionIds[intval($newNote->expressionId)]) || !isset($newAlterIds[intval($newNote->alterId)])) {
                                continue;
                            }

                            $newNote->expressionId = $newExpressionIds[intval($newNote->expressionId)];
                            $newNote->alterId = $newAlterIds[intval($newNote->alterId)];

                            if (!$newNote->save()) {
                                echo "Note: ";
                                print_r($newNote->errors);
                                die();
                            }
                        }
                    }

                    if (count($interview->otherSpecifies->otherSpecify) != 0) {
                        foreach ($interview->otherSpecifies->otherSpecify as $other) {
                            $newOther = new OtherSpecify;
                            foreach ($other->attributes() as $key=>$value) {
                                if ($key != "id") {
                                    $newOther->$key = $value;
                                }
                            }
                            if (!preg_match("/,/", $newOther->interviewId)) {
                                $newOther->interviewId = $newInterview->id;
                            }

                            if (!isset($newOptionIds[intval($newOther->optionId)]) || !$newOptionIds[intval($newOther->optionId)]) {
                                continue;
                            }

                            $newOther->optionId = $newOptionIds[intval($newOther->optionId)];

                            $newOther->alterId = $newAlterIds[intval($newOther->alterId)];

                            if (!$newOther->save()) {
                                echo "OtherSpecify: " . print_r($newOther->errors);
                                print_r($newOptionIds);
                                echo "<br>this id:" . $newOther->optionId;
                                die();
                            }
                        }
                    }

                    if (count($interview->graphs->graph) != 0) {
                        foreach ($interview->graphs->graph as $graph) {
                            $newGraph = new Graph;
                            foreach ($graph->attributes() as $key=>$value) {
                                if ($key!="key" && $key != "id") {
                                    if ($key == "params") {
                                        $params = json_decode(htmlspecialchars_decode($value), true);
                                        if ($params) {
                                            foreach ($params as $k => &$param) {
                                                if (isset($param['questionId']) && is_numeric($param['questionId'])) {
                                                    $param['questionId'] = $newQuestionIds[intval($param['questionId'])];
                                                }
                                                if (isset($param['options']) && count($param['options']) > 0) {
                                                    foreach ($param['options'] as &$option) {
                                                        $option['id'] = $newOptionIds[intval($option['id'])];
                                                    }
                                                }
                                            }
                                        }
                                        $value = json_encode($params);
                                    }
                                    if ($key == "nodes") {
                                        $nodes = json_decode(htmlspecialchars_decode($value), true);
                                        foreach ($nodes as $node) {
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
                            if (!preg_match("/,/", $newGraph->interviewId)) {
                                $newGraph->interviewId = $newInterviewId;
                            }

                            if (isset($newExpressionIds[intval($newGraph->expressionId)])) {
                                $newGraph->expressionId = $newExpressionIds[intval($newGraph->expressionId)];
                            }

                            if (!$newGraph->save()) {
                                print_r($newExpressionIds);
                                print_r($newGraph->expressionId);
                                echo "Graph: " . print_r($newGraph->errors);
                                die();
                            }
                        }
                    }

                    if (count($interview->answers->answer) != 0) {
                        foreach ($interview->answers->answer as $answer) {
                            $newAnswer = new Answer;

                            foreach ($answer->attributes() as $key=>$value) {
                                if ($key!="key" && $key != "id") {
                                    if($key == "value")
                                        $value = htmlspecialchars_decode($value);
                                    $newAnswer->$key = $value;
                                }
                                if ($key == "alterId1" && isset($newAlterIds[intval($value)])) {
                                    $newAnswer->alterId1 = $newAlterIds[intval($value)];
                                }
                                if ($key == "alterId2" && isset($newAlterIds[intval($value)])) {
                                    $newAnswer->alterId2 = $newAlterIds[intval($value)];
                                }


                                if ($key == "questionId") {
                                    $newAnswer->questionId = $newQuestionIds[intval($value)];
                                    $oldQId = intval($value);
                                }

                                if ($key == "answerType") {
                                    $answerType = $value;
                                }
                            }

                            $newAnswer->value = html_entity_decode($newAnswer->value, ENT_QUOTES);

                            if ($answerType == "MULTIPLE_SELECTION" && !in_array($newAnswer->value, array($newStudy->valueRefusal,$newStudy->valueDontKnow,$newStudy->valueLogicalSkip,$newStudy->valueNotYetAnswered))) {
                                $values = explode(',', $newAnswer->value);
                                foreach ($values as &$value) {
                                    if (isset($newOptionIds[intval($value)])) {
                                        $value = $newOptionIds[intval($value)];
                                    }
                                }
                                $newAnswer->value = implode(',', $values);
                            }

                            if ($newAnswer->otherSpecifyText != "") {
                                $otherSpecifies = array();
                                foreach (preg_split('/;;/', $newAnswer->otherSpecifyText) as $otherSpecify) {
                                    if (strstr($otherSpecify, ':')) {
                                        list($optionId, $val) = preg_split('/:/', $otherSpecify);
                                        if (isset($newOptionIds[intval($optionId)])) {
                                            $optionId = $newOptionIds[intval($optionId)];
                                        }
                                        $otherSpecifies[] = $optionId.":".$val;
                                    }
                                }
                                if (count($otherSpecifies) > 0) {
                                    $newAnswer->otherSpecifyText = implode(";;", $otherSpecifies);
                                }
                            }

                            $newAnswer->studyId = $newStudy->id;
                            $newAnswer->interviewId = $newInterviewId;

                            if (!isset($newQuestionIds[$oldQId]) || !$newQuestionIds[$oldQId]) {
                                continue;
                            }

                        
                            if (!$newAnswer->save()) {
                                echo "new answer:";
                                echo $oldQId . "<br>";
                                echo $newQuestionIds[$oldQId]."<br>";
                                print_r($newQuestionIds);
                                print_r($newAnswer->errors);
                                die();
                            }
                        }
                    }
                }
            }

            if (count($study->answerLists) != 0) {
                foreach ($study->answerLists->answerList as $answerList) {
                    $newAnswerList = new AnswerList;
                    $newAnswerList->studyId = $newStudy->id;
                    foreach ($answerList->attributes() as $key=>$value) {
                        if ($key!="key" && $key != "id") {
                            $newAnswerList->$key = $value;
                        }
                    }
                    if (!$newAnswerList->save()) {
                        echo "AnswerList: " .  print_r($newAnswerList->getErrors());
                    }
                }
            }
		}
		foreach ($newAlterIds as $oldId=>$newId) {
			$alter = Alters::model()->findByPk($newId);
			if (preg_match("/,/", $alter->interviewId)) {
				$values = explode(',', $alter->interviewId);
				foreach ($values as &$value) {
					if (isset($newInterviewIds[intval($value)])) {
						$value = $newInterviewIds[intval($value)];
					}
				}
				$alter->interviewId = implode(',', $values);
            }

            if (stristr($alter->nameGenQIds, ",")){
                $nQIds = explode(",", $alter->nameGenQIds);
                foreach($nQIds as &$nQId){
                    $nQId = intval($nQId);
                    if (isset($newQuestionIds[$nQId])) {
                        $nQId = $newQuestionIds[$nQId];
                    }
                }
                $alter->nameGenQIds = implode(",", $nQIds);
            }else{
                if (isset($newQuestionIds[$alter->nameGenQIds])) {
                    $alter->nameGenQIds = $newQuestionIds[$alter->nameGenQIds];
                }
            }

            $nGorder = json_decode($alter->ordering, true);
            $newOrder = array();
            foreach($nGorder as $nQid=>$norder){
                if(isset($newQuestionIds[$nQid]))
                    $newOrder[$newQuestionIds[$nQid]] = $norder;
                else
                    $newOrder[$nQid] = $norder;
            }
            $alter->ordering = json_encode($newOrder);
            $alter->save();
		}
		foreach ($newInterviewIds as $oldId=>$newId) {
			$matches = MatchedAlters::model()->findAllByAttributes(array("interviewId1"=>$oldId));
			if (count($matches) > 0) {
				foreach ($matches as $match) {
					if (isset($newInterviewIds[intval($match->interviewId1)])) {
						$match->interviewId1 = $newInterviewIds[intval($match->interviewId1)];
					}
					if (isset($newInterviewIds[intval($match->interviewId2)])) {
						$match->interviewId2 = $newInterviewIds[intval($match->interviewId2)];
					}
					if (isset($newAlterIds[intval($match->alterId1)])) {
						$match->alterId1 = $newAlterIds[intval($match->alterId1)];
					}
					if (isset($newAlterIds[intval($match->alterId2)])) {
						$match->alterId2 = $newAlterIds[intval($match->alterId2)];
                    }
					if (isset($newUserIds[intval($match->userId)])) {
						$match->userId = $newUserIds[intval($match->userId)];
					}
					$match->save();
				}
            }
            
            $matches = MatchedAlters::model()->findAllByAttributes(array("interviewId2"=>$oldId));
            if (count($matches) > 0) {
				foreach ($matches as $match) {
					if (isset($newInterviewIds[intval($match->interviewId1)])) {
						$match->interviewId1 = $newInterviewIds[intval($match->interviewId1)];
					}
					if (isset($newInterviewIds[intval($match->interviewId2)])) {
						$match->interviewId2 = $newInterviewIds[intval($match->interviewId2)];
					}
					if (isset($newAlterIds[intval($match->alterId1)])) {
						$match->alterId1 = $newAlterIds[intval($match->alterId1)];
					}
					if (isset($newAlterIds[intval($match->alterId2)])) {
						$match->alterId2 = $newAlterIds[intval($match->alterId2)];
                    }
					if (isset($newUserIds[intval($match->userId)])) {
						$match->userId = $newUserIds[intval($match->userId)];
					}
					$match->save();
				}
            }
            
		}
        $this->redirect(array('/authoring/edit','id'=>$newStudy->id));
    }

    public function actionReplicate()
    {
        if ($_POST['name'] == "" || $_POST['studyId'] == "") {
            die("nothing to replicate");
        }
        $study = Study::model()->findByPk((int)$_POST['studyId']);
        $study->name = $_POST['name'];
        $questions = Question::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
        $options = QuestionOption::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
        $expressions = Expression::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
        $alterPrompts = AlterPrompt::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
        $alterLists = AlterList::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));

        $data = Study::replicate($study, $questions, $options, $expressions, $alterPrompts, $alterLists);
        $this->redirect(array('/authoring/edit','id'=>$data['studyId']));
    }

    public function actionIndex()
    {
        if (isset($_POST['Server'])) {
            $server = new Server;
            $server->attributes = $_POST['Server'];
            $server->userId = Yii::app()->user->id;
            $server->save();
            Yii::app()->request->redirect("/importExport");
        }
        if (!Yii::app()->user->isSuperAdmin)
            $result = Server::model()->findAll();
        else
            $result = Server::model()->findAllByAttributes(array("userId"=>Yii::app()->user->id));
        $servers = array();
        foreach ($result as $server) {
            $servers[$server->id] = mToA($server);
        }
        $condition = "id != 0";
        if (!Yii::app()->user->isSuperAdmin) {
            if (Yii::app()->user->id) {
                $criteria = array(
          'condition'=>"interviewerId = " . Yii::app()->user->id,
            );
                $interviewers = Interviewer::model()->findAll($criteria);
                $studies = array();
                foreach ($interviewers as $i) {
                    $studies[] = $i->studyId;
                }
            } else {
                $studies = false;
            }
            if ($studies) {
                $condition = "id IN (" . implode(",", $studies) . ")";
            } else {
                $condition = "id = -1";
            }
        }

        $criteria = array(
      'condition'=>$condition,
      'order'=>'id DESC',
    );

        $studies = Study::model()->findAll($condition);
        $this->render('index', array(
      "servers"=>$servers,
      "studies"=>$studies,
    ));
    }

    public function actionAjaxInterviews($id)
    {
        $study = Study::model()->findByPk($id);
        $interviews = Interview::model()->findAllByAttributes(array('studyId'=>$id));
        $this->renderPartial(
            '_interviews',
            array(
                'study'=>$study,
                'interviews'=>$interviews,
            ),
            false,
            true
        );
    }

    public function actionAjaxexport()
    {
        $interview = Interview::model()->findByPk($_POST['interviewId']);
        if ($interview) {
            $filePath = getcwd() . "/assets/" .  $interview->studyId . "/";
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $columns = array();
            $columns['study'] = Yii::app()->db->schema->getTable("study")->getColumnNames();
            $columns['question'] = Yii::app()->db->schema->getTable("question")->getColumnNames();
            $columns['questionOption'] = Yii::app()->db->schema->getTable("questionOption")->getColumnNames();
            $columns['expression'] = Yii::app()->db->schema->getTable("expression")->getColumnNames();
            $columns['answer'] = Yii::app()->db->schema->getTable("answer")->getColumnNames();
            $columns['alters'] = Yii::app()->db->schema->getTable("alters")->getColumnNames();
            $columns['interview'] = Yii::app()->db->schema->getTable("interview")->getColumnNames();
            $columns['alterList'] = Yii::app()->db->schema->getTable("alterList")->getColumnNames();
            $columns['alterPrompt'] = Yii::app()->db->schema->getTable("alterPrompt")->getColumnNames();
            $columns['alterList'] = Yii::app()->db->schema->getTable("alterList")->getColumnNames();
            $columns['graphs'] = Yii::app()->db->schema->getTable("graphs")->getColumnNames();
			$columns['notes'] = Yii::app()->db->schema->getTable("notes")->getColumnNames();
            $columns['matchedAlters'] = Yii::app()->db->schema->getTable("matchedAlters")->getColumnNames();
            $columns['user'] = Yii::app()->db->schema->getTable("user")->getColumnNames();
            $file = fopen($filePath . "/" .  $interview->id . ".xml", "w") or die("Unable to open file!");
            fclose($file);
            $interview->exportStudyInterview($filePath . "/" .  $interview->id . ".xml", $columns);
            echo "success";
            Yii::app()->end();
        }
        echo "success";
    }

    public function actionExportstudy()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $study = Study::model()->findByPk((int)$_POST['studyId']);

        header("Content-type: text/xml; charset=utf-8");
        header("Content-Disposition: attachment; filename=".$study->name.".study");
        header("Content-Type: application/octet-stream");

        $interviewIds = $_POST['export'];

        $questions = Question::model()->findAllByAttributes(array('studyId'=>$study->id), array('order'=>'subjectType, ordering'));
        $expressions = Expression::model()->findAllByAttributes(array('studyId'=>$study->id));
        //$answerLists = AnswerList::model()->findAllByAttributes(array('studyId'=>$this->id));
        $alterLists = AlterList::model()->findAllByAttributes(array("studyId"=>$study->id));
        $alterPrompts = AlterPrompt::model()->findAllByAttributes(array("studyId"=>$study->id));

        $study->introduction = sanitizeXml($study->introduction);
        $study->egoIdPrompt = sanitizeXml($study->egoIdPrompt);
        $study->alterPrompt = sanitizeXml($study->alterPrompt);
        $study->conclusion = sanitizeXml($study->conclusion);

        if (count($interviewIds) > 0) {
            $interviews = Interview::model()->findAllByAttributes(array("id"=>$interviewIds));
            foreach ($interviews as $result) {
                $interview[$result->id] = $result;
                $answer = Answer::model()->findAllByAttributes(array("interviewId"=>$result->id));
                $answers[$result->id] = $answer;
                $criteria = array(
            'condition'=>"FIND_IN_SET(" . $result->id . ", interviewId)",
          );
                $alter = Alters::model()->findAll($criteria);
                $alters[$result->id] = $alter;
                $graph = Graph::model()->findAllByAttributes(array("interviewId"=>$result->id));
                $graphs[$result->id] = $graph;
                $note = Note::model()->findAllByAttributes(array("interviewId"=>$result->id));
                $notes[$result->id] = $note;
                $other = array();
                //$other = OtherSpecify::model()->findAllByAttributes(array("interviewId"=>$result->id));
                $others[$result->id] = $other;
            }
        }
        $columns = array();
        $columns['study'] = Yii::app()->db->schema->getTable("study")->getColumnNames();
        $columns['question'] = Yii::app()->db->schema->getTable("question")->getColumnNames();
        $columns['questionOption'] = Yii::app()->db->schema->getTable("questionOption")->getColumnNames();
        $columns['expression'] = Yii::app()->db->schema->getTable("expression")->getColumnNames();
        $columns['answer'] = Yii::app()->db->schema->getTable("answer")->getColumnNames();
        $columns['alters'] = Yii::app()->db->schema->getTable("alters")->getColumnNames();
        $columns['interview'] = Yii::app()->db->schema->getTable("interview")->getColumnNames();
        $columns['alterList'] = Yii::app()->db->schema->getTable("alterList")->getColumnNames();
        $columns['alterPrompt'] = Yii::app()->db->schema->getTable("alterPrompt")->getColumnNames();
        $columns['alterList'] = Yii::app()->db->schema->getTable("alterList")->getColumnNames();
        $columns['graphs'] = Yii::app()->db->schema->getTable("graphs")->getColumnNames();
        $columns['notes'] = Yii::app()->db->schema->getTable("notes")->getColumnNames();
        $exclude = array("studyId", "active");
        $x=new XMLWriter();
        $x->openMemory();
        $x->setIndent(true);
        $x->startDocument('1.0', 'UTF-8');
        $x->startElement('study');
        foreach ($columns['study'] as $attr) {
            $x->writeAttribute($attr, $study->$attr);
        }
        $x->writeElement('introduction', $study->introduction);
        $x->writeElement('egoIdPrompt', $study->egoIdPrompt);
        $x->writeElement('alterPrompt', $study->alterPrompt);
        $x->writeElement('conclusion', $study->conclusion);
        if (count($alterLists) > 0) {
            $x->startElement('alterLists');
            foreach ($alterLists as $alterList) {
                $x->startElement('alterList');
                foreach ($columns['alterList'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $alterList->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (count($alterPrompts) > 0) {
            $x->startElement('alterPrompts');
            foreach ($alterPrompts as $alterPrompt) {
                $x->startElement('alterPrompt');
                foreach ($columns['alterPrompt'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $alterPrompt->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (count($questions) > 0) {
            $x->startElement('questions');
            foreach ($questions as $question) {
                $x->startElement('question');
                foreach ($columns['question'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $question->$attr);
                    }
                }
                if ($question->answerType == "SELECTION" || $question->answerType == "MULTIPLE_SELECTION") {
                    $options = QuestionOption::model()->findAllByAttributes(
                        array("studyId"=>$_POST['studyId'], "questionId"=>$question->id)
                    );
                    foreach ($options as $option) {
                        $x->startElement('option');
                        foreach ($columns['questionOption'] as $attr) {
                            if (!in_array($attr, $exclude)) {
                                $x->writeAttribute($attr, $option->$attr);
                            }
                        }
                        $x->endElement();
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (count($expressions) > 0) {
            $x->startElement('expressions');
            foreach ($expressions as $expression) {
                $x->startElement('expression');
                foreach ($columns['expression'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $expression->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        //$x->startElement('interviews');
        //$x->text("");
        $text = $x->outputMemory();
        echo $text."\r\n";
        if (count($interviewIds) > 0) {
            echo "<interviews>\r\n";
            foreach ($interviewIds as $interviewId) {
                $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/". $interviewId . ".xml";
                if (file_exists($filePath)) {
                    echo file_get_contents($filePath);
                    unlink($filePath);
                }
            }
            echo "</interviews>\r\n";
        }
        echo "</study>\r\n";
        exit;
    }

    public function actionSend($id)
    {
        $study = Study::model()->findByPk($id);
        $expressions = array();
        $results = Expression::model()->findAllByAttributes(array("studyId"=>$id));
        foreach ($results as $result) {
            $expressions[] = mToA($result);
        }
        $questions = array();
        $results = Question::model()->findAllByAttributes(array("studyId"=>$id), array('order'=>'ordering'));
        foreach ($results as $result) {
            $questions[] = mToA($result);
        }
        $results = QuestionOption::model()->findAllByAttributes(array("studyId"=>$id));
        foreach ($results as $result) {
            $options[] = mToA($result);
        }
        $participantList = array();
        $results = AlterList::model()->findAllByAttributes(array("studyId"=>$id));
        foreach ($results as $result) {
            if ($result->name) {
                $participantList['name'][] = $result->name;
            }
            if ($result->email) {
                $participantList['email'][] = $result->email;
            }
        }
        $alterPrompts = array();
        $results = AlterPrompt::model()->findAllByAttributes(array("studyId"=>$id));
        foreach ($results as $result) {
            if (!$result->questionId) {
                $result->questionId = 0;
            }
            $alterPrompts[] = mToA($result);
        }
        if (is_array($_POST['export'])) {
            $interviewIds = $_POST['export'];
        } else {
            $interviewIds = array(0);
        }
        if (count($options) == 0) {
            $options = new stdClass();
        }
        $studies = array();
        $interviews = array();
        $answers = array();
        $alters = array();
        $graphs = array();
        $notes = array();
        foreach ($interviewIds as $interviewId) {
            if ($interviewId != 0) {
                $interviewData = mToA(Interview::model()->findByPK($interviewId));
                $interviewData['EGOID'] = Interview::getEgoId($interviewId);
                $interviews[] = $interviewData;

                $results = Answer::model()->findAllByAttributes(array('interviewId'=>$interviewId));
                foreach ($results as $result) {
                    $answers[] = mToA($result);
                }
                $criteria = array(
                      'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId)",
                      'order'=>'ordering',
                  );
                $results = Alters::model()->findAll($criteria);
                foreach ($results as $result) {
                    $alters[] = mToA($result);
                }
                $results = Graph::model()->findAllByAttributes(array('interviewId'=>$interviewId));
                foreach ($results as $result) {
                    $graphs[] = mToA($result);
                }
                $results = Note::model()->findAllByAttributes(array("interviewId"=>$interviewId));
                foreach ($results as $result) {
                    $notes[] = $result->notes;
                }
            }
            $studies[] = array(
             "study"=>mToA($study),
             "questions"=>$questions,
             "expressions"=>$expressions,
             "questionOptions"=>$options,
             "alterPrompts"=>$alterPrompts,
             "participantList"=>$participantList,
             "interviews" => $interviews,
             "answers"=>$answers,
             "alters"=>$alters,
             "graphs"=>$graphs,
             "notes"=>$notes,
         );
        }
        if (count($alters) == 0) {
            $alters = new stdClass();
        }
        if (count($studies) > 1) {
            $data = $studies;
        } else {
            $data = $studies[0];
        }
        echo json_encode($data);
    }

    public function actionDeleteserver()
    {
        if (isset($_POST['serverId'])) {
            $server = Server::model()->findByPK($_POST['serverId']);
            if ($server) {
                $server->delete();
                echo "success";
            } else {
                echo "fail";
            }
        } else {
            echo "fail";
        }
    }
}
