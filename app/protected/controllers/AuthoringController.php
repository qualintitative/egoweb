<?php
namespace app\controllers;

use app\models\ResendVerificationEmailForm;
use app\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\helpers\Tools;
use app\models\User;
use app\models\Study;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Question;
use app\models\QuestionOption;
use app\models\Expression;
use app\models\AlterPrompt;
use app\models\Interview;
use app\models\Interviewer;
use app\models\AlterList;

/**
 * Site controller
 */
class AuthoringController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    public function actionImportlist($id)
	{
		if(!is_uploaded_file($_FILES['userfile']['tmp_name'])) //checks that file is uploaded
			die("Error importing Participant list");
        $nameGenQs = Question::findAll(["studyId"=>$id, "subjectType"=>"NAME_GENERATOR"]);
        $nameGenQIds = array();
        foreach($nameGenQs as $nameGenQ){
            $nameGenQIds[$nameGenQ->title] = $nameGenQ->id;
        }
		$file = fopen($_FILES['userfile']['tmp_name'],"r");
        $results = Interviewer::findAll(["studyId"=>$id]);
        $interviewers = array();
        foreach($results as $result){
            $user = User::findOne($result->interviewerId);
            $interviewers[$result->interviewerId] = $user->name;
        }
		while(! feof($file)){
			$data = fgetcsv($file);
			if(isset($data[0]) && $data[0]){
                $alterlist = AlterList::findAll(['studyId = '.$id]);
				$model = new AlterList;
				$model->ordering = count($alterlist);
				$model->name = trim($data[0]);
				$model->email = isset($data[1]) ? $data[1] : "";
                $interviewerColumn = false;
                $nameGenColumn = false;
                if(count($data) == 3){
                    $nameGenColumn = 2;
                }else if(count($data) == 4){
                    $interviewerColumn = 2;
                    $nameGenColumn = 3;
                }
                $model->nameGenQIds = '';
                if($nameGenColumn && stristr($data[$nameGenColumn], ";")){
                    $Qs = explode(";", $data[$nameGenColumn]);
                    $qIds = array();
                    foreach($Qs as $title){
                        if(isset($nameGenQIds[$title]))
                        $qIds[] = $nameGenQIds[$title];
                    }
                    $model->nameGenQIds = implode(",", $qIds);
                }elseif(isset($nameGenQIds[$data[$nameGenColumn]])){
                    $model->nameGenQIds = $nameGenQIds[$data[$nameGenColumn]];
                }
                if($interviewerColumn && isset($data[$interviewerColumn])){
                    $model->interviewerId =  array_search($data[$interviewerColumn], $interviewers);
                }
				$model->studyId = $id;
			    if(!$model->save()){
                    echo $model->nameGenQIds;
                    print_r($model->errors);
                    die();
                }
			}
		}
		fclose($file);
        return $this->redirect(Yii::$app->request->referrer);
	}

	public function actionImportprompts()
	{
		if(!is_uploaded_file($_FILES['userfile']['tmp_name'])) //checks that file is uploaded
			die("Error importing Variable Alter Prompts");
		$file = fopen($_FILES['userfile']['tmp_name'],"r");
		while(! feof($file)){
			$data = fgetcsv($file);
			if(isset($data[0]) && $data[0]){
				$model = new AlterPrompt;
				$model->studyId = $_POST['studyId'];
                $model->questionId = $_POST['questionId'];
				$model->afterAltersEntered = trim($data[0]);
				$model->display = isset($data[1]) ? $data[1] : "";
				$model->save();
			}
		}
		fclose($file);
        $prompts = AlterPrompt::findAll(['studyId = '.$_POST['studyId']])->asArray();
        return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($prompts)]);
	}

    public function actionCreate()
	{
		$study = new Study;
		if(isset($_POST['Study']))
		{
			$study->attributes = $_POST['Study'];
            if ($study->save()) {
                return $this->response->redirect(Url::toRoute('/authoring/' . Yii::$app->db->getLastInsertID()));
            }
		}
	}

    public function actionReplicate($id)
    {
        $study = Study::findOne($id);
        $study->name = $study->name . "_copy";
        $questions = Question::findAll(array('studyId'=>$id));
        $options = QuestionOption::findAll(array('studyId'=>$id));
        $expressions = Expression::findAll(array('studyId'=>$id));
        $alterPrompts = AlterPrompt::findAll(array('studyId'=>$id));
        $alterLists = AlterList::findAll(array('studyId'=>$id));
        $data = Study::replicate($study, $questions, $options, $expressions, $alterPrompts, $alterLists);
        return $this->response->redirect(Url::toRoute('/authoring/' . $data['studyId']));
    }

    public function actionIndex($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        if ($study->load(Yii::$app->request->post()) && $study->validate()) {
            $study->attributes = $_POST['Study'];
            $study->hideEgoIdPage = isset($_POST['Study']['hideEgoIdPage']);
            $study->fillAlterList = isset($_POST['Study']['fillAlterList']);
            $study->restrictAlters = isset($_POST['Study']['restrictAlters']);
            $study->useAsAlters = isset($_POST['Study']['useAsAlters']);
            if($study->save()){
                return $this->response->redirect(Url::toRoute('/authoring/' . $study->id));
			}else{
                print_r($study->errors);
                die();
            }
        }
        $qs = ArrayHelper::map(
            Question::findAll(["studyId"=>$study->id, "subjectType"=>"EGO_ID"])
        , 'id','title');
        $egoIdOptions = [];
        $egoIdOptions[] = ["value"=>"", "text"=>""];
        foreach($qs as $i=>$q){
            $egoIdOptions[] = ["value"=>$i, "text"=>$q];
        }
        $interviews = Interview::find()->where(['studyId'=>$id])->count();
        $study = Study::find()->where(["id"=>$id])->asArray()->one();
        return $this->render('index',["study"=>$study, "egoIdOptions"=>$egoIdOptions, "interviews"=>$interviews]);
    }



    public function actionEgo_id($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        $questions = Question::find()->where(["studyId"=>$id, "subjectType"=>"EGO_ID"])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        if (Yii::$app->request->post()){
            if ($_POST['Question']['id']) {
                $question = Question::findOne($_POST['Question']['id']);
            }else{
                $question = new Question;
                $question->ordering = count($questions);
            }
            if ($question->load(Yii::$app->request->post()) && $question->validate()) {
                $question->attributes = $_POST['Question'];
                $question->askingStyleList = isset($_POST['Question']['askingStyleList']);
                $question->dontKnowButton = isset($_POST['Question']['dontKnowButton']);
                $question->refuseButton = isset($_POST['Question']['refuseButton']);
                $question->restrictList = isset($_POST['Question']['restrictList']);
                $question->autocompleteList = isset($_POST['Question']['autocompleteList']);
                $question->prefillList = isset($_POST['Question']['prefillList']);

                if($question->save()){
                    return $this->response->redirect(Url::toRoute('/authoring/ego_id/' . $study->id));
                }else{
                    print_r($question->errors);
                    die();
                }
            }
        }
        foreach(Question::EGOID_ANSWERTYPES as $a){
            $answerTypes[] = ["value"=>$a, "text"=>$a];
        }
        $subjectTypes = false;

        $new_question = new Question;
        $new_question->studyId = $study->id;
        $new_question->subjectType = "EGO_ID";
        $new_question = $new_question->toArray();

        $expressions = Expression::find()->where(["studyId"=>$study->id])->asArray()->all();
        return $this->render('questions',["study"=>$study, "questions"=>$questions, "new_question"=>$new_question, "answerTypes"=>$answerTypes, "subjectTypes"=>$subjectTypes, "expressions"=>$expressions]);
    }

    // convertss to new master list format from older format
    public function actionConvert($id)
	{
        $study = Study::findOne($id);
        $ego_questions = array();
        $alter_questions = array();
        $alter_pair_questions = array();
        $network_questions = array();
        $questions = Question::find()->where(["studyId"=>$id])->andWhere(["<>", "subjectType", "EGO_ID"])->orderBy(["ordering"=>"ASC"])->all();
        foreach($questions as $question){
            if($question->subjectType == "EGO")
                $ego_questions[] = $question;
            if($question->subjectType == "ALTER")
                $alter_questions[] = $question;
            if($question->subjectType == "ALTER_PAIR")
                $alter_pair_questions[] = $question;
            if($question->subjectType == "NETWORK")
                $network_questions[] = $question;
        }
        $i = count($ego_questions);
        $model = new Question;
        $model->attributes = array(
            'subjectType' => "NAME_GENERATOR",
            'prompt' => $study->alterPrompt,
            'studyId' => $id,
            'title' => "ALTER_PROMPT",
            'answerType' => "NAME_GENERATOR",
            'ordering' => $i,
        );
        $model->save();
        $nameGenQId = $model->id;
        $interviews = Interview::findAll(array("studyId"=>$study->id));
        foreach($interviews as $interview){
          $alters = Alters::find()
          ->where(new \yii\db\Expression("FIND_IN_SET(" . $interview->id .", interviewId)"))
          ->orderBy(['ordering'=>'ASC'])
          ->all();
          foreach($alters as $alter){
            $alter->nameGenQIds = $nameGenQId;
            $alter->save();
          }
        }
        $i++;
        foreach($alter_questions as $question){
            $question->ordering = $i + $question->ordering;
            $question->save();
        }
        $i = $i + count($alter_questions);
        foreach($alter_pair_questions as $question){
            $question->ordering = $i + $question->ordering;
            $question->save();
        }
        $i = $i + count($alter_pair_questions);
        foreach($network_questions as $question){
            $question->ordering = $i + $question->ordering;
            $question->save();
        }
        return $this->response->redirect(Url::toRoute('/authoring/questions/' . $id));
    }

    public function actionQuestions($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        if (Yii::$app->request->post()){
            if ($_POST['Question']['id']) {
                $question = Question::findOne($_POST['Question']['id']);
            }else{
                $question = new Question;
                $question->ordering = count($questions);
            }
            if ($question->load(Yii::$app->request->post()) && $question->validate()) {
                
                $question->askingStyleList = isset($_POST['Question']['askingStyleList']);
                $question->dontKnowButton = isset($_POST['Question']['dontKnowButton']);
                $question->refuseButton = isset($_POST['Question']['refuseButton']);
                $question->restrictList = isset($_POST['Question']['restrictList']);
                $question->autocompleteList = isset($_POST['Question']['autocompleteList']);
                $question->prefillList = isset($_POST['Question']['prefillList']);
                if($question->save()){
                    $study->save();
                    return $this->response->redirect(Url::toRoute('/authoring/questions/' . $study->id));
                }else{
                    print_r($question->errors);
                    die();
                }
            }
        }
        $questions = Question::find()->where(["studyId"=>$id])->andWhere(['!=', 'subjectType', 'EGO_ID'])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        foreach($questions as &$question){
            $question['options'] = QuestionOption::find()->where(['questionId'=>$question['id']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
            if ($question['subjectType'] == "NAME_GENERATOR") {
                $question['alterPrompts'] = AlterPrompt::find()->where(array('questionId'=>$question['id']))->orderBy(["afterAltersEntered"=>"ASC"])->asArray()->all();
            }
        }
        $new_question = new Question;
        $new_question->studyId = $study->id;
        $new_question = $new_question->toArray();
        foreach(Question::ANSWERTYPES as $a){
            $answerTypes[] = ["value"=>$a, "text"=>$a];
        }
        foreach(Question::SUBJECTTYPES as $s){
            $subjectTypes[] = ["value"=>$s, "text"=>$s];
        }
        $expressions = Expression::find()->where(["studyId"=>$study->id])->asArray()->all();

        return $this->render('questions',["study"=>$study, "questions"=>$questions, "new_question"=>$new_question, "answerTypes"=>$answerTypes, "subjectTypes"=>$subjectTypes, "expressions"=>$expressions]);
    }

    public function actionParticipants($id)
    {
        $study = Study::findOne($id)->toArray();
        $this->view->title = $study['name'];
        $result = Interviewer::find()->where(["studyId"=>$id])->all();
        
        $interviewers = [];
        $alterList = [];
        $interviewerList = [];
        $users = [];
        $userIds = [];
        foreach($result as $interviewer){
            $user = User::findOne($interviewer->interviewerId);
            if ($user) {
                $userIds[] = $user->id;
                $interviewerList[$user->id] = $user->name;
                $interviewers[] = ["id"=>$user->id,"interviewer"=>$user->name, "role"=>User::roles()[$user->permissions]];
            }
        }

        $result = AlterList::find()->where(["studyId"=>$id])->all();
        foreach ($result as $item) {
            $interviewer = "";
            if(isset($interviewerList[$item->interviewerId]))
                $interviewer = $interviewerList[$item->interviewerId];
            $alterList[] = ["id"=>$item->id, "name"=>$item->name, "email"=>$item->email, "nameGenQIds"=>$item->nameGenQIds, "nameGenQIdsArray"=>explode(",",$item->nameGenQIds), "interviewerId"=>$item->interviewerId];
        }
        $result = User::find()->where(['<=', 'permissions', 5])->andWhere(['not', ['id'=>$userIds]])->all();
        foreach ($result as $item) {
            $users[] = $item->toArray();
        }
        $questions = Question::find()->where(["subjectType"=>"NAME_GENERATOR", "studyId"=>$id])->asArray()->all();
        return $this->render('participants',["study"=>$study, "interviewers"=>$interviewers, "alterList"=>$alterList, "users"=>$users, "questions"=>$questions]);
    }

    public function actionExpressions($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        $studyNames = [];
        if($study->multiSessionEgoId){
            $multiQs = $study->multiIdQs();
            foreach($multiQs as $q){
                $studyIds[] = $q->studyId;
                $s = Study::findOne($q->studyId);
                $studyNames[$q->studyId] = $s->name;
            }
        }else{
            $studyIds = $id;
        }
        if(isset($_POST['Expression'])){
            if($_POST['Expression']['id'])
                $expression = Expression::findOne($_POST['Expression']['id']);
            else
                $expression = new Expression;
            $expression->attributes = $_POST['Expression'];
            if($expression->save()){
                if($_POST['Expression']['id'])
                    $expId = $expression->id;
                else
                    $expId = Yii::$app->db->getLastInsertID();
                $study->save();
                Yii::$app->session->setFlash('success', 'Expression saved.');
                return $this->response->redirect(Url::toRoute('/authoring/expressions/' . $study->id . "#/" . $expId));
			}else{
				print_r($expression->errors);
				die();
			}
		}
        $result = Expression::find()->where(["studyId"=>$id])->all();
        $new_expression = new Expression;
        $new_expression->name = "";
        $new_expression->id = 0;
        $new_expression->studyId = $id;
        $new_expression->operator = "Some";

        $new_expression->questionId = null;
        $new_expression->resultForUnanswered = 0;
        $expressions[0] = $new_expression->toArray();
        $countQuestions = [];
        $countExpressions = [];
        foreach($result as $expression){
            $expressions[$expression->id] = $expression->toArray();
            if($expression->type == "Counting"){
                $countExpressions[] = $expression->toArray();
            }
        }
        $result = Question::find()->where(["studyId"=>$studyIds])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        $questions = [];
        $nameGenQuestions = [];
        foreach($result as $question){
            if($question['subjectType'] == "NAME_GENERATOR")
                $nameGenQuestions[] = $question;
            if($question['answerType'] == "NO_RESPONSE" ||
                $question['subjectType'] == "NAME_GENERATOR" ||
                $question['subjectType'] == "MERGE_ALTER" ||
                $question['subjectType'] == "NETWORK")
                continue;
            if($question['answerType'] == "NUMERICAL" || $question['answerType'] == "RANDOM_NUMBER" || $question['answerType'] == "STORED_VALUE")
                $countQuestions[] = $question;
            if($study->multiSessionEgoId)
                $question['title'] = $studyNames[$question['studyId']] .":".$question['title'];
            $questions[$question['id']] = $question;
            $questions[$question['id']]['optionsList'] = QuestionOption::find()->where(["questionId"=>$question['id']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        }
        return $this->render('expressions',["study"=>$study->toArray(), "expressions"=>$expressions, "questions"=>$questions, "nameGenQuestions"=>$nameGenQuestions, "countExpressions"=>$countExpressions, "countQuestions"=>$countQuestions]);
    }

    

    public function actionAddinterviewer($id)
	{
        $interviewer = new Interviewer;
		if ($interviewer->load(Yii::$app->request->post())){
            if($interviewer->save())
                return $this->response->redirect(Url::toRoute('/authoring/participants/' . $id));
			else
				print_r($interviewer->errors);
		}
	}

    public function actionDeleteInterviewer($id){
        if (isset($_POST['Interviewer']['id'])) {
            $interviewer = Interviewer::findOne(array("studyId"=>$id, 'interviewerId'=>$_POST['Interviewer']['id']));
            if ($interviewer) {
                $interviewer->delete();
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
	}

    public function actionDelete($id){
		$interviews = Interview::findAll(array("studyId"=>$id));
		if(count($interviews) > 0){
			echo "Please delete all interviews before deleting this study";
		}else{
			$study = Study::findOne($id);
			$study->delete();
            return $this->response->redirect(Url::toRoute('/admin'));
		}
	}

    public function actionDuplicatequestion($id){
		if(isset($_POST['questionId'])){
            $copy = false;
            $questions = Question::find()->where(['studyId'=>$id])->orderBy(["ordering"=>"ASC"])->all();
            foreach($questions as $question){
                if($copy){
                    $question->ordering++;
                    $question->save();
                }
                if($question->id == $_POST['questionId'])
                    $copy = $question;
            }
			$model = new Question;
			$model->attributes = $copy->attributes;
			$model->title = $model->title . "_COPY";
			$model->id = null;
			$model->ordering++;
            if (!$model->save()) {
                print_r($model->getErrors());
            } else {
                $study->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
		}
	}

    public function actionAjaxupdate($id)
    {
        $study = Study::findOne($id);
        if(isset($_POST['Question'])){
			if(is_numeric($_POST['Question']['id']))
				$question = Question::findOne($_POST['Question']['id']);
			else
				$question = new Question;
			$question->attributes = $_POST['Question'];
			if($question->save()){
                $study->save();
			}else{
				print_r($question->errors);
				die();
			}
		}elseif(isset($_POST['Study'])){
            $study = Study::findOne($_POST['Study']['id']);
            $study->attributes = $_POST['Study'];
            if($study->save()){
			}else{
				print_r($study->errors);
				die();
			}
		}elseif(isset($_POST['QuestionOption'])){
            $options = json_decode($_POST['options']);
            if(is_numeric($_POST['QuestionOption']['id']) && $_POST['QuestionOption']['id'] != 0){
                $option = QuestionOption::findOne($_POST['QuestionOption']['id']);
            }else{
                if($_POST['QuestionOption']['id'] == "replaceOther"){
                    $oldOptions = QuestionOption::findAll(array('questionId'=>$_POST['QuestionOption']['questionId']));
                    foreach($oldOptions as $option){
                        $option->delete();
                    }
                    $models = QuestionOption::findAll(array('questionId'=>$_POST['QuestionOption']['value']));
                    foreach($models as $model){
                        $newOption = new QuestionOption;
                        $newOption->attributes = $model->attributes;
                        $newOption->id = '';
                        $newOption->questionId = $_POST['QuestionOption']['questionId'];
                        $newOption->save();
                    }
                    $study->save();
                    $options = QuestionOption::find()->where(array('questionId'=>$_POST['QuestionOption']['questionId']))->asArray()->all();
                    return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($options)]);
                }else{
                    $option = new QuestionOption;
                    $option->ordering = count($options);
                    $option->studyId = $id;
                }
             }
             $option->attributes = $_POST['QuestionOption'];
             if($option->save()){
                $option = $option->toArray();
                $options[] = $option;
                $study->save();
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($options)]);
            }else{
                print_r($option->errors);
                die();
            }
        }else if(isset($_POST['AlterList'])){
			if(isset($_POST['AlterList']['id'])){
				$alterList = AlterList::findOne($_POST['AlterList']['id']);
			}else{
				$alterList = new AlterList;
				$aList = AlterList::findAll(["studyId"=>$id]);
				$alterList->ordering = count($aList);
			}
			$alterList->attributes=$_POST['AlterList'];
     		$alterList->name = trim($alterList->name);
            $alterList->studyId = $id;
            $study->save();
            if($alterList->save())
                return $this->redirect(Yii::$app->request->referrer);
            else
                print_r($alterList->errors);
        }elseif(isset($_POST['AlterPrompt'])){
            $prompts = json_decode($_POST['prompts']);
            if($_POST['AlterPrompt']['id']){
                $prompt = AlterPrompt::findOne($_POST['AlterPrompt']['id']);
             }else{
                 $prompt = new AlterPrompt; 
                 $prompt->studyId = $id;
             }
             $prompt->attributes = $_POST['AlterPrompt'];
             if($prompt->save()){
                $study->save();
                $prompt = $prompt->toArray();
                $prompts[] = $prompt;
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($prompts)]);
            }else{
                print_r($prompt->errors);
                die();
            }
        }
    }

    public function actionAjaxdelete($id)
    {
        $study = Study::findOne($id);
        if(isset($_POST['Question'])){
			$question = Question::findOne($_POST['Question']['id']);
            if ($question) {           
                $expressions = Expression::findAll(array('questionId'=>$question->id));
                foreach ($expressions as $expression) {
                    $expression->delete();
                }
                $options = QuestionOption::findAll(array('questionId'=>$question->id));
                foreach ($options as $option) {
                    $option->delete();
                }
                $questions = Question::find()->where(["studyId"=>$id])->andWhere(['>', 'ordering', $question->ordering])->all();
                foreach($questions as $q){
                    $q->ordering--;
                    $q->save();
                }
                $question->delete();
                $study->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
        }elseif (isset($_POST['QuestionOption']) && isset($_POST['QuestionOption']['id'])) {
            $option = QuestionOption::findOne($_POST['QuestionOption']['id']);
            if($option){
                $ordering = $option->ordering;
                $option->delete();
                $options = QuestionOption::find()->where(["questionId"=>$_POST['QuestionOption']['questionId']])->andWhere(['>', 'ordering', $ordering])->all();
                foreach($options as $o){
                    $o->ordering--;
                    $o->save();
                }
                $study->save();
                $options = QuestionOption::find()->where(["questionId"=>$_POST['QuestionOption']['questionId']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($options)]);
            }
		}else if(isset($_POST['AlterList'])){
			if($_POST['AlterList']['id'] != 'all'){
				$model = AlterList::findOne($_POST['AlterList']['id']);
				if($model){
					$studyId = $model->studyId;
					$ordering = $model->ordering;
					$model->delete();
                    $study->save();
					//AlterList::sortOrder($ordering, $studyId);
				}
			}else{
				$this->deleteAllAlters($id);
			}
            return $this->redirect(Yii::$app->request->referrer);
        }elseif (isset($_POST['AlterPrompt']) && isset($_POST['AlterPrompt']['id'])) {
            $prompt = AlterPrompt::findOne($_POST['AlterPrompt']['id']);
            if($prompt){
                $prompt->delete();
                $study->save();
                $prompts = AlterPrompt::find()->where(["questionId"=>$_POST['AlterPrompt']['questionId']])->orderBy(["afterAltersEntered"=>"ASC"])->asArray()->all();
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($prompts)]);
            }
        }else if(isset($_POST['expressionId'])){
			$expression = Expression::findOne($_POST['expressionId']);
            if ($expression) {
                $expression->delete();
                $study->save();
            }
            return $this->redirect(Yii::$app->request->referrer);
		}
    }


    public function actionAjaxreorder($id)
    {
        if (isset($_POST['QuestionOption']) && isset($_POST['options'])) {
            $options = json_decode($_POST['options'], true);
            $newOptions = [];
            foreach($options as $o){
                $option = QuestionOption::findOne($o['id']);
                $option->ordering = $o['ordering'];
                $option->save();
            }
            $newOptions = QuestionOption::find()->where(['questionId'=>$_POST['QuestionOption']['questionId']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
            return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($newOptions)]);

        }elseif (isset($_POST['questions'])) {
			foreach($_POST['questions'] as $order=>$q){
                $question = Question::findOne($q['id']);
                $question->ordering = $order;
                $question->save();
			}
        }
    }

    public function actionExportalterlist($id)
	{
		$study = Study::findOne($id);
		$alters = AlterList::findAll(array("studyId"=>$id));

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".$study->name."-predefined-alters".".csv");
		header("Content-Type: application/force-download");

		$headers = array();
		$headers[] = 'Study ID';
		$headers[] = "Alter ID";
		$headers[] = "Alter Name";
		$headers[] = "Alter Email";
		$headers[] = "Link With Key";
		echo implode(',', $headers) . "\n";

        $ego_id = Question::findOne(array("studyId"=>$study->id, "subjectType"=>"EGO_ID", "useAlterListField"=>array("name", "email", "id")));
        if ($ego_id) {
            foreach ($alters as $alter) {
                $row = array();
                if ($ego_id->useAlterListField == "name") {
                    $key = md5($alter->name);
                } elseif ($ego_id->useAlterListField == "email") {
                    $key = md5($alter->email);
                } elseif ($ego_id->useAlterListField == "id") {
                    $key = md5($alter->id);
                } else {
                    $key = "";
                }
                $row[] = $study->id;
                $row[] = $alter->id;
                $row[] = $alter->name;
                $row[] = $alter->email;
                $row[] =  Url::base(true) . Url::toRoute("/interview/".$study->id."#/page/0/".$key);
                echo implode(',', $row) . "\n";
            }
        }
	}

    protected function deleteAllAlters($id){
		$models = AlterList::findAll(array('studyId'=>$id));
		foreach($models as $model){
			$model->delete();
		}
        $study = Study::findOne($id);
        $study->save();
	}

}