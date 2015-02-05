<?php

class AuthoringController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $studyId;

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
				'actions'=>array(Yii::app()->controller->action->id),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionImportlist()
	{
		if(!is_uploaded_file($_FILES['userfile']['tmp_name'])) //checks that file is uploaded
			die("Error importing Participant list");

		$file = fopen($_FILES['userfile']['tmp_name'],"r");

		while(! feof($file)){
			$data = fgetcsv($file);
			if(isset($data[0]) && $data[0]){
				$model = new AlterList;
				$criteria=new CDbCriteria;
				$criteria->condition = ('studyId = '.$_POST['studyId']);
				$criteria->select='count(ordering) AS ordering';
				$row = AlterList::model()->find($criteria);
				$model->ordering = $row['ordering'];
				$model->name = trim($data[0]);
				$model->email = isset($data[1]) ? $data[1] : "";
				$model->studyId = $_POST['studyId'];
				$model->save();
			}

		}
		fclose($file);
		$this->redirect(Yii::app()->request->getUrlReferrer());
	}

	public function actionUploadaudio()
	{
		if(isset($_POST['studyId']) && isset($_POST['type']) && isset($_POST['id'])){
			if(!is_uploaded_file($_FILES['userfile']['tmp_name'])) //checks that file is uploaded
				die("Error importing Audio");

			if(!is_dir(Yii::app()->basePath."/../audio/".$_POST['studyId']))
				mkdir(Yii::app()->basePath."/../audio/".$_POST['studyId'], 0777, true);

			if(!is_dir(Yii::app()->basePath."/../audio/".$_POST['studyId'] . "/" . $_POST['type']))
				mkdir(Yii::app()->basePath."/../audio/".$_POST['studyId'] . "/" . $_POST['type'], 0777, true);

			if(move_uploaded_file($_FILES['userfile']['tmp_name'], Yii::app()->basePath."/../audio/".$_POST['studyId'] . "/" . $_POST['type'] . "/". $_POST['id'] . ".mp3"))
				echo "<a class=\"playSound\" onclick=\"playSound($(this).attr('file'))\" href=\"#\" file=\"/audio/".$_POST['studyId'] . "/" . $_POST['type'] . "/". $_POST['id'] . ".mp3\"><span class=\"fui-volume play-sound\"></span></a>";

		}else if(isset($_GET['studyId']) && isset($_GET['type']) && isset($_GET['id'])){
			$this->renderPartial('_form_audio',array(
				'studyId'=>$_GET['studyId'],
				'type'=>$_GET['type'],
				'id'=>$_GET['id'],
			));
		}
	}

	public function actionDeleteaudio()
	{
		if(isset($_POST['studyId']) && isset($_POST['type']) && isset($_POST['id'])){
			if(file_exists(Yii::app()->basePath."/../audio/".$_POST['studyId'] . "/" . $_POST['type'] . "/". $_POST['id'] . ".mp3"))
				unlink(Yii::app()->basePath."/../audio/".$_POST['studyId'] . "/" . $_POST['type'] . "/". $_POST['id'] . ".mp3");
		}
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		// sets global studyId for authoring
 		$this->studyId = $id;

		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Study;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Study']))
		{
			$model->attributes=$_POST['Study'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}


	/**
	 * Edits a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionEdit($id)
	{
 		$this->studyId = $id;
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Study']))
		{
			$model->attributes=$_POST['Study'];
			if($model->save()){
				Study::updated($this->studyId);
				$this->redirect(array('edit','id'=>$model->id));
			}
		}

		$this->render('edit',array(
			'model'=>$model,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new Study;
		if(isset($_POST['Study']))
		{
			$model->attributes=$_POST['Study'];
			if($model->save())
				$this->redirect(array('edit','id'=>$model->id));
		}

		$condition = "id != 0";
		if(!Yii::app()->user->isSuperAdmin){
			#OK FOR SQL INJECTION
			$studies = array();
			if(Yii::app()->user->isAdmin){
				$studies = q("SELECT id FROM study WHERE userId = " . Yii::app()->user->id)->queryColumn();
				$addedStudies = q("SELECT studyId FROM interviewers WHERE interviewerId = " . Yii::app()->user->id)->queryColumn();
				if(count($addedStudies) > 0)
					$studies = array_merge($studies, $addedStudies);
			}else{
				$studies = q("SELECT studyId FROM interviewers WHERE interviewerId = " . Yii::app()->user->id)->queryColumn();
			}
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

/*
		$single = Study::model()->findAllByAttributes(array('multiSessionEgoId'=>0));
		$multi = Study::model()->findAll('multiSessionEgoId <> 0', $params = array('order'=>'multiSessionEgoId'));
*/
 		$this->render('index',array(
			'model'=>$model,
			'single'=>$single,
			'multi'=>$multi,

		));
	}

	/**
	 * Lists all models.
	 */
	public function actionEgo_id($id)
	{
		$this->studyId=$id;
		if(isset($_POST['Question'])){
			$model = new Question;
			$model->attributes = $_POST['Question'];
			$criteria=new CDbCriteria;
			$criteria->condition = ('studyId = '.$_POST['Question']['studyId'] . ' AND subjectType = "EGO_ID"');
			$criteria->select='count(ordering) AS ordering';
			$row = Question::model()->find($criteria);
			$model->ordering = $row['ordering'];
			$model->save();
			$this->redirect(array('ego_id','id'=>$id));

		}
			$model = new Question;
			$model->subjectType = "EGO_ID";
			$model->studyId = $id;

		// Uncomment the following line if AJAX validation is needed

		$criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $id . " AND subjectType = '" . $model->subjectType . "'",
			'order'=>'ordering',
		);
		$dataProvider=new CActiveDataProvider('Question',array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));

		$this->render('view_question',array(
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionEgo($id)
	{
		$this->studyId=$id;
		if(isset($_POST['Question'])){
			$model = new Question;
			$model->attributes=$_POST['Question'];
			$criteria=new CDbCriteria;
			$criteria->condition = ('studyId = '.$_POST['Question']['studyId'] . ' AND subjectType = "EGO"');
			$criteria->select='count(ordering) AS ordering';
			$row = Question::model()->find($criteria);
			$model->ordering = $row['ordering'];
			$model->save();
			$this->redirect(array('ego','id'=>$id));
		}

			$model = new Question;
			$model->subjectType = "EGO";
			$model->studyId = $id;

		// Uncomment the following line if AJAX validation is needed

		$criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $id . " AND subjectType = '" . $model->subjectType . "'",
			'order'=>'ordering',
		);
		$dataProvider=new CActiveDataProvider('Question',array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));

		$this->render('view_question',array(
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionAlter($id)
	{
		$this->studyId=$id;
		if(isset($_POST['Question'])){
			$model = new Question;
			$model->attributes=$_POST['Question'];
			$criteria=new CDbCriteria;
			$criteria->condition = ('studyId = '.$_POST['Question']['studyId'] . ' AND subjectType = "ALTER"');
			$criteria->select='count(ordering) AS ordering';
			$row = Question::model()->find($criteria);
			$model->ordering = $row['ordering'];
			$model->save();
			$this->redirect(array('alter','id'=>$id));
		}else{
			$model = new Question;
			$model->subjectType = "ALTER";
			$model->studyId = $id;
		}
		// Uncomment the following line if AJAX validation is needed

		$criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $id . " AND subjectType = '" . $model->subjectType . "'",
			'order'=>'ordering',
		);
		$dataProvider=new CActiveDataProvider('Question',array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));

		$this->render('view_question',array(
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionAlterpair($id)
	{
		$this->studyId=$id;
		if(isset($_POST['Question'])){
			$model = new Question;
			$model->attributes=$_POST['Question'];
			$criteria=new CDbCriteria;
			$criteria->condition = ('studyId = '.$_POST['Question']['studyId'] . ' AND subjectType = "ALTER_PAIR"');
			$criteria->select='count(ordering) AS ordering';
			$row = Question::model()->find($criteria);
			$model->ordering = $row['ordering'];
			$model->save();
			$this->redirect(array('alterpair','id'=>$id));
		}else{
			$model = new Question;
			$model->subjectType = "ALTER_PAIR";
			$model->studyId = $id;
		}
		// Uncomment the following line if AJAX validation is needed

		$criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $id . " AND subjectType = '" . $model->subjectType . "'",
			'order'=>'ordering',
		);
		$dataProvider=new CActiveDataProvider('Question',array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));

		$this->render('view_question',array(
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionNetwork($id)
	{
		$this->studyId=$id;
		if(isset($_POST['Question'])){
			$model = new Question;
			$model->attributes=$_POST['Question'];
			$criteria=new CDbCriteria;
			$criteria->condition = ('studyId = '.$id . ' AND subjectType = "NETWORK"');
			$criteria->select='count(ordering) AS ordering';
			$row = Question::model()->find($criteria);
			$model->ordering = $row['ordering'];
			$model->save();
			$this->redirect(array('network','id'=>$id));
		}else{
			$model = new Question;
			$model->subjectType = "NETWORK";
			$model->studyId = $id;
		}
		// Uncomment the following line if AJAX validation is needed

		$criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $id . " AND subjectType = '" . $model->subjectType . "'",
			'order'=>'ordering',
		);
		$dataProvider=new CActiveDataProvider('Question',array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));

		$this->render('view_question',array(
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionPreview(){
		if(isset($_GET['questionId'])){
			$question = Question::model()->findByPk((int)$_GET['questionId']);
			$array_id = 0;
			$model[$array_id] = new Answer;
			echo "<div style='height:320px; overflow-y:auto;'>";
			$form=$this->beginWidget('CActiveForm', array(
				'id'=>'answer-form',
				'enableAjaxValidation'=>true,
			));
			$this->renderPartial('preview', array('rowColor'=>'', 'question'=>$question, 'interviewId'=>'', 'form'=>$form, 'array_id'=>$array_id, 'model'=>$model, 'ajax'=>true), false, false);

			$this->renderPartial('/interviewing/_form_'.strtolower($question->answerType), array('rowColor'=>'', 'question'=>$question, 'interviewId'=>'', 'form'=>$form, 'array_id'=>$array_id, 'model'=>$model, 'ajax'=>true), false, false);

			$skipList = array();
			if($question->dontKnowButton)
				$skipList['DONT_KNOW'] = "Don't Know";
			if($question->refuseButton)
				$skipList['REFUSE'] =  "Refuse";
			if(count($skipList) != 0){
					echo "<div clear=all>".
					CHtml::checkBoxList($array_id."_skip", array($model[$array_id]->skipReason), $skipList, array('class'=>$array_id.'-skipReason'))
					."</div>";
			}
			$this->endWidget();
			echo "</div></div><button onclick='loadData(".$question->id.  ", \"_form_question\"); return false'>Back</button>";
		}
	}

	public function actionExpression($id)
	{
		$this->studyId=$id;
		if(isset($_POST['Expression'])){
			$model = Expression::model()->findByPk((int)$_POST['Expression']['id']);
			if(!$model)
				$model = new Expression;
			$model->attributes = $_POST['Expression'];
			if(!$model->save())
				print_r($model->errors);
			else
				$this->redirect(array('expression','id'=>$model->studyId));
		}

		$model = new Expression;
		$criteria=new CDbCriteria;
		$multi = q("SELECT multiSessionEgoId FROM study WHERE id = " . $id)->queryScalar();

			$criteria=array(
				'condition'=>"studyId = " . $id,
			);

		$dataProvider=new CActiveDataProvider('Expression',array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));

		$this->render('view_expression',array(
			'multi'=>$multi,
			'studyId'=>$id,
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionOptionlist($id)
	{
		$this->studyId=$id;
		$model = AnswerList::model()->findAllByAttributes(array('studyId'=>$id));
		$this->render('view_option_list',array(
			'studyId'=>$id,
			'model'=>$model,
		));
	}

	public function actionAddUser()
	{
		if(isset($_POST['Interviewer'])){
			$model = new Interviewer;
			$model->attributes = $_POST['Interviewer'];
			if($model->save())
				Yii::app()->request->redirect("/authoring/edit/" . $model->studyId);
			else
				print_r($model->getErrors());
		}
	}
	public function actionDeleteInterviewer(){
		if(isset($_GET['interviewerId']))
			$model = Interviewer::model()->findByAttributes(array("studyId"=>$_GET['studyId'], 'interviewerId'=>$_GET['interviewerId']));
		if($model){
			$model->delete();
		}
		Yii::app()->request->redirect("/authoring/edit/" . $model->studyId);
	}

	public function actionDelete($id){
		$interviews = Interview::model()->findAllByAttributes(array("studyId"=>$id));
		if(count($interviews) > 0){
			echo "Please delete all interviews before deleting this study";
		}else{
			$study = Study::model()->findByPk($id);
			$study->delete();
			Yii::app()->request->redirect("/authoring");
		}
	}

	public function actionArchive($id){
		$interviews = Interview::model()->findAllByAttributes(array("studyId"=>$id));
		if(count($interviews) > 0){
			echo "Please delete all interviews before archiving this study";
		}else{
			$study = Study::model()->findByPk($id);
			$study->active = 0;
			$study->save();
			Yii::app()->request->redirect("/archive");
		}
	}

	public function actionDuplicate(){
		if(isset($_GET['questionId'])){
			$copy = Question::model()->findByPk((int)$_GET['questionId']);
			$model = new Question;
			$model->attributes = $copy->attributes;
			$model->title = $model->title . "_COPY";
			$model->id = null;
			$model->ordering++;
			$criteria = new CDbCriteria();
			$criteria=array(
				'condition'=>"studyId = " . $copy->studyId . " AND ordering > ".$copy->ordering ,
				'order'=>'ordering',
			);
			$models = Question::model()->findAll($criteria);
			foreach($models as $other){
				$other->ordering++;
				$other->save();
			}
			if(!$model->save())
				print_r($model->getErrors());
			else
				$this->redirect(Yii::app()->request->getUrlReferrer());
		}
	}

	public function actionImage()
	{
		if ($_FILES['file']['name']) {
			if (!$_FILES['file']['error']) {
				$name = md5(rand(100, 200));
				$ext = explode('.', $_FILES['file']['name']);
				$filename = $name . '.' . $ext[1];
				$destination = Yii::app()->basePath .'/../assets/' . $filename; //change this directory
				$location = $_FILES["file"]["tmp_name"];
				move_uploaded_file($location, $destination);
				echo '/assets/' . $filename;
			}
			else
			{
			  echo  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
			}
		}
	}

	public function actionAjaxupdate()
	{
		if(isset($_POST['Question'])){
			if(is_numeric($_POST['Question']['id']))
				$model = Question::model()->findByPk((int)$_POST['Question']['id']);
			else
				$model = new Question;
			$this->performAjaxValidation($model);
			$oldTitle = $model->title;
			if($model->answerType == "MULTIPLE_SELECTION")
				$oldTitle .= ' <div class="optionLink" style="height:20px; width:60px;float:right">Options</div>';
			$model->attributes=$_POST['Question'];
			if($model->save()){
				Study::updated($model->studyId);

			if($model->answerType == "MULTIPLE_SELECTION")
				$model->title .= ' <div class="optionLink" style="height:20px; width:60px;float:right">Options</div>';
				echo $oldTitle . ";;;" . $model->title;
			}else{
				print_r($model->getErrors());
				die();
			}
		}elseif(isset($_POST['Legend'])){
			$model = Legend::model()->findByPk((int)$_POST['Legend']['id']);
			if(!$model){
				$model = new Legend;
				$criteria=new CDbCriteria;
				$criteria->select='count(ordering) AS ordering';
				$row = Legend::model()->find($criteria);
				$model->ordering = $row['ordering'];
			}
			$model->attributes = $_POST['Legend'];
			if(!$model->save())
				throw new CHttpException(500, print_r($model->errors));
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"questionId = " . $model->questionId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('Legend',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_form_legend', array('dataProvider'=>$dataProvider, 'studyId'=> $model->studyId, 'questionId'=>$model->questionId, 'ajax'=>true), false, true);

		}elseif(isset($_POST['QuestionOption'])){
			// edit existing option
			if(is_numeric($_POST['QuestionOption']['id'])){
				$model = QuestionOption::model()->findByPk((int)$_POST['QuestionOption']['id']);
			// replace options with predefined AnswerList
			}else if($_POST['QuestionOption']['id'] == "replacePreset"){
				$studyId = $this->deleteAllOptions($_POST['questionId']);
				$list = AnswerList::model()->findByPk((int)$_POST['answerListId']);
				$optionPairs = explode(',', $list->listOptionNames);
				$ordering = 0;
				foreach($optionPairs as $option){
					$newOption = new QuestionOption;
					$newOption->studyId = $studyId;
					$newOption->questionId = $_POST['questionId'];
					list($name,$value) = preg_split('/=/', $option);
					$newOption->name = $name;
					$newOption->value = $value;
					$newOption->ordering = $ordering;
					$newOption->save();
					$ordering++;
				}
				$questionId = $_POST['questionId'];
			}else if($_POST['QuestionOption']['id'] == "replaceOther"){
				$this->deleteAllOptions($_POST['questionId']);
				$models = QuestionOption::model()->findAllByAttributes(array('questionId'=>$_POST['otherQuestionId']));
				foreach($models as $model){
					$newOption = new QuestionOption;
					$newOption->attributes = $model->attributes;
					$newOption->id = '';
					$newOption->questionId = $_POST['questionId'];
					$newOption->save();
				}
				$questionId = $_POST['questionId'];
			// create new
			}else{
				$model = new QuestionOption;
				$criteria=new CDbCriteria;
				$criteria->condition = ('questionId = '.$_POST['QuestionOption']['questionId']);
				$criteria->select='count(ordering) AS ordering';
				$row = QuestionOption::model()->find($criteria);
				$model->ordering = $row['ordering'];
			}
			if(!in_array($_POST['QuestionOption']['id'], array("replacePreset", "replaceOther"))){
				$this->performAjaxValidation($model);
				$model->attributes=$_POST['QuestionOption'];
				if(!$model->save())
					throw new CHttpException(500, print_r($model->errors));
				$questionId = $model->questionId;
			}
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"questionId = " . $questionId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('QuestionOption',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_form_option', array('dataProvider'=>$dataProvider, 'questionId'=>$questionId, 'ajax'=>true), false, true);

		}else if(isset($_POST['AlterList'])){
			// edit existing alterList entry
			if(is_numeric($_POST['AlterList']['id'])){
				$model = AlterList::model()->findByPk((int)$_POST['AlterList']['id']);
			}else{
				$model = new AlterList;
				$criteria=new CDbCriteria;
				$criteria->condition = ('studyId = '.$_POST['AlterList']['studyId']);
				$criteria->select='count(ordering) AS ordering';
				$row = AlterList::model()->find($criteria);
				$model->ordering = $row['ordering'];
			}
			$model->attributes=$_POST['AlterList'];
			$model->name = trim($model->name);
			$model->save();
			Study::updated($_POST['AlterList']['studyId']);
			$studyId = $model->studyId;
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $studyId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('AlterList',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
   			$this->renderPartial('_view_alter_list', array('dataProvider'=>$dataProvider, 'model'=>$model, 'studyId'=>$studyId, 'ajax'=>true), false, true);
		}else if(isset($_POST['AlterPrompt'])){
			// edit existing alterList entry
			if(is_numeric($_POST['AlterPrompt']['id'])){
				$model = AlterPrompt::model()->findByPk((int)$_POST['AlterPrompt']['id']);
			}else{
				$model = new AlterPrompt;
				$criteria=new CDbCriteria;
				$criteria->condition = ('studyId = '.$_POST['AlterPrompt']['studyId']);
			}
			$model->attributes=$_POST['AlterPrompt'];
			$model->save();
			Study::updated($model->studyId);
			$studyId = $model->studyId;
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $studyId,
			);
			$dataProvider=new CActiveDataProvider('AlterPrompt',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
   			$this->renderPartial('_view_alter_prompt', array('dataProvider'=>$dataProvider, 'model'=>$model, 'studyId'=>$studyId, 'ajax'=>true), false, true);
		}else if(isset($_POST['AnswerList'])){
			$model = new AnswerList;
			$model->attributes = $_POST['AnswerList'];
			if($model->save())
				$this->redirect(Yii::app()->request->urlReferrer);
			else
				print_r($model->getErrors());
		}else if(isset($_GET['answerListId'])){
			$answerList = AnswerList::model()->findByPk((int)$_GET['answerListId']);
			if($answerList->listOptionNames){
				if(strstr($answerList->listOptionNames, ','))
					$listOptions = preg_split('/,/', $answerList->listOptionNames);
				else
					$listOptions[] = $answerList->listOptionNames;
			}else{
				$listOptions = array();
			}
			$options = array();
			foreach($listOptions as &$listOption){
				if($listOption){
					list($key, $value) = preg_split('/=/', $listOption);
					if(isset($_GET['oldKey']) && isset($_GET['oldValue']) && $_GET['oldKey'] == $key && isset($_GET['key']) && isset($_GET['value'])){
						$options[$_GET['key']] = $_GET['value'];
						$listOption = $_GET['key'] ."=".  $_GET['value'];
					}else{
						$options[$key] = $value;
					}
				}
			}
			if(isset($_GET['key']) && isset($_GET['value']) && !(isset($_GET['oldKey']) && isset($_GET['oldValue']))){
				$listOptions[] = $_GET['key'] ."=". $_GET['value'];
				$options[$_GET['key']] = $_GET['value'];
			}
			$answerList->listOptionNames = implode(',', $listOptions);
			$answerList->save();
				$this->renderPartial('_form_option_list', array('options'=>$options, 'answerList'=>$answerList, 'ajax'=>true), false, false);

		}
		Yii::app()->end();
	}
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionAjaxdelete()
	{
		if(isset($_GET['Question'])){
			$model = Question::model()->findByPk((int)$_GET['Question']['id']);
			if($model){
				$studyId = $model->studyId;
				$ordering = $model->ordering;
				$subjectType = $model->subjectType;
				$questionId = $model->id;
				if(file_exists(Yii::app()->basePath."/../audio/".$model->studyId . "/" . $model->subjectType . "/" . $model->id . ".mp3"))
					unlink(Yii::app()->basePath."/../audio/".$model->studyId . "/" . $model->subjectType . "/" . $model->id . ".mp3");
				$model->delete();
				Question::sortOrder($ordering, $studyId);
				$expressions = Expression::model()->findAllByAttributes(array('questionId'=>$questionId));
				foreach($expressions as $expression){
					$expression->delete();
				}
				Study::updated($model->studyId);
			}
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $studyId ." AND subjectType = '" . $subjectType ."'",
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('Question',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));

			$this->renderPartial('_view_question_list', array('dataProvider'=>$dataProvider, 'studyId'=>$studyId, 'ajax'=>true), false, true);
		}else if(isset($_GET['QuestionOption'])){
			if($_GET['QuestionOption']['id'] != 'all'){
				$model = QuestionOption::model()->findByPk((int)$_GET['QuestionOption']['id']);
				if($model){
					$questionId = $model->questionId;
					$ordering = $model->ordering;
					if(file_exists(Yii::app()->basePath."/../audio/".$model->studyId . "/OPTION/". $model->id . ".mp3"))
						unlink(Yii::app()->basePath."/../audio/".$model->studyId . "/OPTION/". $model->id . ".mp3");
					$model->delete();
					Study::updated($model->studyId);
					QuestionOption::sortOrder($ordering, $questionId);
				}
			}else{
				$this->deleteAllOptions($_GET['questionId']);
				$questionId = $_GET['questionId'];
				#OK FOR SQL INJECTION
				$params = new stdClass();
				$params->name = ':questionId';
				$params->value = $_GET['questionId'];
				$params->dataType = PDO::PARAM_INT;
				$studyId = q("SELECT studyId FROM question WHERE id = :questionId", array($params) )->queryScalar();
				Study::updated($studyId);
			}

			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"questionId = " . $questionId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('QuestionOption',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));

			$this->renderPartial('_form_option', array('dataProvider'=>$dataProvider, 'questionId'=>$questionId, 'ajax'=>true), false, true);

		}else if(isset($_GET['Legend'])){
			if($_GET['Legend']['id'] != 'all'){
				$model = Legend::model()->findByPk((int)$_GET['Legend']['id']);
				if($model){
					$questionId = $model->questionId;
					$ordering = $model->ordering;
					$studyId = $model->studyId;
					$model->delete();
					Legend::sortOrder($ordering, $questionId);
				}
			}else{
				$this->deleteAllLegend($_GET['questionId']);
			}

			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"questionId = " . $_GET['questionId'],
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('Legend',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));

			$this->renderPartial('_form_legend', array('dataProvider'=>$dataProvider, 'questionId'=>$_GET['questionId'],  'studyId'=>$studyId, 'ajax'=>true), false, true);

		}else if(isset($_GET['AlterList'])){
			if($_GET['AlterList']['id'] != 'all'){
				$model = AlterList::model()->findByPk((int)$_GET['AlterList']['id']);
				if($model){
					$studyId = $model->studyId;
					$ordering = $model->ordering;
					$model->delete();
					AlterList::sortOrder($ordering, $studyId);
				}
			}else{
				$this->deleteAllAlters($_GET['studyId']);
				$studyId = $_GET['studyId'];
			}
			Study::updated($studyId);
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $studyId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('AlterList',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_view_alter_list', array('dataProvider'=>$dataProvider, 'studyId'=>$studyId, 'ajax'=>true), false, true);
		}else if(isset($_GET['AlterPrompt'])){
			$model = AlterPrompt::model()->findByPk((int)$_GET['AlterPrompt']['id']);
			if($model){
				$studyId = $model->studyId;
				$model->delete();
				Study::updated($model->studyId);
			}
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $studyId,
			);
			$dataProvider=new CActiveDataProvider('AlterPrompt',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_view_alter_prompt', array('dataProvider'=>$dataProvider, 'studyId'=>$studyId, 'ajax'=>true), false, true);
		}else if(isset($_GET['AnswerList'])){
			$model = AnswerList::model()->findByPk((int)$_GET['AnswerList']['id']);
			if($model)
				$model->delete();
			$this->redirect(Yii::app()->request->urlReferrer);
		}else if(isset($_GET['answerListId'])){
			$answerList = AnswerList::model()->findByPk((int)$_GET['answerListId']);
			$listOptions = preg_split('/,/', $answerList->listOptionNames);
			$options = array();
			$newList = array();
			foreach($listOptions as $listOption){
				list($key, $value) = preg_split('/=/', $listOption);
				if(!($key == $_GET['key'] && $value == $_GET['value']) && $listOption){
					$newList[] = $listOption;
					$options[$key] = $value;
				}
			}
			$answerList->listOptionNames = implode(',', $newList);
			$answerList->save();
				$this->renderPartial('_form_option_list', array('options'=>$options, 'answerList'=>$answerList, 'ajax'=>true), false, false);
		}else if(isset($_GET['expressionId'])){
			$model = Expression::model()->findByPk((int)$_GET['expressionId']);
			if($model)
				$model->delete();
		}
	}
	/**
	* Loads form by ajax
	*/
	public function actionAjaxload()
	{
		Yii::app()->clientScript->scriptMap['jquery.js'] = false;
		Yii::app()->clientScript->scriptMap['jquery.min.js'] = false;
		if(isset($_GET['form'])){
			if($_GET['form'] == "_form_question"){
				if( !is_numeric($_GET['questionId']) ){
					throw new CHttpException(500,"Invalid questionId specified ".$_GET['questionId']." !");
				}
				$model = Question::model()->findByPk((int)$_GET['questionId']);

				$this->renderPartial($_GET['form'], array('model'=>$model, 'ajax'=>true), false, true);
			}else if($_GET['form'] == "_form_alter_list_edit"){
				if( !is_numeric($_GET['alterListId']) ){
					throw new CHttpException(500,"Invalid alterListId specified ".$_GET['alterListId']." !");
				}
				$model = AlterList::model()->findByPk((int)$_GET['alterListId']);
				$this->renderPartial($_GET['form'], array('model'=>$model, 'ajax'=>true, 'studyId'=>$model->studyId), false, true);
			}else if($_GET['form'] == "_form_alter_prompt_edit"){
				if( !is_numeric($_GET['alterPromptId']) ){
					throw new CHttpException(500,"Invalid alterPromptId specified ".$_GET['alterPromptId']." !");
				}
				$model = AlterPrompt::model()->findByPk((int)$_GET['alterPromptId']);
				$this->renderPartial($_GET['form'], array('model'=>$model, 'ajax'=>true, 'studyId'=>$model->studyId), false, true);
			}else if($_GET['form'] == "_form_option_edit"){
				if( !is_numeric($_GET['optionId']) ){
					throw new CHttpException(500,"Invalid optionId specified ".$_GET['optionId']." !");
				}
				$model = QuestionOption::model()->findByPk((int)$_GET['optionId']);

				$this->renderPartial($_GET['form'], array('model'=>$model, 'ajax'=>true, 'questionId'=>$model->questionId), false, true);
			}else if($_GET['form'] == "_form_legend_edit"){
				if(isset($_GET['legendId']))
					$model = Legend::model()->findByPk((int)$_GET['legendId']);
				if(!$model)
					$model = new Legend;
				$this->renderPartial($_GET['form'], array('model'=>$model, 'ajax'=>true, 'questionId'=>$_GET['questionId'], 'studyId'=>$_GET['studyId']), false, true);
			}else if($_GET['form'] == "_form_expression_text" || $_GET['form'] == "_form_expression_counting" || $_GET['form'] == "_form_expression_comparison" || $_GET['form'] == "_form_expression_compound"){
				$questionId = "";
				if(isset($_GET['questionId']) && is_numeric($_GET['questionId']) && $_GET['questionId'] != 0)
					$question = Question::model()->findByPk((int)$_GET['questionId']);
				else
					$question = new Question;
				if(isset($_GET['id']))
					$model = Expression::model()->findbyPk((int)$_GET['id']);
				else
					$model = new Expression;

				if(isset($_GET['expressionId']))
					$expression = Expression::model()->findbyPk((int)$_GET['expressionId']);
				else
					$expression = new Expression;
				$this->renderPartial($_GET['form'], array('model'=>$model, 'expression'=>$expression, 'ajax'=>true, 'question'=>$question, 'studyId'=>(int)$_GET['studyId']), false, false);
			}else if($_GET['form'] == "_form_option"){

				$criteria=new CDbCriteria;
				$criteria=array(
					'condition'=>"questionId = " . $_GET['questionId'],
					'order'=>'ordering',
				);

				$dataProvider=new CActiveDataProvider('QuestionOption',array(
					'criteria'=>$criteria,
					'pagination'=>false,
				));

				$this->renderPartial("_form_option", array('dataProvider'=>$dataProvider, 'questionId'=>$_GET['questionId'], 'ajax'=>true), false, true);
			}else if($_GET['form'] == "_form_legend"){

				$criteria=new CDbCriteria;
				$criteria=array(
					'condition'=>"questionId = " . $_GET['questionId'],
					'order'=>'ordering',
				);
				$studyId = q("SELECT studyId FROM question WHERE id = " . $_GET['questionId'])->queryScalar();

				$dataProvider=new CActiveDataProvider('Legend',array(
					'criteria'=>$criteria,
					'pagination'=>false,
				));
				$this->renderPartial("_form_legend", array('dataProvider'=>$dataProvider, 'studyId'=>$studyId, 'questionId'=> $_GET['questionId'], 'ajax'=>true), false, true);
			}else if($_GET['form'] == "_form_option_list"){
				$answerList = AnswerList::model()->findByPk((int)$_GET['answerListId']);
				$listOptions = preg_split('/,/', $answerList->listOptionNames);
				$options = array();
				foreach($listOptions as $listOption){
					if($listOption){
						list($key, $value) = preg_split('/=/', $listOption);
						$options[$key] = $value;
					}
				}
				$this->renderPartial($_GET['form'], array('options'=>$options, 'answerList'=>$answerList, 'ajax'=>true), false, true);
			}else if($_GET['form'] == "_form_option_list_edit"){
				$this->renderPartial($_GET['form'], array('answerListId'=>$_GET['answerListId'], 'key'=>$_GET['key'], 'value'=>$_GET['value'], 'ajax'=>true), false, true);
			}
		}
		Yii::app()->end();
	}

	public function actionAjaxreorder(){
		if(isset($_GET['reorder'])){
			foreach($_GET['reorder'] as $order=>$questionId){
				$data = array(
					'ordering'=>$order
				);
				u('question', $data, "id = " . $questionId);
			}
		}
	}

	public function actionAjaxshowlink()
	{
		if(isset($_GET['alterListId'])){

			$alter = AlterList::model()->findByPk((int)$_GET['alterListId']);
			$key = "key=".User::hashPassword($alter->email);
			echo "<div style='clear:both'><label>Authorized Link</label><br>" . Yii::app()->getBaseUrl(true) . "/interviewing/".$alter->studyId."?".$key."</div>";
		}
	}
	public function actionAjaxmoveup()
	{
		Yii::app()->clientScript->scriptMap['jquery.js'] = false;
		Yii::app()->clientScript->scriptMap['jquery.min.js'] = false;
		if(isset($_GET['optionId'])){
			QuestionOption::moveUp($_GET['optionId']);
			$model = QuestionOption::model()->findByPk((int)$_GET['optionId']);
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"questionId = " . $model->questionId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('QuestionOption',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_form_option', array('dataProvider'=>$dataProvider, 'questionId'=>$model->questionId, 'ajax'=>true), false, true);
		}else if(isset($_GET['legendId'])){
			Legend::moveUp($_GET['legendId']);
			$model = Legend::model()->findByPk((int)$_GET['legendId']);
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"questionId = " . $model->questionId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('Legend',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_form_legend', array('dataProvider'=>$dataProvider, 'questionId'=>$model->questionId,  'studyId'=>$model->studyId, 'ajax'=>true), false, true);
		}else if(isset($_GET['alterListId'])){
			AlterList::moveUp($_GET['alterListId']);
			$model = AlterList::model()->findByPk((int)$_GET['alterListId']);
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $model->studyId,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('AlterList',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_view_alter_list', array('dataProvider'=>$dataProvider, 'studyId'=>$model->studyId, 'ajax'=>true), false, true);
		}else if(isset($_GET['questionId'])){
			Question::moveUp($_GET['questionId']);
			$model = Question::model()->findByPk((int)$_GET['questionId']);
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $model->studyId ." AND subjectType = '" . $model->subjectType ."'",
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('Question',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
			$this->renderPartial('_view_question_list', array('dataProvider'=>$dataProvider, 'studyId'=>$model->studyId, 'ajax'=>true), false, true);
		}else if(isset($_GET['answerListId'])){
			$answerList = AnswerList::model()->findByPk((int)$_GET['answerListId']);
			$listOptions = preg_split('/,/', $answerList->listOptionNames);
			$options = array();
			$newList = array();
			foreach($listOptions as $listOption){
				if(!$listOption)
					break;
				list($key, $value) = preg_split('/=/', $listOption);
				if(!($key == $_GET['key'] && $value == $_GET['value'])){
					$newList[] = $listOption;
					$options[$key] = $value;
				}else{
					$newPop = array_pop($newList);
					list($oldKey, $oldValue) = preg_split('/=/', $newPop);
					unset($options[$oldKey]);
					$newList[] = $listOption;
					$options[$key] = $value;
					$newList[] = $newPop;
					$options[$oldKey] = $oldValue;

				}
			}
			$answerList->listOptionNames = implode(',', $newList);
			$answerList->save();
				$this->renderPartial('_form_option_list', array('options'=>$options, 'answerList'=>$answerList, 'ajax'=>true), false, false);
		}
	}
	/**
	 * Performs the AJAX validation.
	 * @param Study $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	protected function deleteAllOptions($questionId){
		$models = QuestionOption::model()->findAllByAttributes(array('questionId'=>$questionId));
		foreach($models as $model){
			$studyId = $model->studyId;
			if(file_exists(Yii::app()->basePath."/../audio/".$studyId . "/OPTION/". $model->id . ".mp3"))
				unlink(Yii::app()->basePath."/../audio/".$studyId . "/OPTION/". $model->id . ".mp3");
			$model->delete();
		}
		if(isset($studyId))
			return $studyId;
	}

	protected function deleteAllAlters($studyId){
		$models = AlterList::model()->findAllByAttributes(array('studyId'=>$studyId));
		foreach($models as $model){
			$model->delete();
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Study the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Study::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

}
