<?php

class DataController extends Controller
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
				'actions'=>array('index', 'exportego', 'savenote', 'noteexists','exportalterpair', 'exportalterlist', 'exportother', 'visualize', 'study', 'ajaxAdjacencies', 'exportinterview' , "savematch" , "unmatch", "edit"),
				'users'=>array('@'),
			),
			array('allow',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionEdit($id)
    {
        $interview = Interview::model()->findByPk(array("id"=>$id));
        $interview->completed = 0;
        $interview->save();
		Yii::app()->request->redirect(Yii::app()->request->urlReferrer);
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
			'condition'=>'studyId = '.$id . $restrictions,
			'order'=>'id DESC',
		);

        $interviews = Interview::model()->findAll($criteria);
        $study = Study::model()->findByPk((int)$id);
        $questionIds = array();
        $questions = Question::model()->findAllByAttributes(array("subjectType"=>"ALTER_PAIR", "studyId"=>$id));
		foreach($questions as $question)
			$questionIds[] = $question->id;
        $expressions = array();
        if(count($questionIds) > 0){
            $questionIds = implode(",", $questionIds);
            $criteria = array(
                'condition'=>"studyId = " . $study->id ." AND questionId in ($questionIds)",
            );
            $expressions = CHtml::listData(
                Expression::model()->findAll($criteria),
                'id',
                function($post) {return CHtml::encode(substr($post->name,0,40));}
            );
        }
        $this->render('study', array(
            'study'=>$study,
            'interviews'=>$interviews,
            'expressions'=>$expressions,
        ));
    }

    public function actionVisualize()
    {
        $graphs = array();
        if(isset($_GET['interviewId'])){
            #OK FOR SQL INJECTION
            $params = new stdClass();
            $params->name = ':id';
            $params->value = $_GET["interviewId"];
            $params->dataType = PDO::PARAM_INT;

            $studyId = q("SELECT studyId FROM interview WHERE id = :id",array($params))->queryScalar();
            $params->value = $studyId;

            if( !$studyId ){
                echo "No studyId found for interviewId = ".$_GET['interviewId'];
                return;
            }

            #OK FOR SQL INJECTION
            $questionIds = q("SELECT id FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = :id",array($params))->queryColumn();

            $questionIds = implode(",", $questionIds);
            if(!$questionIds)
                $questionIds = 0;
            $alter_pair_expression_ids = q("SELECT id FROM expression WHERE studyId = :id AND questionId in (" . $questionIds . ")",array($params))->queryColumn();

            if (count($alter_pair_expression_ids) < 1 ) {
                echo "NO ALTER PAIR EXPRESSION IDS FOUND FOR QUESTION IDS ".(string)$questionIds;
                $alter_pair_expressions = array();
            }
            else{
                $all_expression_ids = $alter_pair_expression_ids;
                foreach($alter_pair_expression_ids as $id){
                    #OK FOR SQL INJECTION
                    $all_expression_ids = array_merge(q("SELECT id FROM expression WHERE FIND_IN_SET($id, value)")->queryColumn(),$all_expression_ids);
                }
                #OK FOR SQL INJECTION
                $alter_pair_expressions = q("SELECT * FROM expression WHERE id in (" . implode(",",$all_expression_ids) . ")")->queryAll();
            }

            if(isset($_GET['print'])){
                $this->renderPartial('print',
                    array(
                        'graphs'=>$graphs,
                        'studyId'=>$studyId,
                        'alter_pair_expressions'=> $alter_pair_expressions,
                        'interviewId'=>$_GET['interviewId'],
                    ), false, true
                );
            }else{
                $this->render('visualize',
                    array(
                        'graphs'=>$graphs,
                        'studyId'=>$studyId,
                        'alter_pair_expressions'=> $alter_pair_expressions,
                        'interviewId'=>$_GET['interviewId'],
                    )
                );

            }

        }
    }

    public function actionMatching()
    {
        if(count($_POST['export']) < 2)
            die("You must select at least 2 interviews");

        foreach($_POST['export'] as $key=>$value){
            $interviewIds[] = $key;
        }
        arsort($interviewIds);
        $interview1 = Interview::model()->findByPK($interviewIds[0]);
        $interview2 = Interview::model()->findByPK($interviewIds[1]);
        $study = Study::model()->findByPk($interview1->studyId);
		$criteria = array(
			'condition'=>"FIND_IN_SET(" . $interview1->id . ", interviewId)",
		);
		$result = Alters::model()->findAll($criteria);
		foreach($result as $alter){
    		$alters1[$alter->id] = $alter->name;
		}
		$criteria = array(
			'condition'=>"FIND_IN_SET(" . $interview2->id . ", interviewId)",
		);
		$result = Alters::model()->findAll($criteria);


		foreach($result as $alter){
    		$alters2[$alter->id] = $alter->name;
		}
        $criteria = array(
			'condition'=>"questionType = 'ALTER' AND interviewId in (" . $interview1->id . ", " . $interview2->id  . ")",
		);
        $result = Answer::model()->findAll($criteria);
		foreach($result as $answer){
    		if($answer->answerType == "MULTIPLE_SELECTION"){
                    $optionIds = explode(",", $answer->value);
                    $answer->value = "";
                    $answerArray = array();
                    foreach  ($optionIds as $optionId)
                    {
                        $option = QuestionOption::model()->findbyPk($optionId);
                        if ($option)
                        {
                            $criteria=new CDbCriteria;
                            $criteria=array(
                                'condition'=>"optionId = " . $option->id . " AND interviewId in (".$answer->interviewId.")",
                            );
                            $otherSpecify = OtherSpecify::model()->find($criteria);
                            if ($otherSpecify)
                                $answerArray[] = $option->name . " (\"" . $otherSpecify->value . "\")";
                            else
                                $answerArray[] = $option->name;
                        }
                    }
                    $answer->value = implode("; ", $answerArray);

    		}
            $answers[$answer->questionId][$answer->alterId1] = $answer->value;
		}
        $result = Question::model()->findAllByAttributes(array("subjectType"=>"ALTER", "studyId"=>$interview1->studyId));
        foreach($result as $question){
            $questions[$question->id] = $question->title;
            $prompts[$question->id] = substr($question->prompt,0,80);
        }
		$this->render('matching', array(
			'interview1'=>$interview1,
			'alters1'=>$alters1,
			'interview2'=>$interview2,
			'alters2'=>$alters2,
			'answers'=>$answers,
			'questions'=>$questions,
			'prompts'=>$prompts,
			'study'=>$study
		));
    }

	public function actionSavematch()
	{
    	if(isset($_POST)){
        	$match = new MatchedAlters;
        	$match->attributes = $_POST;
        	if($match->save())
                echo "<button class='btn btn-xs btn-danger unMatch-" . $_POST['alterId1'] . "' onclick='unMatch(" . $_POST['alterId1'] . ", " . $_POST['alterId2'] . ")'>Unmatch</button>";
            else
                print_r($match->errors);

    	}
    }

	public function actionUnmatch()
    {
        if(isset($_POST)){
            $match = MatchedAlters::model()->findByAttributes(array("alterId1"=>$_POST['alterId1'], "alterId2"=>$_POST['alterId2']));
            if($match)
                $match->delete();
        }
    }

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
			'condition'=>$condition,
			'order'=>'id DESC',
		);

        $studies = Study::model()->findAll($condition);

		$this->render('index', array(
			'studies'=>$studies,
		));
	}

	public function actionExportego()
	{
		if(!isset($_POST['studyId']) || $_POST['studyId'] == "")
			die("nothing to export");

		if(isset($_POST['expressionId']))
			$expressionId = $_POST['expressionId'];
		else
			$expressionId = '';

        #OK FOR SQL INJECTION
		$study = Study::model()->findByPk((int)$_POST['studyId']);
        #OK FOR SQL INJECTION
        $optionsRaw = q("SELECT * FROM questionOption WHERE studyId = " . $study->id)->queryAll();

		// create an array with option ID as key
		$options = array();
		foreach ($optionsRaw as $option){
			$options[$option['id']] = $option['value'];
		}

		// fetch questions
        #OK FOR SQL INJECTION
		$ego_id_questions = q("SELECT * FROM question WHERE subjectType = 'EGO_ID' AND studyId = " . $study->id . " ORDER BY ordering")->queryAll();
        #OK FOR SQL INJECTION
        $ego_questions = q("SELECT * FROM question WHERE subjectType = 'EGO' AND studyId = " . $study->id . " ORDER BY ordering")->queryAll();
        #OK FOR SQL INJECTION
        $alter_questions = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND studyId = " . $study->id . " ORDER BY ordering")->queryAll();

        $criteria=new CDbCriteria;
        $criteria->condition = ("studyId = $study->id and subjectType = 'NETWORK'");
        $criteria->order = "ordering";
        $network_questions = Question::model()->findAll($criteria);

		// start generating export file
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".seoString($study->name)."-ego-alter-data".".csv");
		header("Content-Type: application/force-download");

		$headers = array();
		$headers[] = 'Interview ID';
		$headers[] = "EgoID";
		$headers[] = 'Start Time';
		$headers[] = 'End Time';
		foreach ($ego_id_questions as $question){
			$headers[] = $question['title'];
		}
		foreach ($ego_questions as $question){
			$headers[] = $question['title'];
		}
		$headers[] = "Alter Number";
		$headers[] = "Alter Name";
		foreach ($alter_questions as $question){
			$headers[] = $question['title'];
		}
		foreach ($network_questions as $question){
			$headers[] = $question->title;
		}
		if($expressionId){
			$headers[] = "Density";
			$headers[] = "Max Degree Value";
			$headers[] = "Max Betweenness Value";
			$headers[] = "Max Eigenvector Value";
			$headers[] = "Degree Centralization";
			$headers[] = "Betweenness Centralization";
			$headers[] = "Components";
			$headers[] = "Dyads";
			$headers[] = "Isolates";
			$headers[] = "Degree";
			$headers[] = "Betweenness";
			$headers[] = "Eigenvector";
		}

        $interviewIds = array();
        foreach($_POST['export'] as $key=>$value){
            $interviewIds[] = $key;
        }
		echo implode(',', $headers) . "\n";

		foreach ($interviewIds as $interviewId){
    		$filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/". $interviewId . ".csv";
    		  if (file_exists($filePath)) {
                echo file_get_contents($filePath);
                unlink($filePath);
            }
		}
		Yii::app()->end();

	}

    public function actionExportinterview()
    {
        if (!isset($_POST['studyId']))
            die("no study selected");

        $filePath = getcwd()."/assets/".$_POST['studyId'];
        if(file_exists($filePath . "/" . $_POST['interviewId'] . ".csv")){
            echo "success";
            Yii::app()->end();
        }

        if (!is_dir($filePath))
            mkdir($filePath, 0777, true);

        $interview = Interview::model()->findByPk($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . ".csv", "w") or die("Unable to open file!");
            $interview->exportEgoAlterData($file);
	    	//fwrite($file, $text);
    		echo "success";
    		Yii::app()->end();
        }
        echo "fail";
    }

	public function actionExportalterpair()
	{
		if(!isset($_POST['studyId']) || $_POST['studyId'] == "")
			die("nothing to export");

		$study = Study::model()->findByPk((int)$_POST['studyId']);
        #OK FOR SQL INJECTION
		//$optionsRaw = q("SELECT * FROM questionOption WHERE studyId = " . $study->id)->queryAll();
		$optionsRaw = QuestionOption::model()->findAllByAttributes(array('studyId'=>$study->id));
		// create an array with option ID as key
		$options = array();
		foreach ($optionsRaw as $option){
			$options[$option->id] = $option->value;
		}

        #OK FOR SQL INJECTION
		$alter_pair_questions = q("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = " . $study->id . " ORDER BY ordering")->queryAll();
        #OK FOR SQL INJECTION
        $alterCount = q("SELECT count(id) FROM `alterList` WHERE studyId = " . $study->id)->queryScalar();
		if($alterCount > 0)
			$idNumber = "Id";
		else
			$idNumber = "Number";

		// start generating export file
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".seoString($study->name)."-alter-pair-data".".csv");
		header("Content-Type: application/force-download");
		$headers = array();
		$headers[] = 'Interview ID';
		$headers[] = 'EgoID';
		$headers[] = "Alter 1 " . $idNumber;
		$headers[] = "Alter 1 Name";
		$headers[] = "Alter 2 " . $idNumber;
		$headers[] = "Alter 2 Name";
		foreach ($alter_pair_questions as $question){
			$headers[] = $question['title'];
		}
		echo implode(',', $headers) . "\n";

		$interviews = Interview::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		foreach ($interviews as $interview){
			if(!isset($_POST['export'][$interview->id]))
				continue;
            #OK FOR SQL INJECTION
			$alters = Alters::model()->findAll(array('order'=>'id', 'condition'=>'FIND_IN_SET(:x, interviewId)', 'params'=>array(':x'=>$interview->id)));
			//$alterNames = AlterList::model()->findAllByAttributes(array('interviewId'=>$interview->id));

			$i = 1;
			$alterNum = array();
			foreach($alters as $alter){
				$alterNum[$alter->id] = $i;
				$i++;
			}
			$alters2 = $alters;
			foreach ($alters as $alter){
				array_shift($alters2);
				foreach ($alters2 as $alter2){
					$answers = array();
                    #OK FOR SQL INJECTION
					$realId1 = q("SELECT id FROM alterList WHERE studyId = " . $study->id . " AND name = '" . addslashes($alter['name']) . "'")->queryScalar();
                    #OK FOR SQL INJECTION
                    $realId2 = q("SELECT id FROM alterList WHERE studyId = " . $study->id . " AND name = '" . addslashes($alter2['name']) . "'")->queryScalar();
					$answers[] = $interview->id;
					$answers[] = Interview::getEgoId($interview->id);
					//if(is_numeric($realId1))
					//	$answers[] = $realId1;
					//else
						$answers[] = $alterNum[$alter->id];
					$answers[] = str_replace(",", ";", $alter->name);
					if(is_numeric($realId2))
						$answers[] = $realId2;
					else
						$answers[] = $alterNum[$alter2->id];
					$answers[] = $alter2->name;
					foreach ($alter_pair_questions as $question){
                        #OK FOR SQL INJECTION
						$answer = decrypt(q("SELECT value FROM answer WHERE interviewId = " . $interview->id . " AND questionId = " . $question['id'] . " AND alterId1 = " . $alter->id . " AND alterId2 = " . $alter2->id)->queryScalar());
                        #OK FOR SQL INJECTION
                        $skipReason =  q("SELECT skipReason FROM answer WHERE interviewId = " . $interview->id . " AND questionId = " . $question['id'] . " AND alterId1 = " . $alter->id . " AND alterId2 = " . $alter2->id)->queryScalar();
						if($answer != "" && $skipReason == "NONE"){
							if($question['answerType'] == "SELECTION"){
								$answers[] = $options[$answer];
							}else if($question['answerType'] == "MULTIPLE_SELECTION"){
								$optionIds = explode(',', $answer);
								$list = array();
								foreach($optionIds as $optionId){
									if(isset($options[$optionId]))
									$list[] = $options[$optionId];
								}
								if(count($list) == 0)
									$answers[] = $study->valueNotYetAnswered;
								else
									$answers[] = implode('; ', $list);
							}else{
								$answers[] = $answer;
							}
						} else if (!$answer && ($skipReason == "DONT_KNOW" || $skipReason == "REFUSE")) {
							if($skipReason == "DONT_KNOW")
								$answers[] = $study->valueDontKnow;
							else
								$answers[] = $study->valueRefusal;
						}
					}
					echo implode(',', $answers) . "\n";
					flush();
				}
			}
		}
		Yii::app()->end();
	}

	public function actionOldexportother()
	{
		if(!isset($_POST['studyId']) || $_POST['studyId'] == "")
			die("nothing to export");

		$study = Study::model()->findByPk((int)$_POST['studyId']);
        #OK FOR SQL INJECTION
		$optionsRaw = q("SELECT * FROM questionOption WHERE studyId = " . $study->id)->queryAll();

		// create an array with option ID as key
		$options = array();
		foreach ($optionsRaw as $option){
			$options[$option['id']] = $option['value'];
		}

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".seoString($study->name)."-other-specify-data".".csv");
		header("Content-Type: application/force-download");
		$headers = array();
		$headers[] = 'INTERVIEW ID';
		$headers[] = "EGO ID";
		$headers[] = "QUESTION";
		$headers[] = "ALTER ID";
		$headers[] = "RESPONSE";
		echo implode(',', $headers) . "\n";

        #OK FOR SQL INJECTION
		$other_qs = q("SELECT * FROM question WHERE otherSpecify = 1 AND studyId = ".$study->id)->queryAll();
		$interviews = Interview::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));

		foreach ($interviews as $interview){
			if(!isset($_POST['export'][$interview->id]))
				continue;
			foreach($other_qs as $question){
				$answer = array();
				if($question['subjectType'] == "ALTER"){
					$alters = Alters::model()->findAllByAttributes(array('interviewId'=>$interview->id));
					foreach($alters as $alter){
                        #OK FOR SQL INJECTION
						$response = q("SELECT otherSpecifyText FROM answer WHERE questionId = " . $question['id'] . " AND interviewId = " . $interview->id . "AND alterId1 = " . $alter->id)->queryScalar();
						$responses = array();
						foreach(preg_split('/;;/', $response) as $other){
					    	if($other && strstr($other, ':')){
						    	list($key, $val) = preg_split('/:/', $other);
						    	$responses[] = $options[$key] . ":" . '"'.$val.'"';
						    }
						}
						if(count($responses) > 0)
							$response = implode(";; ", $responses);
						$answer[] = $interview->id;
						$answer[] = Interview::getEgoId($interview->id);
						$answer[] = $question['title'];
						if($alter->name!="")
						         $answer[] = decrypt($alter->name);
						//$answer[] = $alter->name;
						if($response!="")
						         $answer[] = decrypt($response);
						//$answer[] = $response;
						echo implode(',', $answer) . "\n";
						flush();
					}
				}else{
                    #OK FOR SQL INJECTION
					$response = q("SELECT otherSpecifyText FROM answer WHERE questionId = " . $question['id'] . " AND interviewId = " . $interview->id)->queryScalar();
					$responses = array();
					foreach(preg_split('/;;/', $response) as $other){
					    if($other && strstr($other, ':')){
					    	list($key, $val) = preg_split('/:/', $other);
					    	$responses[] = $options[$key] . ":" . '"'.$val.'"';
					    }
					}
					if(count($responses) > 0)
						$response = implode("; ", $responses);
					$answer[] = $interview->id;
					$answer[] = Interview::getRespondant($interview->id);
					$answer[] = $question['title'];
					$answer[] = "";
						if($response!="")
						         $answer[] = decrypt($response);
					//$answer[] = $response;
					echo implode(',', $answer) . "\n";
					flush();
				}
			}
		}
		Yii::app()->end();
	}

	public function actionExportother()
	{
		if(!isset($_POST['studyId']) || $_POST['studyId'] == "")
			die("nothing to export");

		$study = Study::model()->findByPk((int)$_POST['studyId']);

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".seoString($study->name)."-other-specify-data".".csv");
		header("Content-Type: application/force-download");
		$headers = array();
		$headers[] = 'INTERVIEW ID';
		$headers[] = "EGO ID";
		$headers[] = "QUESTION";
		$headers[] = "ALTER ID";
		$headers[] = "RESPONSE";
		echo implode(',', $headers) . "\n";

        #OK FOR SQL INJECTION
		$options = QuestionOption::model()->findAllByAttributes(array("otherSpecify"=>true, "studyId"=>$study->id));
		if(!$options)
		    die();
		foreach($options as $option){
    		$other_options[$option->id] = $option;
    		if(!isset($other_qs[$option->questionId]))
    		    $other_qs[$option->questionId] = Question::model()->findByPk($option->questionId);
		}
		$interviews = Interview::model()->findAllByAttributes(array('studyId'=>$_POST['studyId']));
		foreach ($interviews as $interview){
			if(!isset($_POST['export'][$interview->id]))
				continue;
			foreach($other_qs as $question){
    			    $answers = array();
					$answerList = Answer::model()->findAllByAttributes(array('interviewId'=>$interview->id));
					foreach($answerList as $a){
						if($a->alterId1 && $a->alterId2)
							$answers[$a->questionId . "-" . $a->alterId1 . "and" . $a->alterId2] = $a;
						else if ($a->alterId1 && ! $a->alterId2)
							$answers[$a->questionId . "-" . $a->alterId1] = $a;
						else
							$answers[$a->questionId] = $a;
					}
				if($question->subjectType == "ALTER"){
					$alters = Alters::model()->findAllByAttributes(array('interviewId'=>$interview->id));
					foreach($alters as $alter){
    					$answer = array();
                        #OK FOR SQL INJECTION
                        /*
						$response = q("SELECT otherSpecifyText FROM answer WHERE questionId = " . $question['id'] . " AND interviewId = " . $interview->id . "AND alterId1 = " . $alter->id)->queryScalar();
						$responses = array();
						foreach(preg_split('/;;/', $response) as $other){
					    	if($other && strstr($other, ':')){
						    	list($key, $val) = preg_split('/:/', $other);
						    	$responses[] = $options[$key] . ":" . '"'.$val.'"';
						    }
						}
						if(count($responses) > 0)
							$response = implode(";; ", $responses);
                        */

                        $optionIds = explode(",", $answers[$question->id . "-" . $alter->id]->value);
                        $answerArray = array();
                        foreach  ($optionIds as $optionId)
                        {
                            if (isset($other_options[$optionId])) {
                                $otherSpecify = OtherSpecify::model()->findByAttributes(array("optionId"=>$optionId, "interviewId"=>$interview->id, "alterId"=>$alter->id));
                                if ($otherSpecify)
                                    $answerArray[] = $other_options[$optionId]->name . " (\"" . $otherSpecify->value . "\")";
                                else
                                    $answerArray[] = $other_options[$optionId]->name;
                            }
                        }

						$answer[] = $interview->id;
						$answer[] = Interview::getEgoId($interview->id);
						$answer[] = $question->title;
						$answer[] = $alter->name;
                        $answer[] = implode("; ", $answerArray);

						echo implode(',', $answer) . "\n";
						flush();
					}
				} else {
    				$answer = array();
                    $optionIds = explode(",", $answers[$question->id]->value);
                    $answerArray = array();
                    foreach  ($optionIds as $optionId)
                    {
                        if (isset($other_options[$optionId])) {
                            $otherSpecify = OtherSpecify::model()->findByAttributes(array("optionId"=>$optionId, "interviewId"=>$interview->id));
                            if ($otherSpecify)
                                $answerArray[] = $other_options[$optionId]->name . " (\"" . $otherSpecify->value . "\")";
                            else
                                $answerArray[] = $other_options[$optionId]->name;
                        }
                    }

					$answer[] = $interview->id;
					$answer[] = Interview::getRespondant($interview->id);
					$answer[] = $question->title;
					$answer[] = "";
                    $answer[] = implode("; ", $answerArray);

					echo implode(',', $answer) . "\n";
					flush();
				}
			}
		}
		Yii::app()->end();
	}

	public function actionExportalterlist()
	{
		if(!isset($_POST['studyId']) || $_POST['studyId'] == "")
			die("nothing to export");

		$study = Study::model()->findByPk((int)$_POST['studyId']);
        #OK FOR SQL INJECTION
		$alters = AlterList::model()->findAllByAttributes(array("studyId"=>$study->id));

		// start generating export file
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".seoString($study->name)."-predefined-alters".".csv");
		header("Content-Type: application/force-download");

		$headers = array();
		$headers[] = 'Study ID';
		$headers[] = "Alter ID";
		$headers[] = "Alter Name";
		$headers[] = "Alter Email";
		$headers[] = "Link With Key";
		echo implode(',', $headers) . "\n";

        $ego_id = Question::model()->findByAttributes(array("studyId"=>$study->id, "subjectType"=>"EGO_ID", "useAlterListField"=>array("name", "email")));

		foreach($alters as $alter){
			$row = array();
			if($ego_id->useAlterListField == "name")
    			$key = User::hashPassword($alter->name);
			else if($ego_id->useAlterListField == "email")
    			$key = User::hashPassword($alter->email);
			else if($ego_id->useAlterListField == "id")
    			$key = User::hashPassword($alter->id);
            else
                $key = "";
			$row[] = $study->id;
			$row[] = $alter->id;
			$row[] = $alter->name;
			$row[] = $alter->email;
			$row[] =  Yii::app()->getBaseUrl(true) . "/interview/".$study->id."#/page/0/".$key;
			echo implode(',', $row) . "\n";
		}
		Yii::app()->end();
	}

    public function actionSavegraph()
    {
        if($_POST['Graph']){
            $graph = Graph::model()->findByAttributes(array("interviewId"=>$_POST['Graph']['interviewId'],"expressionId"=>$_POST['Graph']['expressionId']));
            if(!$graph)
                $graph = new Graph;
            $graph->attributes = $_POST['Graph'];
            if($graph->save()){
                //echo "success";
                $graphs = array();
    			$results = Graph::model()->findAllByAttributes(array('interviewId'=>$_POST['Graph']['interviewId']));
    			foreach($results as $result){
        			$graphs[$result->expressionId] = mToA($result);
    			}
                echo json_encode($graphs);
                die();
                //$url =  "graphId=" . $graph->id . "&interviewId=" . $graph->interviewId . "&expressionId=".$graph->expressionId."&params=".urlencode($graph->params);
                //Yii::app()->request->redirect($this->createUrl("/data/visualize?" . $url));
            }
        }
    }

	public function actionDeletegraph()
	{
		if(isset($_GET['id'])){
			$graph = Graph::model()->findByPk($_GET['id']);
			if($graph)
				$graph->delete();
		}
	}

	public function actionGetnote()
	{
		if(isset($_GET['interviewId']) && isset($_GET['expressionId']) && isset($_GET['alterId'])){
			$model = Note::model()->findByAttributes(array(
				'interviewId' => (int)$_GET['interviewId'],
				'expressionId' => (int)$_GET['expressionId'],
				'alterId' => $_GET['alterId']
			));
			if(!$model){
				$model = new Note;
				$model->interviewId = $_GET['interviewId'];
				$model->expressionId = $_GET['expressionId'];
				$model->alterId = $_GET['alterId'];
			}
			$this->renderPartial('_form_note', array('model'=>$model, 'ajax'=>true), false, false);
		}
	}

	public function actionSavenote()
	{
		if(isset($_POST['Note'])){
			$new = false;
			if($_POST['Note']['id']){
				$note = Note::model()->findByPk((int)$_POST['Note']['id']);
			}else{
				$note = new Note;
				$new = true;
			}
			$note->attributes = $_POST['Note'];
			if(!$note->save())
				print_r($note->errors);

			echo $note->alterId;
		}
	}

	public function actionDeletenote()
	{
		if(isset($_POST['Note'])){

			$note = Note::model()->findByPk((int)$_POST['Note']['id']);
			$alterId = $note->alterId;
			if($note){
				$note->delete();
				echo $alterId;
			}
		}
	}

	public function actionDeleteinterviews(){
		if(!isset($_POST['export']))
			return false;
		foreach($_POST['export'] as $interviewId=>$selected){
			if($selected){
				$model = Interview::model()->findByPk((int)$interviewId);
				if($model){
					$answers = Answer::model()->findAllByAttributes(array("interviewId"=>$interviewId));
					foreach($answers as $answer)
						$answer->delete();
					$alters = Alters::model()->findAllByAttributes(array("interviewId"=>$interviewId));
					foreach($alters as $alter)
						$alter->delete();
					$model->delete();
				}
			}
		}
		Yii::app()->request->redirect(Yii::app()->request->urlReferrer);
	}
}
