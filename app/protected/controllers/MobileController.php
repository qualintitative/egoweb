<?php

class MobileController extends Controller
{

	public $newAlterIds = array();
	public $newInterviewIds = array();

	public function actionIndex()
	{
		$filename = "EgoWebMobile.ipa";
		$date = date ("F d Y", filemtime($filename));
		$filename = "EgoWebMobile.apk";
		$android_date = date ("F d Y", filemtime($filename));

		$this->render('index', array(
			'date'=>$date,
			'android_date'=>$android_date
		));
	}

	public function actionCheck(){
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
		echo "success";
	}

	public function actionInterview(){
		$this->render('interview');
	}

	public function actionImport(){
		$this->render('import');
	}
	public function actionAjaxstudies(){
		if(isset($_POST['userId'])){
            $user = User::model()->findByPK($_POST['userId']);
			$permission = $user->permissions;
			if($permission != 11){
                $studyIds = array();
                $criteria = new CDbCriteria;
                $criteria->condition = "interviewerId = " . $user->id;
                $interviewers = Interviewer::model()->findAll($criteria);
                foreach($interviewers as $interviewer){
                    $studyIds[] = $interviewer->studyId;
                }
			}else{
				$studyIds = "";
            }
		}else{
			$studyIds = "";
		}
		if($studyIds){
            $criteria = array(
                "condition"=>"id IN (" . implode(",", $studyIds) . ")",
            );
            $studies = Study::model()->findAll($criteria);
		}else{
			$studies =  Study::model()->findAll();
        }

		foreach($studies as $study){
			$json[$study->id] = $study->name;
		}
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
		echo json_encode($json);
		Yii::app()->end();
	}

	public function actionAjaxdata($id){
        Yii::log("begin import");
		$study = mToA(Study::model()->findByPK($id));
		$results = Question::model()->findAllByAttributes(array("studyId"=>$id));
        $questions = array();
        foreach($results as $result){
            $questions[] = mToA($result);
        }
        $results = QuestionOption::model()->findAllByAttributes(array("studyId"=>$id));
        $options = array();
        foreach($results as $result){
            $options[] = mToA($result);
        }
        $results = Expression::model()->findAllByAttributes(array("studyId"=>$id));
        $expressions = array();
        foreach($results as $result){
            $expressions[] = mToA($result);
        }
        $results = AlterList::model()->findAllByAttributes(array("studyId"=>$id));
        $alterList = array();
        foreach($results as $result){
            $alterList[] = mToA($result);
        }
        $results = AlterPrompt::model()->findAllByAttributes(array("studyId"=>$id));
        $alterPrompts = array();
        foreach($results as $result){
            $alterPrompts[] = mToA($result);
        }

		$interviewIds = array();
		#OK FOR SQL INJECTION
		$audioFiles = array();

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

        foreach($columns as &$column){
            foreach($column as &$label){
                $label = strtoupper($label);
            }
        }
        /*
		if(file_exists(Yii::app()->basePath."/../audio/".$id . "/STUDY/ALTERPROMPT.mp3")){
			$audioFiles[] = array(
				"url"=>Yii::app()->getBaseUrl(true)."/audio/". $id . "/STUDY/ALTERPROMPT.mp3",
				"type"=>"STUDY",
				"id"=>"ALTERPROMPT"
			);
		}

		foreach($questions as $question){
			if($question[4] && file_exists(Yii::app()->basePath."/../audio/".$id . "/PREFACE/" . $question[0] . ".mp3")){
				$audioFiles[] = array(
					"url"=>Yii::app()->getBaseUrl(true)."/audio/". $id . "/PREFACE/" . $question[0] . ".mp3",
					"type"=>"PREFACE",
					"id"=>$question[0]
				);
			}
			if(file_exists(Yii::app()->basePath."/../audio/".$id . "/" .  $question[6] . "/" . $question[0] . ".mp3")){
				$audioFiles[] = array(
					"url"=>Yii::app()->getBaseUrl(true)."/audio/". $id . "/" .  $question[6] . "/"  . $question[0] . ".mp3",
					"type"=>$question[6],
					"id"=>$question[0]
				);
			}
		}

		foreach($options as $option){
			if(file_exists(Yii::app()->basePath."/../audio/".$id . "/OPTION/" . $option[0] . ".mp3")){
				$audioFiles[] = array(
					"url"=>Yii::app()->getBaseUrl(true)."/audio/". $id . "/OPTION/"  . $option[0] . ".mp3",
					"type"=>"OPTION",
					"id"=>$option[0]
				);
			}
		}
*/
		/*
		foreach($interviews as $interview){
			array_push($interviewIds, $interview[0]);
		}

		if($interviewIds){
			$interviewIds = implode(',', $interviewIds);
			#OK FOR SQL INJECTION
			foreach($alters as &$alter){
				if(strlen($alter[3]) >= 8)
					$alter[3] = decrypt($alter[3]);
			}
		}else{
			$alters = "";
		}
        */

		$data = array(
			'study'=>$study,
			'questions'=>$questions,
			'options'=>$options,
			'expressions'=>$expressions,
	//		'answers'=>$answers,
	//		'interviews'=>$interviews,
	//		'alters'=>$alters,
	        'alterList'=>$alterList,
	        'alterPrompts'=>$alterPrompts,
			'audioFiles'=>$audioFiles,
			'columns'=>$columns,
		);

		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');	// cache for 1 day
		echo json_encode($data);
        Yii::log("end import");
		Yii::app()->end();
	}

