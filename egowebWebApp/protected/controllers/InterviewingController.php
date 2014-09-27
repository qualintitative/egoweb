<?php

class InterviewingController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $interviewId = "";

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('view', 'save', 'autocomplete', 'ajaxupdate', 'ajaxdelete'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update','index', 'study'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 *  CORE FUNCTION
	 *  Displays a page from a study for an interviewing session
	 * @param integer $id the ID of the study
	 */
	public function actionView($id)
	{
		$study = Study::model()->findByPk($id);
		$currentPage = 0;
		if(isset($_GET['page']))
			$currentPage = $_GET['page'];

		if(isset($_GET['interviewId'])){
			$interviewId = $_GET['interviewId'];
			$questions = Study::buildQuestions($study, $currentPage, $interviewId);
			if(!$questions){
				$this->redirect(Yii::app()->createUrl(
					'interviewing/'.$id.'?'.
					'interviewId='.$_GET['interviewId'].'&'.
					'page=0'
				));
			}

			// loads answers into array model
			foreach($questions as $question){
				if(is_numeric($question->alterId1) && !is_numeric($question->alterId2)){
					$array_id = $question->id . '-' . $question->alterId1;
					$model[$array_id] = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->id, 'alterId1'=>$question->alterId1));
				}else if(is_numeric($question->alterId1) && is_numeric($question->alterId2)){
					$array_id = $question->id . '-' . $question->alterId1 . 'and' . $question->alterId2;
					$model[$array_id] = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->id, 'alterId1'=>$question->alterId1,'alterId2'=>$question->alterId2));
				}else{
					$array_id = $question->id;
					$model[$array_id] = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->id));
				}
				if(!$model[$array_id]){
					$model[$array_id] = new Answer;
				}else{
					if($model[$array_id]->value == $study->valueNotYetAnswered)
						$model[$array_id]->value = "";
				}
			}
		}else{
			$questions = Study::buildQuestions($study, $currentPage);
			$interviewId = '';
			foreach($questions as $question){
				$array_id = $question->id;
				$model[$array_id] = new Answer;
			}
		}

		if(isset($questions[0]) && $questions[0]->answerType == 'ALTER_PROMPT' && $study->fillAlterList){
            #OK FOR SQL INJECTION
            $check = q("SELECT count(id) FROM alters WHERE interviewId = " . $interviewId)->queryScalar();
			if(!$check){
                #OK FOR SQL INJECTION
				$names = q("SELECT name FROM alterList where studyId = " . $study->id)->queryColumn();
				$count = 0;
				foreach($names as $name){
					$alter = new Alters;
					$alter->name = $name;
					$alter->ordering = $count;
					$alter->interviewId = $interviewId;
					$alter->save();
					$count++;
				}
			}
		}

		$qNav = Study::nav($study, $currentPage, $interviewId);
		$this->render('view',array(
			'studyId'=>$id,
			'questions'=>$questions,
			'page'=>$currentPage,
			'model'=>$model,
			'study'=>$study,
			'qNav'=>$qNav,
			'interviewId'=>$interviewId,
		));
	}

	/**
	 *  CORE FUNCTION
	 *  Saves answers for all the questions on a page
	 * @param integer $id the ID of the study
	 */
	public function actionSave($id){

		if(isset($_POST['Answer']))
		{

			$study = Study::model()->findByPk($id);
			$errors = 0;

			if(stristr($id, "key"))
				list($id, $key) = explode('&key=', $id);
			else
				$key = '';

			if(isset($_POST['nodes']))
				$nodes = "&nodes=" .  urlencode($_POST['nodes']);
			else
				$nodes = "";

			if(isset($_POST['Answer'][0]) && $_POST['Answer'][0]['answerType'] == "CONCLUSION"){
				$interview = Interview::model()->findByPk((int)$_POST['Answer'][0]['interviewId']);
				$interview->completed = -1;
				$interview->save();
				if(Yii::app()->user->isGuest)
					$this->redirect(Yii::app()->createUrl(''));
				else
					$this->redirect(Yii::app()->createUrl('admin/'));
			}

			foreach($_POST['Answer'] as $Answer){

				if(!isset($interviewId) || !$interviewId)
					$interviewId = $Answer['interviewId'];

				if(!isset($questions))
					$questions = Study::buildQuestions($study, $_POST['page'], $interviewId);

				if($Answer['questionType'] == "EGO_ID" && $Answer['value'] != "" && !$interviewId){
					if(Yii::app()->user->isGuest){
						foreach($_POST['Answer'] as $ego_id){
							$array_id = $ego_id['questionId'];
							$model[$array_id] = new Answer;
							$model[$array_id]->attributes = $ego_id;
							if(stristr(Question::getTitle($ego_id['questionId']), 'email')){
								$email = $ego_id['value'];
								$email_id = $array_id;
							}
						}
						if($key && User::hashPassword($email) != $key){
							$model[$email_id]->addError('value', 'You do not have the correct email for this survey.');
							$errors++;
							break;
						}
					}
					if($errors == 0){
						if(Yii::app()->user->isGuest && isset($email)){
							$interview = Interview::getInterviewFromEmail($_POST['studyId'],$email);
							if($interview){
								$this->redirect(Yii::app()->createUrl(
									'interviewing/'.$_POST['studyId'].'?'.
									'interviewId='.$interview->id.'&'.
									'page='.($interview->completed).'&key=' . $key
								));
							}
						}
						$interview = new Interview;
						$interview->studyId = $_POST['studyId'];
						if($interview->save()){
							$interviewId = $interview->id;
							$this->createEgoAnswers($interviewId, $id);
						}else{
							print_r($interview->errors);
							die();
						}
					}
				}

				if($Answer['questionType'] == "ALTER")
					$array_id = $Answer['questionId'] . "-" . $Answer['alterId1'];
				else if($Answer['questionType'] == "ALTER_PAIR")
					$array_id = $Answer['questionId'] . "-" . $Answer['alterId1'] . "and" . $Answer['alterId2'];
				else
					$array_id = $Answer['questionId'];

				if(isset($array_id) && $questions[$array_id] && !isset($model[$array_id])){
					if($questions[$array_id]->subjectType == "ALTER")
						$model[$array_id] = Answer::model()->findByAttributes(array('interviewId'=>$interviewId, 'questionId'=>$questions[$array_id]->id, 'alterId1'=>$questions[$array_id]->alterId1));
					else if($questions[$array_id]->subjectType == "ALTER_PAIR")
						$model[$array_id] = Answer::model()->findByAttributes(array('interviewId'=>$interviewId, 'questionId'=>$questions[$array_id]->id, 'alterId1'=>$questions[$array_id]->alterId1, 'alterId2'=>$questions[$array_id]->alterId2));
					else
						$model[$array_id] = Answer::model()->findByAttributes(array('interviewId'=>$interviewId, 'questionId'=>$questions[$array_id]->id));
				}

				if(!$model[$array_id])
					$model[$array_id] = new Answer;


				if($questions[$array_id]->useAlterListField){
					$interviewer = "";
					$field = $questions[$array_id]->useAlterListField;
					if(!Yii::app()->user->isSuperAdmin && !Yii::app()->user->isGuest)
						$interviewer = " AND interviewerId = " . Yii::app()->user->id;
                    #OK FOR SQL INJECTION
                    $params = new stdClass();
                    $params->name = ':studyId';
                    $params->value = $_POST['studyId'];
                    $params->dataType = PDO::PARAM_INT;
					$restricted = q("SELECT " . $field . " FROM alterList WHERE studyId = :studyId " . $interviewer, array($params))->queryColumn();
					if(!in_array($Answer['value'], $restricted))
						$model[$array_id]->addError('value', $Answer['value'] . " is either not in the participant list or has been assigned to another interviewer");
				}

				// check for list range limitations
				$checks = 0;
				if($questions[$array_id]->withListRange){
					foreach($_POST['Answer'] as $listCheck){
						if(in_array($questions[$array_id]->listRangeString, explode(',',$listCheck['value']))){
							$checks++;
						}

					}
					if($checks < $questions[$array_id]->maxListRange || $checks > $questions[$array_id]->maxListRange){
						$errorMsg = "";
						if($questions[$array_id]->minListRange && $questions[$array_id]->maxListRange){
							if($questions[$array_id]->minListRange != $questions[$array_id]->maxListRange)
								$errorMsg = $questions[$array_id]->minListRange . " - " . $questions[$array_id]->maxListRange;
							else
								$errorMsg = "just ". $questions[$array_id]->minListRange;
						}else if(!$questions[$array_id]->minListRange && !$questions[$array_id]->maxListRange){
								$errorMsg = "up to ".$questions[$array_id]->maxListRange;
						}else{
								$errorMsg = "at least ".$questions[$array_id]->minListRange;
						}
						$model[$array_id]->addError('value', "Please select " . $errorMsg . " response(s).");
					}

				}

				if($Answer['questionType'] == "ALTER_PROMPT"){
					// no Answer to save, go to next page
					if(Interview::countAlters($Answer['interviewId']) < $_POST['minAlters']){
						$model[$Answer['questionId']]->addError('value', 'Please list ' . $_POST['minAlters'] . ' people');
					}else{
						$this->createAlterAnswers($Answer['interviewId'], $_POST['studyId']);
						$this->redirect(Yii::app()->createUrl(
							'interviewing/'.$_POST['studyId'].'?'.
							'interviewId='.$Answer['interviewId'].'&'.
							'page='.($_POST['page']+1).'&key=' . $key
						));
					}
				}

				if($Answer['questionType'] == "INTRODUCTION" || $Answer['questionType'] == "PREFACE"){
					// no Answer to save, go to next page
						$this->redirect(Yii::app()->createUrl(
							'interviewing/'.$_POST['studyId'].'?'.
							'interviewId='.$Answer['interviewId'].'&'.
							'page='.($_POST['page']+1).'&key=' . $key
						));
				}

				if($Answer['value'] == "" && $Answer['skipReason'] == "NONE" && $Answer['answerType'] == "TEXTUAL"){
					$model[$array_id]->addError('value', 'Please enter a valid response');
					$errors++;
				}

				if($Answer['answerType'] == "DATE"){

					preg_match("/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/", $Answer['value'], $date);
					preg_match("/(\d{1,2}):(\d{1,2}) (AM|PM)/", $Answer['value'], $time);

					if(count($time) > 0){
						if(intval($time[1]) < 1 || intval($time[1]) > 12){
							$model[$array_id]->addError('value', 'Please enter 1 to 12 for the HH');
							$errors++;
						}
						if(intval($time[2]) < 0 || intval($time[2]) > 59){
							$model[$array_id]->addError('value', 'Please enter 0 to 59 for the MM');
							$errors++;
						}
					}else{
						$model[$array_id]->addError('value', 'Please enter the time of day');
						$errors++;
					}
					if(count($date) > 0){
						if(intval($date[2]) < 1 || intval($date[2]) > 31){
							$model[$array_id]->addError('value', 'Please enter a different number for the day of month');
							$errors++;
						}
					}
				}
				// Custom validators
				if($Answer['answerType'] == "NUMERICAL"){
					$min = ""; $max = ""; $numberErrors = 0; $showError = false;
					if(($Answer['value'] == "" && $Answer['skipReason'] == "NONE") || ($Answer['value'] != "" && !is_numeric($Answer['value'])))
						$model[$array_id]->addError('value', "Please enter a number");
					if($questions[$array_id]->minLimitType == "NLT_LITERAL"){
					    $min = $questions[$array_id]->minLiteral;
					}else if($questions[$array_id]->minLimitType == "NLT_PREVQUES"){
					    $min = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$questions[$array_id]->minPrevQues));
					    if($min)
					    	$min = $min->value;
					    else
					    	$min = "";
					}
					if($questions[$array_id]->maxLimitType == "NLT_LITERAL"){
					    $max = $questions[$array_id]->maxLiteral;
					}else if($questions[$array_id]->maxLimitType == "NLT_PREVQUES"){
					    $max = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$questions[$array_id]->maxPrevQues));
					    if($max)
					    	$max = $max->value;
					    else
					    	$max = "";
					}
					if($min != "")
						$numberErrors++;
					if($max != "")
						$numberErrors = $numberErrors + 2;

 					if((($max != "" && $Answer['value'] > $max)  ||  ($min != "" && $Answer['value'] < $min)) && $Answer['skipReason'] == "NONE")
 						$showError = true;

					if($numberErrors == 3 && $showError)
						$errorMsg = "The range of valid answers is " . $min . " to " . $max .".";
					else if ($numberErrors == 2 && $showError)
						$errorMsg = "The range of valid answers is " . $max . " or fewer.";
					else if ($numberErrors == 1 && $showError)
						$errorMsg = "The range of valid answers is " . $min . " or greater.";
					if($showError)
						$model[$array_id]->addError('value', $errorMsg);
				}

				if($Answer['answerType'] == "MULTIPLE_SELECTION"){

					$min = $questions[$array_id]->minCheckableBoxes;
					$max = $questions[$array_id]->maxCheckableBoxes;
					$numberErrors = 0; $showError = false; $errorMsg = "";
					if($min != "")
						$numberErrors++;
					if($max != "")
						$numberErrors = $numberErrors + 2;

					$checkedBoxes = count(explode(',',$Answer['value']));

					if (($Answer['value'] == "" || $checkedBoxes < $min || $checkedBoxes > $max) && $Answer['skipReason'] == "NONE")
						$showError = true;

					$s='';
					if($max != 1)
						$s = 's';
					if($questions[$array_id]->askingStyleList)
						$s .= ' for each row';
					if($numberErrors == 3 && $min == $max && $showError)
						$errorMsg = "Select " . $max ." response" . $s . " please.";
					else if($numberErrors == 3 && $min != $max && $showError)
						$errorMsg = "Select " . $min . " to " . $max ." response" . $s ." please.";
					else if ($numberErrors == 2 && $showError)
						$errorMsg = "You may select up to " . $max . " response" . $s ." please.";
					else if ($numberErrors == 1 && $showError)
						$errorMsg = "You must select at least " . $min . " response" . $s ." please.";

					if($showError)
						$model[$array_id]->addError('value', $errorMsg);

				}


				$model[$array_id]->attributes=$Answer;
				if($interviewId){
					$model[$array_id]->interviewId = $interviewId;
					$interview = Interview::model()->findByPk((int)$interviewId);
					if(!$model[$array_id]->getError('value')){
						$model[$array_id]->save();
						if($interview->completed != -1){
							$interview->completed = $_POST['page'] + 1;
							$interview->save();
						}
					}else{
						if($interview->completed != -1){
							$interview->completed = $_POST['page'];
							$interview->save();
						}
						$errors++;
					}
				}
			}
			if($errors == 0) {
				$page = $_POST['page'] + 1;
				$this->redirect(Yii::app()->createUrl(
					'interviewing/'.$_POST['studyId'].'?'.
					'interviewId='.$interviewId.'&'.
					'page='.$page.'&key=' . $key . $nodes
				));
			}else{
				$qNav =  Study::nav($study, $_POST['page'], $interviewId);
				$this->render('view',array(
					'studyId'=>$_POST['studyId'],
					'questions'=>$questions,
					'page'=>$_POST['page'],
					'study'=>$study,
					'model'=>$model,
					'qNav'=>$qNav,
					'key'=>$key,
					'interviewId'=>$interviewId,
				));
			}
		}

	}

	public function actionAjaxupdate(){
		if(isset($_POST['Alters'])){
            #OK FOR SQL INJECTION
            $params = new stdClass();
            $params->name = ':interviewId';
            $params->value = $_POST['Alters']['interviewId'];
            $params->dataType = PDO::PARAM_INT;

			$studyId = q("SELECT studyId FROM interview WHERE id = :interviewId", array($params))->queryScalar();

			$name_exists = Alters::model()->findByAttributes(array('name'=>$_POST['Alters']['name'], 'interviewId'=>(int)$_POST['Alters']['interviewId']));
			$model = new Alters;
			$model->attributes = $_POST['Alters'];
			if($name_exists){
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

			if(isset($study->multiSessionEgoId) && $study->multiSessionEgoId){
                #OK FOR SQL INJECTION
				$egoValue = q("SELECT value FROM answer WHERE interviewId = " . $model->interviewId . " AND questionID = " . $study->multiSessionEgoId)->queryScalar();
                #OK FOR SQL INJECTION
                $multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
                #OK FOR SQL INJECTION
                $interviewIds = q("SELECT interviewId FROM answer WHERE questionId in (" . implode(",", $multiIds) . ") AND value = '" .$egoValue . "'" )->queryColumn();
				$interviewIds = array_diff($interviewIds, array($_POST['Alters']['interviewId']));
				foreach($interviewIds as $interviewId){
                    #OK FOR SQL INJECTION
                    $params = new stdClass();
                    $params->name = ':name';
                    $params->value = $_POST['Alters']['name'];
                    $params->dataType = PDO::PARAM_STR;
					$oldAlterId = q("SELECT id FROM alters WHERE FIND_IN_SET (" . $interviewId . ", interviewId) and name = :name LIMIT 1", array($params))->queryScalar();
					if($oldAlterId){
						$model = Alters::model()->findByPk($oldAlterId);
						$model->interviewId = $model->interviewId . ",". $_POST['Alters']['interviewId'];
						break;
					}
				}
			}
			$criteria=new CDbCriteria;
			$criteria->condition = ('interviewId = '.$_POST['Alters']['interviewId']);
			$criteria->select='count(ordering) AS ordering';
			$row = Alters::model()->find($criteria);
			$model->ordering = $row['ordering'];
			if(!$model->getError('name'))
				$model->save();
			$interviewId = $_POST['Alters']['interviewId'];

			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"afterAltersEntered <= " . Interview::countAlters($interviewId),
				'order'=>'afterAltersEntered DESC',
			);
			$alterPrompt = AlterPrompt::getPrompt($studyId, Interview::countAlters($interviewId));

			$criteria=array(
				'condition'=>"FIND_IN_SET(" . $interviewId .", interviewId)",
				'order'=>'ordering',
			);

			$dataProvider=new CActiveDataProvider('Alters',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));

			$this->renderPartial('_view_alter', array('dataProvider'=>$dataProvider, 'alterPrompt'=>$alterPrompt, 'model'=>$model, 'studyId'=>$studyId, 'interviewId'=>$interviewId, 'ajax'=>true), false, true);
		}else if(isset($_GET['studyId']) && isset($_GET['interviewId'])){
			$study = Study::model()->findByPk((int)$_GET['studyId']);
			$alter_prompt = new Question;
			$alter_prompt->answerType = "ALTER_PROMPT";
			$alter_prompt->prompt = $study->alterPrompt;
			$alter_prompt->studyId = $study->id;
			$alter = new Alters;
			$this->renderPartial('_form_alter_prompt', array( 'question'=>$alter_prompt, 'interviewId'=>$_GET['interviewId'], 'model'=>$alter, 'study'=>$study, 'ajax'=>true), false, true);
		}
	}

	function actionAutocomplete() {
		if (Yii::app()->request->isAjaxRequest && isset($_GET['term'])) {
			$self = ''; $filter = "";
			if(isset($_GET['self']))
				$self = $_GET['self'];
			$names = "";
			if(isset($_GET['interviewId']) && $_GET['interviewId']){
				$sql = "SELECT " . $_GET['field'] .  " FROM alters WHERE interviewId = " . $_GET['interviewId'];
				$names = Yii::app()->db->createCommand($sql)->queryColumn();
				$names = addslashes(implode("' , '", $names));
			}else{
				if(!Yii::app()->user->isSuperAdmin && !Yii::app()->user->isGuest)
					$filter = " AND interviewerId = " . Yii::app()->user->id;
			}

			$criteria = new CDbCriteria();
			$criteria=array(
				'condition'=>$_GET['field'] . " LIKE '%" . $_GET['term'] .
				"%' AND studyId = ". $_GET['studyId'] .
				" AND " . $_GET['field']. " != '" . $self . "'" .
				" AND " . $_GET['field']. " NOT IN ('" . $names . "')" . $filter,
				'order'=>'ordering',
			);
			$models = AlterList::model()->findAll($criteria);
			$result = array();
			foreach ($models as $model)
				$result[] = array(
					'label' => $model->$_GET['field'],
					'value' => $model->$_GET['field'],
					'id' => $model->id,
					'field' => $model->email,
				);

			echo CJSON::encode($result);
		}
	}

	public function actionAjaxdelete(){
		if(isset($_GET['Alters'])){
			$model = Alters::model()->findByPk((int)$_GET['Alters']['id']);
			if($model){
				$interviewId = $_GET['interviewId'];
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
			$criteria=new CDbCriteria;
			$alterPrompt = AlterPrompt::getPrompt($_GET['studyId'], Interview::countAlters($interviewId));

			$criteria=array(
				'condition'=>"FIND_IN_SET(" . $interviewId . ", interviewId)",
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('Alters',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));

			$alter = new Alters;
			$this->renderPartial('_view_alter', array('dataProvider'=>$dataProvider, 'model'=>$alter,'alterPrompt'=>$alterPrompt, 'studyId'=>$_GET['studyId'], 'interviewId'=>$interviewId, 'ajax'=>true), false, true);
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$condition = "id != 0";
		if(!Yii::app()->user->isSuperAdmin){
            #OK FOR SQL INJECTION
			$studies = q("SELECT studyId FROM interviewers WHERE interviewerId = " . Yii::app()->user->id)->queryColumn();
			if($studies)
				$condition = "id IN (" . implode(",", $studies) . ")";
			else
				$condition = "id = -1";
		}

		$criteria = array(
			'condition'=>$condition . " AND multiSessionEgoId = 0",
			'order'=>'id DESC',
		);

		$single = Study::model()->findAll($criteria);

		$criteria = array(
			'condition'=>$condition . " AND multiSessionEgoId <> 0",
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
			if($participants){
				$interviewIds = q("SELECT interviewId from answer where questionId = " .$egoIdQ['id'] . " AND value in ( '" . implode("','", $participants) . "' )")->queryColumn();
				if($interviewIds)
					$restrictions = ' and id in (' . implode(",", $interviewIds) . ')';
				else
					$restrictions = ' and id = -1';
			}
		}
		$criteria=array(
			'condition'=>'completed > -1 && studyId = '.$id . $restrictions,
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

	// loads blank answers for everything before the alter questions
	public function createEgoAnswers($interviewId, $studyId){
        #OK FOR SQL INJECTION
		$questions = q("SELECT * FROM question WHERE subjectType = 'EGO' AND studyId = " . $studyId)->queryAll();
        #OK FOR SQL INJECTION
        $study = q("SELECT * FROM study WHERE id = ".$studyId)->queryRow();
		foreach($questions as $question){
            #OK FOR SQL INJECTION
			$oldAnswer = q("SELECT id FROM answer WHERE interviewId = $interviewId AND questionId = " . $question['id'])->queryScalar();
			if(!$oldAnswer){
				$answer = array(
					'questionId' => $question['id'],
					'interviewId'=>$interviewId,
					'value'=>$study['valueNotYetAnswered'],
					'skipReason'=>'NONE',
					'studyId'=>$study['id'],
					'questionType'=>$question['subjectType'],
					'answerType'=>$question['answerType'],
				);
				i('answer',$answer);
			}
		}
	}

	// loads blank answers for everything before the alter questions
	public function createAlterAnswers($interviewId, $studyId){
        #OK FOR SQL INJECTION
		$questions = q("SELECT * FROM question WHERE subjectType != 'EGO' AND subjectType != 'EGO_ID' AND studyId = " . $studyId)->queryAll();
        #OK FOR SQL INJECTION
        $study = q("SELECT * FROM study WHERE id = ".$studyId)->queryRow();
		$criteria = array(
			'condition'=>"FIND_IN_SET(" . $interviewId . ", interviewId)",
		);
		$alters = Alters::model()->findAll($criteria);
		$checkOnce = false;
		foreach($questions as $question){
			if($question['subjectType'] == 'ALTER'){
				foreach($alters as $alter){
					if($checkOnce == false){
                        #OK FOR SQL INJECTION
						$oldAnswer = q("SELECT id FROM answer WHERE interviewId = $interviewId AND questionId = " . $question['id'] . " AND alterId1 = " . $alter->id)->queryScalar();
						$checkOnce = true;
					}
					if(!$oldAnswer){
						$answer = array(
						    'questionId' => $question['id'],
						    'interviewId'=>$interviewId,
						    'value'=>$study['valueNotYetAnswered'],
						    'skipReason'=>'NONE',
						    'studyId'=>$study['id'],
						    'alterId1'=>$alter->id,
						    'questionType'=>$question['subjectType'],
						    'answerType'=>$question['answerType'],
						);
						i('answer',$answer);
					}
				}
			}
			if($question['subjectType'] == 'ALTER_PAIR'){
				foreach($alters as $alter){
					$alters2 = $alters;
					if($question['symmetric'])
						array_shift($alters2);
					foreach($alters2 as $alter2){
					if($checkOnce == false){
                        #OK FOR SQL INJECTION
						$oldAnswer = q("SELECT id FROM answer WHERE interviewId = $interviewId AND questionId = " . $question['id'] . " AND alterId1 = " . $alter->id . " AND alterId2 = " . $alter2->id)->queryScalar();
						$checkOnce = true;
					}
					if(!$oldAnswer){
							$answer = array(
							    'questionId' => $question['id'],
							    'interviewId'=>$interviewId,
							    'value'=>$study['valueNotYetAnswered'],
							    'skipReason'=>'NONE',
							    'studyId'=>$study['id'],
							    'alterId1'=>$alter->id,
							    'alterId2'=>$alter2->id,
							    'questionType'=>$question['subjectType'],
							    'answerType'=>$question['answerType'],
							);
							i('answer',$answer);
						}
					}
				}
			}
		}
        #OK FOR SQL INJECTION
		$questions = q("SELECT * FROM question WHERE subjectType = 'NETWORK' AND studyId = " . $studyId)->queryAll();
        #OK FOR SQL INJECTION
        $study = q("SELECT * FROM study WHERE id = ".$studyId)->queryRow();
		foreach($questions as $question){
            #OK FOR SQL INJECTION
			$oldAnswer = q("SELECT id FROM answer WHERE interviewId = $interviewId AND questionId = " . $question['id'])->queryScalar();
			if(!$oldAnswer){
				$answer = array(
					'questionId' => $question['id'],
					'interviewId'=>$interviewId,
					'value'=>$study['valueNotYetAnswered'],
					'skipReason'=>'NONE',
					'studyId'=>$study['id'],
					'questionType'=>$question['subjectType'],
					'answerType'=>$question['answerType'],
				);
				i('answer',$answer);
			}
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Answer the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Answer::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Answer $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='answer-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
