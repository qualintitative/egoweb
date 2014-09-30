<?php

class MobileController extends Controller
{

	public $newAlterIds = array();
	public $newInterviewIds = array();

	public function actionIndex()
	{
		$this->render('index');
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
		if(isset($_GET['userId'])){
            #OK FOR SQL INJECTION
            $params = new stdClass();
            $params->name = ':userId';
            $params->value = $_GET['userId'];
            $params->dataType = PDO::PARAM_INT;

			$permission = q("SELECT permissions FROM user WHERE id = :userID",array($params))->queryScalar();
			if($permission != 11)
                #OK FOR SQL INJECTION
				$studyIds = q("SELECT studyId FROM interviewers WHERE interviewerId = :userID",array($params))->queryColumn();
			else
				$studyIds = "";
		}else{
			$studyIds = "";
		}
		if($studyIds)
            #OK FOR SQL INJECTION
			$studies = q("SELECT id,name FROM study WHERE id IN (" . implode(",", $studyIds) . ")")->queryAll();
		else
            #OK FOR SQL INJECTION
			$studies = q("SELECT id,name FROM study")->queryAll();

		foreach($studies as $study){
			$json[$study['id']] = $study['name'];
		}
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
		echo json_encode($json);
		Yii::app()->end();
	}

	public function actionAjaxdata($id){
        #OK FOR SQL INJECTION
		$study = q("SELECT * FROM study WHERE id = " . $id)->queryRow(false);
        #OK FOR SQL INJECTION
        $questions = q("SELECT * FROM question WHERE studyId = ".$id)->queryAll(false);
        #OK FOR SQL INJECTION
        $options = q("SELECT * FROM questionOption WHERE studyId = " . $id)->queryAll(false);
        #OK FOR SQL INJECTION
        $expressions = q("SELECT * FROM expression WHERE studyId = " . $id)->queryAll(false);
        #OK FOR SQL INJECTION
        $answers = q("SELECT * FROM answer WHERE studyId = " . $id)->queryAll(false);
		$interviewIds = array();
        #OK FOR SQL INJECTION
        $interviews = q("SELECT * FROM interview WHERE studyId = " . $id)->queryAll(false);
		$audioFiles = array();

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

		foreach($interviews as $interview){
			array_push($interviewIds, $interview[0]);
		}

		if($interviewIds){
			$interviewIds = implode(',', $interviewIds);
            #OK FOR SQL INJECTION
			$alters = q("SELECT * FROM alters WHERE interviewId  in (" . $interviewIds . ")")->queryAll(false);
		}else{
			$alters = "";
		}
		$data = array(
			'study'=>$study,
			'questions'=>$questions,
			'options'=>$options,
			'expressions'=>$expressions,
			'answers'=>$answers,
			'interviews'=>$interviews,
			'alters'=>$alters,
			'audioFiles'=>$audioFiles,
		);
		if (isset($_SERVER['HTTP_ORIGIN'])) {
		    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		    header('Access-Control-Allow-Credentials: true');
		    header('Access-Control-Max-Age: 86400');    // cache for 1 day
		}
		echo json_encode($data);
		Yii::app()->end();
	}

	public function actionAuthenticate(){
		    header("Access-Control-Allow-Origin: *");
		    header('Access-Control-Allow-Credentials: true');
		    header('Access-Control-Max-Age: 86400');    // cache for 1 day

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

	public function actionUploadData(){
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
				die();
			}
            #OK FOR SQL INJECTION
            $params = new stdClass();
            $params->name = ':studyId';
            $params->value = $data['study']['ID'];
            $params->dataType = PDO::PARAM_INT;

			$oldStudy = q("SELECT * FROM study WHERE id = :studyId", array($params))->queryRow();
			if($oldStudy['modified'] == $data['study']['MODIFIED']){
				$this->saveAnswers($data);
			}else{
				$study = new Study;
				foreach($study->attributes as $key=>$value){
					$study->$key = $data['study'][strtoupper($key)];
				}
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
					echo "Study was modified.  Generated new study: " . $study->name . ". ";
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

	private function compare($data){
        #OK FOR SQL INJECTION
		$oldStudy = q("SELECT * FROM study WHERE id = " . $data['study']['ID'])->queryRow();

		foreach($oldStudy as $key=>$value){
		    $oldStudy[strtoupper($key)] = $value;
		    unset($oldStudy[$key]);
		}

		if($data['study'] != $oldStudy)
		    return false;

        #OK FOR SQL INJECTION
		$oldQuestions = q("SELECT * FROM question WHERE studyId = " . $data['study']['ID'])->queryAll();
		if(count($data['questions']) != count($oldQuestions))
		    return false;

        #OK FOR SQL INJECTION
		$oldQuestionOptions = q("SELECT * FROM questionOption WHERE studyId = " . $data['study']['ID'])->queryAll();
		if(count($data['questionOptions']) != count($oldQuestionOptions))
		    return false;

        #OK FOR SQL INJECTION
		$oldExpressions = q("SELECT * FROM expression WHERE studyId = " . $data['study']['ID'])->queryAll();
		if(count($data['expressions']) != count($oldExpressions))
		    return false;

		return true;
	}

	private function saveAnswers($data, $newData = null)
	{
		foreach($data['interviews'] as $interview){
		    $newInterview = new Interview;
		    if($newData)
		    	$newInterview->studyId = $newData['studyId'];
		    else
		    	$newInterview->studyId = $interview['STUDYID'];
		    $newInterview->completed = $interview['COMPLETED'];
		    $newInterview->save();
		    $newInterviewIds[$interview['ID']] = $newInterview->id;
		}
		if(isset($data['alters'])){
		    foreach($data['alters'] as $alter){
		    	if(!isset($newInterviewIds[$alter['INTERVIEWID']]))
		    		continue;
		    	$newAlter = new Alters;
		    	$newAlter->name = $alter['NAME'];
		    	$newAlter->interviewId = $newInterviewIds[$alter['INTERVIEWID']];
		    	$newAlter->ordering=1;
		    	if(!$newAlter->save()){
		    		$errors++;
		    		print_r($newAlter->getErrors());
		    		die();
		    	}else{
		    		$newAlterIds[$alter['ID']] = $newAlter->id;
		    	}
		    	$newAlter->save();
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
		    	$newAnswer->studyId = $answer['STUDYID'];
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