	public function actionAuthenticate(){
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');	// cache for 1 day

		if(isset($_POST['LoginForm']))
		{
			$model = new LoginForm;
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()){
				echo Yii::app()->user->id;
				Yii::app()->end();
			}else{
				echo "failed";
			}
		}else{
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		}
	}

	public function actionGetstudies(){
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');	// cache for 1 day

		if(isset($_POST['LoginForm']))
		{
			$model = new LoginForm;
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()){
        		if(Yii::app()->user->id){
                    $user = User::model()->findByPK(Yii::app()->user->id);
        			$permission = $user->permissions;
        			if($permission != 11){
                        $studyIds = array();
                        $criteria = new CDbCriteria;
                        $criteria->condition = "interviewerId = " . $user->id;
                        $interviewers = Interviewer::model()->findAll($criteria);
                        foreach($interviewers as $interviewer){
                            $studyIds[] = $interviewer->studyId;
                        };
        			}else{
        				$studyIds = "";
                    }
        		}else{
        			$studyIds = "";
        		}
                if($studyIds){
                    $criteria = array(
                        "condition"=>"id IN (" . implode(",", $studyIds) . ")",
                    );
                    $studies = Study::model()->findAll($criteria);
        		}else{
        			$studies =  Study::model()->findAll();
                }
        		foreach($studies as $study){
        			$json[] = array("id"=>$study->id, "name"=>$study->name);
        		}
        		echo json_encode($json);
				Yii::app()->end();
			}else{
				echo "failed";
			}
		}else{
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		}
	}

  public function actionUploadData(){
  			header("Access-Control-Allow-Origin: *");
  		$errorMsg = "";
  		if(count($_POST)){
  			header("Access-Control-Allow-Origin: *");
  			header('Access-Control-Allow-Credentials: true');
  			header('Access-Control-Max-Age: 86400');
  			$errors = 0;
  			$errorMsgs = array();
  			Yii::log($_POST['data']);
  			$data = json_decode($_POST['data'],true);
  			if(!$data['study']['ID']){
  				echo "Study object broken:";
  				print_r($data['study']);
  				header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
  				die();
  			}
        $oldStudy = Study::model()->findByAttributes(array("name"=>$data['study']['NAME']));
  			if($oldStudy && $oldStudy->modified == $data['study']['MODIFIED']){
  				$this->saveAnswers($data);
  		  }else{
  				$study = new Study;
  				foreach($study->attributes as $key=>$value){
  					$study->$key = $data['study'][strtoupper($key)];
  				}
          if($oldStudy)
  				   $study->name = $data['study']['NAME'] . " 2";
  				$questions = array();
  				foreach($data['questions'] as $q){
  					$question = new Question;
  					foreach($question->attributes as $key=>$value){
  						$question->$key = $q[strtoupper($key)];
  					}
  					array_push($questions, $question);
  				}
  				$options = array();
  				foreach($data['questionOptions'] as $o){
  					$option = new QuestionOption;
  					foreach($option->attributes as $key=>$value){
  						$option->$key = $o[strtoupper($key)];
  					}
  					array_push($options, $option);
  				}
  				$expressions = array();
  				foreach($data['expressions'] as $e){
  					$expression = new Expression;
  					foreach($expression->attributes as $key=>$value){
  						$expression->$key = $e[strtoupper($key)];
  					}
  					array_push($expressions, $expression);
  				}
          echo "questions ". count($questions);
  				$newData = Study::replicate($study, $questions, $options, $expressions, array());
  				if($newData){
  					$this->saveAnswers($data, $newData);
  					echo "Study " . $oldStudy->name . " was modified. (" . $oldStudy->modified .  ":" . $data['study']['MODIFIED'] . ")  Generated new study: " . $study->name . ". ";
  				}else{
  					echo "Error while attempting to create a new study.";
  				}
  			}
  			if($errors == 0)
  				echo "Upload completed.  No Errors Found";
  			else
  				echo "Errors encountered!";
  		}
  	}

	public function actionSyncData(){
		header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    $data = json_decode($_POST['data'],true);
		$errorMsg = "";
		if(count($_POST)){
      if(!isset($_POST['LoginForm']))
  		{
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die();
      }
      $egoIds = array();
      if(isset($data['study']['NAME'])){
        $oldStudy = Study::model()->findByAttributes(array("name"=>$data['study']['NAME']));
        if($oldStudy){
          $interviews = Interview::model()->findAllByAttributes(array("studyId"=>$oldStudy->id));
          foreach($interviews as $interview){
            $egoId = Interview::getEgoId($interview->id);
            if($egoId == $data['interviews'][0]["EGOID"]){
              echo $egoId . ": interview already exists";
              die();
            }
          }
        }
      }else{
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die();
      }
			$model = new LoginForm;
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()){

      }else{
    			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
          die();
    	}
			$errors = 0;
			$errorMsgs = array();
			Yii::log($_POST['data']);
			if(!$data['study']['ID']){
				//echo "Study object broken:";
				//print_r($data['study']);
				header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
				die();
			}
			if($oldStudy){
        $questions = Question::model()->findAllByAttributes(array("studyId"=>$oldStudy->id));
        $newQuestionIds = array();
        $newQuestionTitles = array();
        foreach($questions as $question){
          if($question->subjectType == "NAME_GENERATOR")
            $nameGenQId = $question->id;
          $newQuestionIds[$question->title] = $question->id;
          $newQuestionTitles[$question->id] = $question->title;
        }
        $options = QuestionOption::model()->findAllByAttributes(array("studyId"=>$oldStudy->id));
        $newOptionIds = array();
        foreach($options as $option){
          $newOptionIds[$newQuestionTitles[$option->questionId]."_".$option->name] = $option->id;
        }
        echo "Marging with existing study $oldStudy->name. ";
        $data['interviews'][0]['STUDYID'] = $oldStudy->id;
        $newData = array(
          "studyId"=>$oldStudy->id,
          "newQuestionIds"=>$newQuestionIds,
          "newOptionIds"=>$newOptionIds,
          "nameGenQId"=>$nameGenQId,
        );
				$this->saveAnswersMerge($data, $newData);
		  }else{
				$study = new Study;
				foreach($study->attributes as $key=>$value){
					$study->$key = $data['study'][strtoupper($key)];
				}
        if($oldStudy)
				   $study->name = $data['study']['NAME'] . " 2";
				$questions = array();
				foreach($data['questions'] as $q){
					$question = new Question;
					foreach($question->attributes as $key=>$value){
						$question->$key = $q[strtoupper($key)];
					}
					array_push($questions, $question);
				}
				$options = array();
				foreach($data['questionOptions'] as $o){
					$option = new QuestionOption;
					foreach($option->attributes as $key=>$value){
						$option->$key = $o[strtoupper($key)];
					}
					array_push($options, $option);
				}
				$expressions = array();
				foreach($data['expressions'] as $e){
					$expression = new Expression;
					foreach($expression->attributes as $key=>$value){
						$expression->$key = $e[strtoupper($key)];
					}
					array_push($expressions, $expression);
				}
				$newData = Study::replicate($study, $questions, $options, $expressions, array());
				if($newData){
					$this->saveAnswers($data, $newData);
					echo "Study " . $oldStudy->name . " was created because " . $data['study']['NAME'] . " was not found. (" . $oldStudy->modified .  ":" . $data['study']['MODIFIED'] . ")  Generated new study: " . $study->name . ". ";
				}else{
					echo "Error while attempting to create a new study.";
				}
			}
			if($errors == 0)
				echo "Upload completed.  No Errors Found";
			else
				echo "Errors encountered!";
		}
	}

	private function saveAnswers($data, $newData = null)
	{
    if(count($data['interviews']) == 0)
      return false;
		foreach($data['interviews'] as $interview){
    		$newInterview = new Interview;
    		if($newData)
    			$newInterview->studyId = $newData['studyId'];
    		else
    			$newInterview->studyId = $interview['STUDYID'];
    		$newInterview->completed = $interview['COMPLETED'];
    		$newInterview->start_date = $interview['START_DATE'];
    		$newInterview->complete_date = $interview['COMPLETE_DATE'];
    		if($newInterview->save()){
                $newInterviewIds[$interview['ID']] = $newInterview->id;
            }else{
                $errors++;
                print_r($newInterview->getErrors());
				die();
            }
		}
		if(isset($data['alters'])){
		foreach($data['alters'] as $alter){
			if(!isset($newInterviewIds[$alter['INTERVIEWID']]))
				continue;
			$newAlter = new Alters;
			$newAlter->name = $alter['NAME'];
			$newAlter->interviewId = $newInterviewIds[$alter['INTERVIEWID']];
            $newAlter->nameGenQIds = $alter['NAMEGENQIDS'];
			$newAlter->ordering = 1;

			if(!$newAlter->save()){
				$errors++;
        echo $alter['NAMEGENQIDS'];
				print_r($newAlter->getErrors());
				die();
			}else{
				$newAlterIds[$alter['ID']] = $newAlter->id;
			}
		}
		}
		foreach($data['answers'] as $answer){
		$newAnswer = new Answer;
		if($newData){
			if(!isset($newData['newQuestionIds'][$answer['QUESTIONID']]))
				continue;
			$newAnswer->questionId = $newData['newQuestionIds'][$answer['QUESTIONID']];
			$newAnswer->studyId = $newData['studyId'];
			if($answer['ANSWERTYPE'] == "MULTIPLE_SELECTION"){
				$values = explode(',', $answer['VALUE']);
				foreach($values as &$value){
					if(isset($newData['newOptionIds'][$value]))
						$value = $newData['newOptionIds'][$value];
				}
				$answer['VALUE'] = implode(',', $values);
			}
			$newAnswer->value = $answer['VALUE'];
			if($answer['OTHERSPECIFYTEXT']){
				foreach(preg_split('/;;/', $answer['OTHERSPECIFYTEXT']) as $other){
				if($other && strstr($other, ':')){
					list($key, $val) = preg_split('/:/', $other);
					$responses[] = $newData['newOptionIds'][$key] . ":" .$val;
				}
				}
				$answer['OTHERSPECIFYTEXT'] = implode(";;", $responses);
			}
		}else{
			if(!isset($answer['QUESTIONID']))
				continue;
			$newAnswer->questionId = $answer['QUESTIONID'];
			$newAnswer->studyId = $newInterview->studyId;
			$newAnswer->value = $answer['VALUE'];
		}
		$newAnswer->questionType = $answer['QUESTIONTYPE'];
		$newAnswer->answerType = $answer['ANSWERTYPE'];
		$newAnswer->otherSpecifyText = $answer['OTHERSPECIFYTEXT'];
		$newAnswer->skipReason = $answer['SKIPREASON'];
		$newAnswer->interviewId = $newInterviewIds[$answer['INTERVIEWID']];
		if(is_numeric($answer['ALTERID1']) && isset($newAlterIds[$answer['ALTERID1']]))
			$newAnswer->alterId1 = $newAlterIds[$answer['ALTERID1']];
		if(is_numeric($answer['ALTERID2']) && isset($newAlterIds[$answer['ALTERID2']]))
			$newAnswer->alterId2 = $newAlterIds[$answer['ALTERID2']];
		if(!$newAnswer->save()){
			print_r($newAnswer->getErrors());
			die();
		}
		}
	}

  private function saveAnswersMerge($data, $newData)
	{
    if(count($data['interviews']) == 0)
      return false;
		foreach($data['interviews'] as $interview){
    		$newInterview = new Interview;
    		$newInterview->studyId = $newData['studyId'];
    		$newInterview->completed = $interview['COMPLETED'];
    		$newInterview->start_date = $interview['START_DATE'];
    		$newInterview->complete_date = $interview['COMPLETE_DATE'];
    		if($newInterview->save()){
                $newInterviewIds[$interview['ID']] = $newInterview->id;
            }else{
                $errors++;
                print_r($newInterview->getErrors());
				die();
            }
		}
    $questionTitles = array();
    foreach($data['questions'] as $q){
      $questionTitles[$q['ID']] = $q['TITLE'];
    }
		if(isset($data['alters'])){
		foreach($data['alters'] as $alter){
			if(!isset($newInterviewIds[$alter['INTERVIEWID']]))
				continue;
			$newAlter = new Alters;
			$newAlter->name = $alter['NAME'];
			$newAlter->interviewId = $newInterviewIds[$alter['INTERVIEWID']];
      if(!isset($alter['NAMEGENQIDS'])){
        $newAlter->nameGenQIds = $newData["nameGenQId"];
      }else{
        if(stristr($alter['NAMEGENQIDS'], ",")){
          $qIds = explode(",", $alter['NAMEGENQIDS']);
          foreach($qIds as $qId){
            $qTitle = $questionTitles[$qId];
            if(!isset($newData['newQuestionIds'][$qTitle]))
              $newAlter->nameGenQIds = $newData['newQuestionIds'][$qTitle];
            else
              $newAlter->nameGenQIds =  $newData["nameGenQId"];
          }
        }else{
          $qTitle = $questionTitles[$alter['NAMEGENQIDS']];
          if(!isset($newData['newQuestionIds'][$qTitle]))
            $newAlter->nameGenQIds = $newData['newQuestionIds'][$qTitle];
          else
            $newAlter->nameGenQIds =  $newData["nameGenQId"];
        }
      }
			$newAlter->ordering = $alter['ORDERING'];
			if(!$newAlter->save()){
				$errors++;
        echo $questionTitles[$alter['NAMEGENQIDS']];
        echo $newData["nameGenQId"];
        echo $alter['NAMEGENQIDS'];
				print_r($newAlter->getErrors());
				die();
			}else{
				$newAlterIds[$alter['ID']] = $newAlter->id;
			}
		}
		}
    $optionNames = array();
    foreach($data['questionOptions'] as $o){
      $optionNames[$o['ID']] = $o['NAME'];
    }
		foreach($data['answers'] as $answer){
		$newAnswer = new Answer;
		if($newData){
      $qTitle = $questionTitles[$answer['QUESTIONID']];
			if(!isset($newData['newQuestionIds'][$qTitle]))
				continue;
			$newAnswer->questionId = $newData['newQuestionIds'][$qTitle];
			$newAnswer->studyId = $newData['studyId'];
			if($answer['ANSWERTYPE'] == "MULTIPLE_SELECTION"){
				$values = explode(',', $answer['VALUE']);
				foreach($values as &$value){
					if(isset($newData['newOptionIds'][$qTitle . "_" . $optionNames[$value]]))
						$value = $newData['newOptionIds'][$qTitle . "_" . $optionNames[$value]];
				}
				$answer['VALUE'] = implode(',', $values);
			}
			$newAnswer->value = $answer['VALUE'];
			if($answer['OTHERSPECIFYTEXT']){
				foreach(preg_split('/;;/', $answer['OTHERSPECIFYTEXT']) as $other){
				if($other && strstr($other, ':')){
					list($key, $val) = preg_split('/:/', $other);
					$responses[] = $newData['newOptionIds'][$optionNames[$key]] . ":" .$val;
				}
				}
				$answer['OTHERSPECIFYTEXT'] = implode(";;", $responses);
			}
		}
		$newAnswer->questionType = $answer['QUESTIONTYPE'];
		$newAnswer->answerType = $answer['ANSWERTYPE'];
		$newAnswer->otherSpecifyText = $answer['OTHERSPECIFYTEXT'];
		$newAnswer->skipReason = $answer['SKIPREASON'];
		$newAnswer->interviewId = $newInterviewIds[$answer['INTERVIEWID']];
		if(is_numeric($answer['ALTERID1']) && isset($newAlterIds[$answer['ALTERID1']]))
			$newAnswer->alterId1 = $newAlterIds[$answer['ALTERID1']];
		if(is_numeric($answer['ALTERID2']) && isset($newAlterIds[$answer['ALTERID2']]))
			$newAnswer->alterId2 = $newAlterIds[$answer['ALTERID2']];
		if(!$newAnswer->save()){
			print_r($newAnswer->getErrors());
			die();
		}
		}
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}
