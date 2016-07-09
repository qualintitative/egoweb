<?php

class InterviewController extends Controller
{

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$condition = "id != 0";
		if(!Yii::app()->user->isSuperAdmin){
            #OK FOR SQL INJECTION
            if(Yii::app()->user->id)
			    $studies = q("SELECT studyId FROM interviewers WHERE interviewerId = " . Yii::app()->user->id)->queryColumn();
            else
                $studies = false;
			if($studies)
				$condition = "id IN (" . implode(",", $studies) . ")";
			else
				$condition = "id = -1";
		}

		$criteria = array(
			'condition'=>$condition . " AND multiSessionEgoId = 0 AND active = 1",
			'order'=>'id DESC',
		);

		$single = Study::model()->findAll($criteria);

		$criteria = array(
			'condition'=>$condition . " AND multiSessionEgoId <> 0 AND active = 1",
			'order'=>'multiSessionEgoId DESC',
		);

		$multi = Study::model()->findAll($criteria);

		$this->render('index',array(
			'single'=>$single,
			'multi'=>$multi,
		));
	}

	public function actionStudy($id)
	{
		$egoIdQ = q("SELECT * from question where studyId = $id and useAlterListField in ('name','email','id')")->queryRow();
		$restrictions = "";
		if($egoIdQ){
			$participants = q("SELECT " . $egoIdQ['useAlterListField'] . " FROM alterList where interviewerId = " . Yii::app()->user->id)->queryColumn();
			foreach($participants as &$p){
    			if(strlen($p) >= 8)
    			    $p = decrypt($p);
			}
			if($participants){
        		$criteria = array(
        			'condition'=>"questionId = " .$egoIdQ['id'],
        		);
                $answers = Answer::model()->findAll($criteria);
                foreach($answers as $answer){
                    if(in_array($answer->value, $participants))
                        $interviewIds[] = $answer->interviewId;
                }
				if($interviewIds)
					$restrictions = ' and id in (' . implode(",", $interviewIds) . ')';
				else
					$restrictions = ' and id = -1';
			}
		}
        if(Yii::app()->user->isSuperAdmin)
            $restrictions = "";
		$criteria=array(
			'condition'=>'completed > -1 AND studyId = '.$id . $restrictions,
			'order'=>'id DESC',
		);
		$dataProvider=new CActiveDataProvider('Interview',array(
			'criteria'=>$criteria,
		));
		$this->renderPartial('study', array(
			'dataProvider'=>$dataProvider,
			'studyId'=>$id,
		),false,false);
	}

	/**
	 * Main page.
	 */
	public function actionView($id)
	{
        $study = Study::model()->findByPk($id);
        if ($study->multiSessionEgoId)
            $multiIds = q("SELECT studyId FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
        else
            $multiIds = $study->id;
        $this->pageTitle = $study->name;
        $expressions = array();
        $results = Expression::model()->findAllByAttributes(array("studyId"=>$multiIds));
        foreach($results as $result)
            $expressions[$result->id] = mToA($result);
        $questions = array();
        $audio = array();
        if(file_exists(Yii::app()->basePath."/../audio/".$study->id . "/STUDY/ALTERPROMPT.mp3"))
            $audio['ALTERPROMPT'] = "/audio/".$study->id . "/STUDY/ALTERPROMPT.mp3";
        $results = Question::model()->findAllByAttributes(array("studyId"=>$multiIds), array('order'=>'ordering'));
        $ego_questions = array();
        $alter_questions = array();
        $alter_pair_questions = array();
        $network_questions = array();
        foreach($results as $result){
            $questions[$result->id] = mToA($result);
            if(file_exists(Yii::app()->basePath."/../audio/".$study->id . "/PREFACE/" . $result->id . ".mp3"))
                $audio['PREFACE_' . $result->id] = "/audio/".$study->id . "/PREFACE/" . $result->id . ".mp3";
            if(file_exists(Yii::app()->basePath."/../audio/".$study->id . "/" . $result->subjectType . "/" . $result->id . ".mp3"))
                $audio[$result->subjectType . $result->id] = "/audio/".$study->id . "/" . $result->subjectType . "/" . $result->id . ".mp3";

            if($id == $result->studyId){
                if($result->subjectType == "EGO_ID")
                    $ego_id_questions[] = mToA($result);
                if($result->subjectType == "EGO")
                    $ego_questions[] = mToA($result);
                if($result->subjectType == "ALTER")
                    $alter_questions[] = mToA($result);
                if($result->subjectType == "ALTER_PAIR")
                    $alter_pair_questions[] = mToA($result);
                if($result->subjectType == "NETWORK")
                    $network_questions[] = mToA($result);
            }
        }
        $options = array();
        $results = QuestionOption::model()->findAllByAttributes(array("studyId"=>$id));
        foreach($results as $result){
    	    if(file_exists(Yii::app()->basePath."/../audio/". $study->id . "/OPTION/" . $result->id . ".mp3"))
                $audio['OPTION' . $result->id] = "/audio/".$study->id . "/OPTION/" . $result->id . ".mp3";
            $options[$result->questionId][$result->ordering] = mToA($result);
        }
        $answers = array();
        $interviewId = false;
        $interview = false;
        $participantList = array();
        $otherGraphs = array();
        $results = AlterList::model()->findAllByAttributes(array("studyId"=>$id));
        foreach($results as $result){
            if($result->name)
                $participantList['name'][] = $result->name;
            if($result->email)
                $participantList['email'][] = $result->email;
        }
        if(isset($_GET['interviewId'])){
            $interviewId = $_GET['interviewId'];
            $interview = Interview::model()->findByPk($_GET['interviewId']);
    		$interviewIds = Interview::multiInterviewIds($_GET['interviewId'], $study);
    		$prevIds = array();
    		if(is_array($interviewIds))
        		$prevIds = array_diff($interviewIds, array($interviewId));
    		if(is_array($interviewIds)){
    		    $answerList = Answer::model()->findAllByAttributes(array('interviewId'=>$interviewIds));
                foreach($network_questions as $nq){
                    if(!isset($otherGraphs[$nq['ID']]))
                        $otherGraphs[$nq['ID']] = array();
                    foreach($interviewIds as $i_id){
                        if($i_id == $interviewId)
                            continue;
                        $graphId = "";
                        $s = Study::model()->findByPk((int)q("SELECT studyId from interview WHERE id = " . $i_id)->queryScalar());
                        #OK FOR SQL INJECTION
                        $networkExprId = q("SELECT networkRelationshipExprId FROM question WHERE title = '" . $nq['TITLE'] . "' AND studyId = " . $s->id)->queryScalar();
                        #OK FOR SQL INJECTION
                        if($networkExprId)
                            $graphId = q("SELECT id FROM graphs WHERE expressionId = " . $networkExprId  . " AND interviewId = " . $i_id)->queryScalar();
                        if($graphId){
                            $otherGraphs[$nq['ID']][] = array(
                                "interviewId" => $i_id,
                                "expressionId" => $networkExprId,
                                "studyName" => $s->name,
                            );
                        }
                    }
                    //echo '<br><a href="#" onclick="print(' . $networkExprId . ','. $interviewId . ')">' . $study->name . '</a>';
                }
    		}else{
    		    $answerList = Answer::model()->findAllByAttributes(array('interviewId'=>$_GET['interviewId']));
            }
            $alterPrompts = array();
            $results = AlterPrompt::model()->findAllByAttributes(array("studyId"=>$id));
            foreach($results as $result){
                $alterPrompts[$result->afterAltersEntered] = $result->display;
            }
    		foreach($answerList as $answer){
    			if($answer->alterId1 && $answer->alterId2)
    				$array_id = $answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2;
    			else if ($answer->alterId1 && ! $answer->alterId2)
    				$array_id = $answer->questionId . "-" . $answer->alterId1;
    			else
    				$array_id = $answer->questionId;
                $answers[$array_id] = mToA($answer);
    		}
    		$prevAlters = array();
    		foreach($prevIds as $i_id){
    			$criteria = array(
    				'condition'=>"FIND_IN_SET(" . $i_id .", interviewId)",
    				'order'=>'ordering',
    			);
    			$results = Alters::model()->findAll($criteria);
    			foreach($results as $result){
        			$prevAlters[$result->id] = mToA($result);
    			}
            }
    		if(isset($_GET['interviewId']) && $study->fillAlterList){
                #OK FOR SQL INJECTION
                $check = q("SELECT count(id) FROM alters WHERE interviewId = " . $interviewId)->queryScalar();
    			if(!$check){
                    #OK FOR SQL INJECTION
    				$names = q("SELECT name FROM alterList where studyId = " . $study->id)->queryColumn();
    				$count = 0;
    				foreach($names as $name){
    					$alter = new Alters;
    					if(strlen($name) >= 8)
        					$alter->name = decrypt($name);
        				else
        				    continue;
    					$alter->ordering = $count;
    					$alter->interviewId = $interviewId;
    					$alter->save();
    					$count++;
    				}
    			}
    		}
    		$alters = array();
			$criteria = array(
				'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId)",
				'order'=>'ordering',
			);
			$results = Alters::model()->findAll($criteria);
			foreach($results as $result){
    			if(isset($prevAlters[$result->id]))
    			    unset($prevAlters[$result->id]);
    			$alters[$result->id] = mToA($result);
			}
			$graphs = array();
			$results = Graph::model()->findAllByAttributes(array('interviewId'=>$interviewId));
			foreach($results as $result){
    			$graphs[$result->expressionId] = mToA($result);
			}
    		$notes = array();
    		$results = Note::model()->findAllByAttributes(array("interviewId"=>$interviewId));
    		foreach($results as $result){
    			$notes[$result->expressionId][$result->alterId] = $result->notes;
    		}
        }
        $this->render('view', array(
                "study"=>json_encode(mToA($study)),
                "questions"=>json_encode($questions),
                "ego_id_questions"=>json_encode($ego_id_questions),
                "ego_questions"=>json_encode($ego_questions),
                "alter_questions"=>json_encode($alter_questions),
                "alter_pair_questions"=>json_encode($alter_pair_questions),
                "network_questions"=>json_encode($network_questions),
                "no_response_questions"=>json_encode($no_response_questions),
                "expressions"=>json_encode($expressions),
                "options"=>json_encode($options),
                "interviewId" => $interviewId,
                "interview" => json_encode($interview ? mToA($interview) : false),
                "answers"=>json_encode($answers),
                "alterPrompts"=>json_encode($alterPrompts),
                "alters"=>json_encode($alters),
                "prevAlters"=>json_encode($prevAlters),
                "graphs"=>json_encode($graphs),
                "allNotes"=>json_encode($notes),
                "participantList"=>json_encode($participantList),
                "questionList"=>json_encode($study->questionList()),
                "audio"=>json_encode($audio),
                "otherGraphs"=>json_encode($otherGraphs),
            )
        );
	}

	public function actionSave()
	{
        $errors = 0;
        $key = "";
        if(isset($_POST["hashKey"]))
            $key = $_POST["hashKey"];

        $interviewId = null;
		foreach($_POST['Answer'] as $Answer){

            if($Answer['interviewId'])
                $interviewId = $Answer['interviewId'];

            if($interviewId && !isset($answers)){
            	$answers = array();
        		$interviewIds = Interview::multiInterviewIds($interviewId, $study);
        		if(is_array($interviewIds))
        		    $answerList = Answer::model()->findAllByAttributes(array('interviewId'=>$interviewIds));
        		else
        		    $answerList = Answer::model()->findAllByAttributes(array('interviewId'=>$interviewId));
        		foreach($answerList as $answer){
        			if($answer->alterId1 && $answer->alterId2)
        				$answers[$answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2] = $answer;
        			else if ($answer->alterId1 && ! $answer->alterId2)
        				$answers[$answer->questionId . "-" . $answer->alterId1] = $answer;
        			else
        				$answers[$answer->questionId] = $answer;
        		}
            }
			if($Answer['questionType'] == "ALTER")
				$array_id = $Answer['questionId'] . "-" . $Answer['alterId1'];
			else if($Answer['questionType'] == "ALTER_PAIR")
				$array_id = $Answer['questionId'] . "-" . $Answer['alterId1'] . "and" . $Answer['alterId2'];
			else
				$array_id = $Answer['questionId'];

			if($Answer['questionType'] == "EGO_ID" && $Answer['value'] != "" && !$interviewId){
				if(Yii::app()->user->isGuest){
					foreach($_POST['Answer'] as $ego_id){
						$array_id = $ego_id['questionId'];
						$answers[$array_id] = new Answer;
						$answers[$array_id]->attributes = $ego_id;
						if(stristr(Question::getTitle($ego_id['questionId']), 'email')){
							$email = $ego_id['value'];
							$email_id = $array_id;
						}
					}
					if(!$key || ($key && User::hashPassword($email) != $key)){
						//$model[$email_id]->addError('value', 'You do not have the correct email for this survey.');
						$errors++;
						break;
					}
				}
				if($errors == 0){
					if(Yii::app()->user->isGuest && isset($email)){
						$interview = Interview::getInterviewFromEmail($Answer['studyId'], $email);
						if($interview){
							$this->redirect(Yii::app()->createUrl(
								'interview/'.$study->id.'/'.
								$interview->id.'#/'.
								'page/'.$interview->completed
							));
						}
					}
					$interview = new Interview;
					$interview->studyId = $Answer['studyId'];
					if($interview->save()){
    					$randoms = Question::model()->findAllByAttributes(array("answerType"=>"RANDOM_NUMBER", "studyId"=>$Answer['studyId']));
    					foreach($randoms as $q){
        				    $a = $q->id;
                            $answers[$a] = new Answer;
                            $answers[$a]->interviewId = $interview->id;
                            $answers[$a]->studyId = $Answer['studyId'];
                            $answers[$a]->questionType = "EGO_ID";
                            $answers[$a]->answerType = "RANDOM_NUMBER";
                            $answers[$a]->questionId = $q->id;
                            $answers[$a]->skipReason = "NONE";
                            $answers[$a]->value = mt_rand ($q->minLiteral , $q->maxLiteral);
                            $answers[$a]->save();
    					}
						$interviewId = $interview->id;
					}else{
						print_r($interview->errors);
						die();
					}
				}
			}
			if(!isset($answers[$array_id]))
                $answers[$array_id] = new Answer;
			$answers[$array_id]->attributes = $Answer;
			if($interviewId){
				$answers[$array_id]->interviewId = $interviewId;
				$answers[$array_id]->save();
				if(strlen($answers[$array_id]->value) >= 8)
				    $answers[$array_id]->value = decrypt( $answers[$array_id]->value);
				if(strlen($answers[$array_id]->otherSpecifyText) >= 8)
				    $answers[$array_id]->otherSpecifyText = decrypt( $answers[$array_id]->otherSpecifyText);

			}
        }
		$interview = Interview::model()->findByPk((int)$interviewId);
		if($interview &&$interview->completed != -1 && is_numeric($_POST['page'])){
			$interview->completed = (int)$_POST['page'] + 1;
			$interview->save();
		}
		foreach($answers as $index => $answer){
    		$json[$index] = mToA($answer);
		}

		if(isset($_POST['conclusion'])){
			$interview = Interview::model()->findByPk((int)$interviewId);
			$interview->completed = -1;
			$interview->complete_date = time();
			$interview->save();

			if(isset(Yii::app()->params['exportFilePath']) && Yii::app()->params['exportFilePath'])
				$this->exportInterview($interview->id);
		}

		if($errors == 0)
    		echo json_encode($json);
        else
            echo "error";
    }

	public function actionAlter(){
		if(isset($_POST['Alters'])){
            #OK FOR SQL INJECTION
            $params = new stdClass();
            $params->name = ':interviewId';
            $params->value = $_POST['Alters']['interviewId'];
            $params->dataType = PDO::PARAM_INT;

			$studyId = q("SELECT studyId FROM interview WHERE id = :interviewId", array($params))->queryScalar();
			$criteria=array(
				'condition'=>"FIND_IN_SET(" . $_POST['Alters']['interviewId'] .", interviewId)",
				'order'=>'ordering',
			);
			$alters = Alters::model()->findAll($criteria);
			$alterNames = array();
			foreach($alters as $alter){
				$alterNames[] = $alter->name;
			}
			$model = new Alters;
			$model->attributes = $_POST['Alters'];
			if(in_array($_POST['Alters']['name'], $alterNames)){
				$model->addError('name', $_POST['Alters']['name']. ' has already been added!');
			}

            #OK FOR SQL INJECTION
			$study = Study::model()->findByPk((int)$studyId);

			// check to see if pre-defined alters exist.  If they do exist, check name against list
			if($study->useAsAlters){
                #OK FOR SQL INJECTION
				$alterCount = q("SELECT count(id) FROM alterList WHERE studyId = ".$studyId)->queryScalar();
				if($alterCount > 0){
                    #OK FOR SQL INJECTION
                    $params = new stdClass();
                    $params->name = ':name';
                    $params->value = $_POST['Alters']['name'];
                    $params->dataType = PDO::PARAM_STR;
					$nameInList = q('SELECT name FROM alterList WHERE name = :name AND studyId = '. $studyId, array($params))->queryScalar();
					if(!$nameInList && $study->restrictAlters){
						$model->addError('name', $_POST['Alters']['name']. ' is not in our list of participants');
					}
				}
			}

            $foundAlter = false;
			if(isset($study->multiSessionEgoId) && $study->multiSessionEgoId){
                #OK FOR SQL INJECTION
                #OK FOR SQL INJECTION
                $multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
                #OK FOR SQL INJECTION

    			$criteria=array(
    				'condition'=>"interviewId = ". $_POST['Alters']['interviewId']." AND questionId IN (" . implode(",", $multiIds) . ")",
    			);
                $egoValue = Answer::model()->find($criteria);
    			$criteria=array(
    				'condition'=>"questionId IN (" . implode(",", $multiIds) . ")",
    			);

                $otherEgoValues = Answer::model()->findAll($criteria);
                foreach($otherEgoValues as $other){
                    if($other->value == $egoValue->value)
                        $interviewIds[] = $other->interviewId;
                }
				$interviewIds = array_diff(array_unique($interviewIds), array($_POST['Alters']['interviewId']));

                foreach($interviewIds as $iId){
                    $criteria=array(
                        'condition'=>"FIND_IN_SET (" . $iId . ", interviewId) ",
                    );
                    $alters = Alters::model()->findAll($criteria);
                    foreach($alters as $alter){
                        if($alter->name == $_POST['Alters']['name']){
                            $alter->interviewId = $alter->interviewId . ",". $_POST['Alters']['interviewId'];
                            $alter->save();
                            $foundAlter = true;
                        }

                    }
                }
			}
			$criteria=new CDbCriteria;
			$criteria->condition = ('interviewId = '.$_POST['Alters']['interviewId']);
			$criteria->select='count(ordering) AS ordering';
			$row = Alters::model()->find($criteria);
			$model->ordering = $row['ordering'];
			if(!$model->getError('name') && $foundAlter == false)
				$model->save();
			$interviewId = $_POST['Alters']['interviewId'];

			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"afterAltersEntered <= " . Interview::countAlters($interviewId),
				'order'=>'afterAltersEntered DESC',
			);
			$alterPrompt = AlterPrompt::getPrompt($studyId, Interview::countAlters($interviewId));

    		$alters = array();
			$criteria = array(
				'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId)",
				'order'=>'ordering',
			);
			$results = Alters::model()->findAll($criteria);
			foreach($results as $result){
    			$alters[$result->id] = mToA($result);
			}

			echo json_encode($alters);

		}
	}

	public function actionDeletealter()
	{
		if(isset($_POST['Alters'])){
			$model = Alters::model()->findByPk((int)$_POST['Alters']['id']);
			$interviewId = $_POST['Alters']['interviewId'];
			if($model){
				$ordering = $model->ordering;
				if(strstr($model->interviewId, ",")){
					$interviewIds = explode(",", $model->interviewId);
					$interviewIds = array_diff($interviewIds,array($interviewId));
					$model->interviewId = implode(",", $interviewIds);
					$model->save();
				}else{
					$model->delete();
				}
				Alters::sortOrder($ordering, $interviewId);
			}

    		$alters = array();
			$criteria = array(
				'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId)",
				'order'=>'ordering',
			);
			$results = Alters::model()->findAll($criteria);
			foreach($results as $result){
    			$alters[$result->id] = mToA($result);
			}

			echo json_encode($alters);
		}
	}

	/**
	 * Exports study to file (added for LIM)
	 * @param $id ID of interview to be exported
	 */
	protected function exportInterview($id)
	{
		$result = Interview::model()->findByPk($id);
		$study = Study::model()->findByPk($result->studyId);
		$text = $study->export(array($id));
		$file = fopen(Yii::app()->params['exportFilePath'] . Interview::getEgoId($id) . ".study", "w") or die("Unable to open file!");
		fwrite($file, $text);
		fclose($file);
	}
}
