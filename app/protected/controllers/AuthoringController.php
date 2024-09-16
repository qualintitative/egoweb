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
use yii\data\Pagination;

/**
 * Authoring controller
 * edit and create studies, set interviewer permissions
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
                'class' => AccessControl::class,
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

    /**
     * Import participant list
     * /authoring/importlist/{id}
     */
    public function actionImportlist($id)
    {
        if (!is_uploaded_file($_FILES['userfile']['tmp_name'])) { //checks that file is uploaded
            die("Error importing Participant list");
        }
        $nameGenQs = Question::findAll(["studyId"=>$id, "subjectType"=>"NAME_GENERATOR"]);
        $nameGenQIds = array();
        foreach ($nameGenQs as $nameGenQ) {
            $nameGenQIds[$nameGenQ->title] = $nameGenQ->id;
        }
        $file = fopen($_FILES['userfile']['tmp_name'], "r");
        $results = Interviewer::findAll(["studyId"=>$id]);
        $interviewers = array();
        foreach ($results as $result) {
            $user = User::findOne($result->interviewerId);
            $interviewers[$result->interviewerId] = $user->name;
        }
        while (! feof($file)) {
            $data = fgetcsv($file);
            if (isset($data[0]) && $data[0]) {
                $alterlist = AlterList::findAll(['studyId'=>$id]);
                $model = new AlterList;
                $model->ordering = count($alterlist);
                $model->name = trim($data[0]);
                $model->email = isset($data[1]) ? $data[1] : "";
                $interviewerColumn = false;
                $nameGenColumn = false;
                if (count($data) == 3) {
                    $nameGenColumn = 2;
                } elseif (count($data) == 4) {
                    $interviewerColumn = 3;
                    $nameGenColumn = 2;
                }
                $model->nameGenQIds = '';
                if ($nameGenColumn && stristr($data[$nameGenColumn], ";")) {
                    $Qs = explode(";", $data[$nameGenColumn]);
                    $qIds = array();
                    foreach ($Qs as $title) {
                        if (isset($nameGenQIds[$title])) {
                            $qIds[] = $nameGenQIds[$title];
                        }
                    }
                    $model->nameGenQIds = implode(",", $qIds);
                } elseif (isset($nameGenQIds[$data[$nameGenColumn]])) {
                    $model->nameGenQIds = strval($nameGenQIds[$data[$nameGenColumn]]);
                }
                if ($interviewerColumn && isset($data[$interviewerColumn])) {
                    $model->interviewerId =  array_search($data[$interviewerColumn], $interviewers);
                }
                $model->studyId = $id;
                if (!$model->save()) {
                    echo $model->nameGenQIds;
                    print_r($model->errors);
                    die();
                }
            }
        }
        fclose($file);
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionImportprompts($id)
    {
        if (!is_uploaded_file($_FILES['userfile']['tmp_name'])) { //checks that file is uploaded
            die("Error importing Variable Alter Prompts");
        }
        $file = fopen($_FILES['userfile']['tmp_name'], "r");
        while (! feof($file)) {
            $data = fgetcsv($file);
            if (isset($data[0])) {
                $model = new AlterPrompt;
                $model->studyId = $id;
                $model->questionId = $_POST['questionId'];
                $model->afterAltersEntered = trim($data[0]);
                $model->display = isset($data[1]) ? $data[1] : "";
                $model->save();
            }
        }
        fclose($file);
        $prompts = AlterPrompt::find()->where(['studyId'=>$id,'questionId'=>$_POST['questionId']])->orderBy(['afterAltersEntered'=>'ASC'])->asArray()->all();
        return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($prompts)]);
    }

    /**
     * Create new study
     * /authoring/create
     */
    public function actionCreate()
    {
        $study = new Study;
        if (isset($_POST['Study'])) {
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

    /**
     * Edit study settings
     * /authoring/{id}
     */
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
            $study->active = intval(!isset($_POST['Study']['inactive']));

            if ($study->save()) {
                return $this->response->redirect(Url::toRoute('/authoring/' . $study->id));
            } else {
                print_r($study->errors);
                die();
            }
        }
        $qs = ArrayHelper::map(
            Question::findAll(["studyId"=>$study->id, "subjectType"=>"EGO_ID"]),
            'id',
            'title'
        );
        $egoIdOptions = [];
        $egoIdOptions[] = ["value"=>"", "text"=>""];
        foreach ($qs as $i=>$q) {
            $egoIdOptions[] = ["value"=>$i, "text"=>$q];
        }
        $interviews = Interview::find()->where(['studyId'=>$id])->count();
        $study = Study::find()->where(["id"=>$id])->asArray()->one();
        return $this->render('index', ["study"=>$study, "egoIdOptions"=>$egoIdOptions, "interviews"=>$interviews]);
    }

    /**
     * Add, delete, and edit Ego ID questions
     * /authoring/ego_id/{id}
     */
    public function actionEgo_id($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        $questions = Question::find()->where(["studyId"=>$id, "subjectType"=>"EGO_ID"])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        if (Yii::$app->request->post()) {
            if ($_POST['Question']['id']) {
                $question = Question::findOne($_POST['Question']['id']);
            } else {
                $question = new Question;
                $question->ordering = count($questions);
            }
            if ($question->load(Yii::$app->request->post()) && $question->validate()) {
                if ($question->save()) {
                    return $this->response->redirect(Url::toRoute('/authoring/ego_id/' . $study->id));
                } else {
                    print_r($question->errors);
                    die();
                }
            }
        }
        foreach (Question::EGOID_ANSWERTYPES as $a) {
            $answerTypes[] = ["value"=>$a, "text"=>$a];
        }
        $subjectTypes = false;
        foreach ($questions as &$question) {
            $question['options'] = QuestionOption::find()->where(['questionId'=>$question['id']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        }
        $new_question = new Question;
        $new_question->id = 0;
        $new_question->studyId = $study->id;
        $new_question->subjectType = "EGO_ID";
        $new_question = $new_question->toArray();

        $expressions = Expression::find()->where(["studyId"=>$study->id])->asArray()->all();
        return $this->render('questions', ["study"=>$study, "studyNames"=>[], "questions"=>$questions, "all_questions"=>[], "new_question"=>$new_question, "answerTypes"=>$answerTypes, "subjectTypes"=>$subjectTypes, "expressions"=>$expressions]);
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
        foreach ($questions as $question) {
            if ($question->subjectType == "EGO") {
                $ego_questions[] = $question;
            }
            if ($question->subjectType == "ALTER") {
                $alter_questions[] = $question;
            }
            if ($question->subjectType == "ALTER_PAIR") {
                $alter_pair_questions[] = $question;
            }
            if ($question->subjectType == "NETWORK") {
                $network_questions[] = $question;
            }
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
        foreach ($interviews as $interview) {
            $alters = Alters::find()
          ->where(new \yii\db\Expression("FIND_IN_SET(" . $interview->id .", interviewId)"))
          ->orderBy(['ordering'=>'ASC'])
          ->all();
            foreach ($alters as $alter) {
                $alter->nameGenQIds = $nameGenQId;
                $alter->save();
            }
        }
        $i++;
        foreach ($alter_questions as $question) {
            $question->ordering = $i + $question->ordering;
            $question->save();
        }
        $i = $i + count($alter_questions);
        foreach ($alter_pair_questions as $question) {
            $question->ordering = $i + $question->ordering;
            $question->save();
        }
        $i = $i + count($alter_pair_questions);
        foreach ($network_questions as $question) {
            $question->ordering = $i + $question->ordering;
            $question->save();
        }
        return $this->response->redirect(Url::toRoute('/authoring/questions/' . $id));
    }

    /**
     * Add, delete, and edit questions
     * /authoring/questions/{id}
     */
    public function actionQuestions($id)
    {
        $studyNames = [];
        $allQuestions = [];
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        if ($study->multiSessionEgoId) {
            $multiQs = $study->multiIdQs();
            if(count($multiQs) > 0){
                foreach ($multiQs as $q) {
                    $studyIds[] = $q->studyId;
                    $s = Study::findOne($q->studyId);
                    $studyNames[$q->studyId] = $s->name;
                }
            }else{
                $studyIds = $id;
            }
        } else {
            $studyIds = $id;
        }
        $questions = Question::find()->where(["studyId"=>$id])->andWhere(['!=', 'subjectType', 'EGO_ID'])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        if (Yii::$app->request->isPost) {
            if ($_POST['Question']['id']) {
                $question = Question::findOne($_POST['Question']['id']);
            } else {
                $question = new Question;
                $question->ordering = count($questions);
            }
            if ($question->load(Yii::$app->request->post()) && $question->validate()) {
                if ($question->save()) {
                    $study->save();
                    return $this->response->redirect(Url::toRoute('/authoring/questions/' . $study->id));
                } else {
                    print_r($question->errors);
                    die();
                }
            }
        }
        foreach ($questions as &$question) {
            $question['options'] = QuestionOption::find()->where(['questionId'=>$question['id']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
            if ($question['subjectType'] == "NAME_GENERATOR") {
                $question['alterPrompts'] = AlterPrompt::find()->where(array('questionId'=>$question['id']))->orderBy(["afterAltersEntered"=>"ASC"])->asArray()->all();
            }
        }
        $result = Question::find()->where(["studyId"=>$studyIds])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        foreach ($result as $q) {
            //if ($study->multiSessionEgoId) {
            //    $question['title'] = $studyNames[$question['studyId']] .":".$question['title'];
            //}
            if ($q['answerType'] == "NO_RESPONSE" ||
                $q['subjectType'] == "NAME_GENERATOR" ||
                $q['subjectType'] == "MERGE_ALTER" ||
                $q['subjectType'] == "NETWORK") {
                continue;
            }
            $q['optionsList'] = QuestionOption::find()->where(["questionId"=>$q['id']])->orderBy(["ordering"=>"ASC"])->asArray()->all();

            $allQuestions[] = $q;
        }
        $new_question = new Question;
        $new_question->studyId = $study->id;
        $new_question->subjectType = "EGO";
        $new_question->id = 0;
        $new_question = $new_question->toArray();
        foreach (Question::ANSWERTYPES as $a) {
            $answerTypes[] = ["value"=>$a, "text"=>$a];
        }
        foreach (Question::SUBJECTTYPES as $s) {
            $subjectTypes[] = ["value"=>$s, "text"=>$s];
        }
        $expressions = Expression::find()->where(["studyId"=>$study->id])->asArray()->all();

        return $this->render('questions', ["study"=>$study, "studyNames"=>$studyNames, "questions"=>$questions, "all_questions"=>$allQuestions, "new_question"=>$new_question, "answerTypes"=>$answerTypes, "subjectTypes"=>$subjectTypes, "expressions"=>$expressions]);
    }

    /**
     * Create participant list and assign interviewers
     * /authoring/participants/{id}
     */
    public function actionParticipants($id)
    {
        $study = Study::findOne($id)->toArray();
        $this->view->title = $study['name'];
        $ego_id = Question::findOne(array("studyId"=>$study['id'], "subjectType"=>"EGO_ID", "useAlterListField"=>array("name", "email", "id")));
        $result = Interviewer::find()->where(["studyId"=>$id])->all();
        $interviewers = [];
        $alterList = [];
        $interviewerList = [];
        $users = [];
        $userIds = [];
        foreach ($result as $interviewer) {
            $user = User::findOne($interviewer->interviewerId);
            if ($user) {
                $userIds[] = $user->id;
                $interviewerList[$user->id] = $user->name;
                $interviewers[] = ["id"=>$user->id,"name"=>$user->name, "role"=>User::roles()[$user->permissions]];
            }
        }

        $result = AlterList::find()->where(["studyId"=>$id]);
        $pagination = new Pagination(['totalCount' => $result->count()]);
        $items = $result->offset($pagination->offset)
        ->limit($pagination->limit)
        ->all();
    
        foreach ($items as $item) {
            $interviewer = "";
            $url = "No Ego Id question Use List field selected";
            if (isset($interviewerList[$item->interviewerId])) {
                $interviewer = $interviewerList[$item->interviewerId];
            }
            if ($ego_id) {
                if ($ego_id) {
                    if ($ego_id->useAlterListField == "name") {
                        $key = md5($item->name);
                    } elseif ($ego_id->useAlterListField == "email") {
                        $key = md5($item->email);
                    } elseif ($ego_id->useAlterListField == "id") {
                        $key = md5($item->id);
                    }
                }
                $url = Url::base(true) . Url::toRoute("/interview/".$study['id']."#/page/0/".$key);
            }
            if($item->nameGenQIds != null && stristr(",", $item->nameGenQIds))
                $nameGenQIds = explode(",", $item->nameGenQIds);
            else
                $nameGenQIds = "";
            $alterList[] = ["id"=>$item->id, "name"=>$item->name, "email"=>$item->email, "nameGenQIds"=>$item->nameGenQIds, "nameGenQIdsArray"=>$nameGenQIds, "interviewerId"=>$item->interviewerId, "url"=>$url];
        }
        $result = User::find()->where(['<=', 'permissions', 5])->andWhere(['not', ['id'=>$userIds]])->all();
        foreach ($result as $item) {
            $users[] = $item->toArray();
        }
        $questions = Question::find()->where(["subjectType"=>"NAME_GENERATOR", "studyId"=>$id])->asArray()->all();
        return $this->render('participants', ["study"=>$study, "interviewers"=>$interviewers, "alterList"=>$alterList, "users"=>$users, "questions"=>$questions, "pagination"=>$pagination]);
    }

    public function actionGetlink($id)
    {
        $study = Study::findOne($id);
        $ego_id = Question::findOne(array("studyId"=>$study->id, "subjectType"=>"EGO_ID", "useAlterListField"=>array("name", "email", "id")));
        $alter = AlterList::find()
        ->where(["id"=>$_POST['alterListId']])
        ->one();
        $key = "";
        $study = Study::findOne($id);
        if ($ego_id) {
            if ($ego_id) {
                if ($ego_id->useAlterListField == "name") {
                    $key = md5($alter->name);
                } elseif ($ego_id->useAlterListField == "email") {
                    $key = md5($alter->email);
                } elseif ($ego_id->useAlterListField == "id") {
                    $key = md5($alter->id);
                }
            }
        }
        return Url::base(true) . Url::toRoute("/interview/".$study->id."#/page/0/".$key);
    }

    /**
     * Add, delete, and edit expressions
     * /authoring/expressions/{id}
     */
    public function actionExpressions($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        $studyNames = [];
        if ($study->multiSessionEgoId) {
            $multiQs = $study->multiIdQs();
            foreach ($multiQs as $q) {
                $studyIds[] = $q->studyId;
                $s = Study::findOne($q->studyId);
                $studyNames[$q->studyId] = $s->name;
            }
        } else {
            $studyIds = $id;
        }
        if (isset($_POST['Expression'])) {
            if ($_POST['Expression']['id']) {
                $expression = Expression::findOne($_POST['Expression']['id']);
            } else {
                $expression = new Expression;
            }
            $expression->attributes = $_POST['Expression'];
            if ($expression->save()) {
                if ($_POST['Expression']['id']) {
                    $expId = $expression->id;
                } else {
                    $expId = Yii::$app->db->getLastInsertID();
                }
                $study->save();
                Yii::$app->session->setFlash('success', 'Expression saved.');
                return $this->response->redirect(Url::toRoute('/authoring/expressions/' . $study->id . "#/" . $expId));
            } else {
                print_r($expression->errors);
                die();
            }
        }
        $new_expression = new Expression;
        $new_expression->name = "";
        $new_expression->id = 0;
        $new_expression->studyId = $id;
        $new_expression->operator = "Some";

        $new_expression->questionId = null;
        $new_expression->resultForUnanswered = 0;
        $expressionList = [];
        $expressionList[0] = $new_expression->toArray();
        $expressions[0] = $new_expression->toArray();
        $countQuestions = [];
        $countExpressions = [];
        
        $result = Question::find()->where(["studyId"=>$studyIds])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        $questions = [];
        $nameGenQuestions = [];
        $questionIds = [];
        foreach ($result as $question) {
            if ($study->multiSessionEgoId) {
                $question['title'] = $studyNames[$question['studyId']] .":".$question['title'];
            }
            if ($question['subjectType'] == "NAME_GENERATOR") {
                $nameGenQuestions[] = $question;
            }
            if ($question['answerType'] == "NUMERICAL" || $question['answerType'] == "RANDOM_NUMBER" || $question['answerType'] == "STORED_VALUE") {
                $countQuestions[] = $question;
            }
            if ($question['answerType'] == "NO_RESPONSE" ||
                $question['subjectType'] == "NAME_GENERATOR" ||
                $question['subjectType'] == "MERGE_ALTER" ||
                $question['subjectType'] == "NETWORK") {
                continue;
            }
            $questions[$question['id']] = $question;
            $questionIds[] = $question['id'];
            $questions[$question['id']]['optionsList'] = QuestionOption::find()->where(["questionId"=>$question['id']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
        }
        $result = Expression::find()->where(["studyId"=>$id])->orderBy(["name"=>"ASC"])->all();
        foreach ($result as $expression) {
            if(is_numeric($expression->questionId) && !in_array($expression->questionId, $questionIds))
                continue;
            $expressionList[] = $expression->toArray();
            $expressions[$expression->id] = $expression->toArray();
            if ($expression->type == "Counting") {
                $countExpressions[] = $expression->toArray();
            }
        }
        return $this->render('expressions', ["study"=>$study->toArray(), "expressions"=>$expressions, "expressionList"=>$expressionList, "questions"=>$questions, "nameGenQuestions"=>$nameGenQuestions, "countExpressions"=>$countExpressions, "countQuestions"=>$countQuestions]);
    }


    public function actionAddinterviewer($id)
    {
        $interviewer = new Interviewer;
        if ($interviewer->load(Yii::$app->request->post())) {
            if ($interviewer->save()) {
                return $this->response->redirect(Url::toRoute('/authoring/participants/' . $id));
            } else {
                print_r($interviewer->errors);
            }
        }
    }

    public function actionDeleteInterviewer($id)
    {
        if (isset($_POST['Interviewer']['id'])) {
            $interviewer = Interviewer::findOne(array("studyId"=>$id, 'interviewerId'=>$_POST['Interviewer']['id']));
            if ($interviewer) {
                $interviewer->delete();
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionDelete($id)
    {
        $interviews = Interview::findAll(array("studyId"=>$id));
        if (count($interviews) > 0) {
            echo "Please delete all interviews before deleting this study";
        } else {
            $study = Study::findOne($id);
            $study->delete();
            return $this->response->redirect(Url::toRoute('/admin'));
        }
    }

    public function actionDuplicatequestion($id)
    {
        $study = Study::findOne($id);
        if (isset($_POST['questionId'])) {
            $copy = false;
            $questions = Question::find()->where(['studyId'=>$id])->orderBy(["ordering"=>"ASC"])->all();
            foreach ($questions as $question) {
                if ($copy) {
                    $question->ordering++;
                    $question->save();
                }
                if ($question->id == $_POST['questionId']) {
                    $copy = $question;
                }
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
        if (isset($_POST['Question'])) {
            if (is_numeric($_POST['Question']['id'])) {
                $question = Question::findOne($_POST['Question']['id']);
            } else {
                $question = new Question;
            }
            $question->attributes = $_POST['Question'];
            if ($question->save()) {
                $study->save();
            } else {
                print_r($question->errors);
                die();
            }
        } elseif (isset($_POST['Study'])) {
            $study = Study::findOne($_POST['Study']['id']);
            $study->attributes = $_POST['Study'];
            if ($study->save()) {
            } else {
                print_r($study->errors);
                die();
            }
        } elseif (isset($_POST['QuestionOption'])) {
            $options = json_decode($_POST['options']);
            if (is_numeric($_POST['QuestionOption']['id']) && $_POST['QuestionOption']['id'] != 0) {
                $option = QuestionOption::findOne($_POST['QuestionOption']['id']);
            } else {
                if ($_POST['QuestionOption']['id'] == "replaceOther") {
                    $oldOptions = QuestionOption::findAll(array('questionId'=>$_POST['QuestionOption']['questionId']));
                    foreach ($oldOptions as $option) {
                        $option->delete();
                    }
                    $models = QuestionOption::findAll(array('questionId'=>$_POST['QuestionOption']['value']));
                    foreach ($models as $model) {
                        $newOption = new QuestionOption;
                        $newOption->attributes = $model->attributes;
                        $newOption->id = '';
                        $newOption->questionId = $_POST['QuestionOption']['questionId'];
                        $newOption->save();
                    }
                    $study->save();
                    $options = QuestionOption::find()->where(array('questionId'=>$_POST['QuestionOption']['questionId']))->asArray()->all();
                    return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($options)]);
                } else {
                    $option = new QuestionOption;
                    if (!is_array($options)) {
                        $option->ordering = 0;
                    } else {
                        $option->ordering = count($options);
                    }
                    $option->studyId = $id;
                }
            }
            $option->attributes = $_POST['QuestionOption'];
            if ($option->save()) {
                $option = $option->toArray();
                $options[] = $option;
                $study->save();
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($options)]);
            } else {
                print_r($option->errors);
                die();
            }
        } elseif (isset($_POST['AlterList'])) {
            if (isset($_POST['AlterList']['id'])) {
                $alterList = AlterList::findOne($_POST['AlterList']['id']);
            } else {
                $alterList = new AlterList;
                $aList = AlterList::findAll(["studyId"=>$id]);
                $alterList->ordering = count($aList);
            }
            $alterList->attributes=$_POST['AlterList'];
            $alterList->name = trim($alterList->name);
            $alterList->studyId = $id;
            $study->save();
            if ($alterList->save()) {
                if (!isset($_POST['AlterList']['id'])) {
                    return $this->redirect(Yii::$app->request->referrer);
                }
            } else {
                print_r($alterList->errors);
            }
        } elseif (isset($_POST['AlterPrompt'])) {
            $prompts = json_decode($_POST['prompts']);
            if ($_POST['AlterPrompt']['id']) {
                $prompt = AlterPrompt::findOne($_POST['AlterPrompt']['id']);
            } else {
                $prompt = new AlterPrompt;
                $prompt->studyId = $id;
            }
            $prompt->attributes = $_POST['AlterPrompt'];
            if ($prompt->save()) {
                $study->save();
                $prompt = $prompt->toArray();
                $prompts[] = $prompt;
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($prompts)]);
            } else {
                print_r($prompt->errors);
                die();
            }
        }
    }

    public function actionAjaxdelete($id)
    {
        $study = Study::findOne($id);
        if (isset($_POST['Question'])) {
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
                foreach ($questions as $q) {
                    $q->ordering--;
                    $q->save();
                }
                $question->delete();
                $study->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
        } elseif (isset($_POST['QuestionOption']) && isset($_POST['QuestionOption']['id'])) {
            $option = QuestionOption::findOne($_POST['QuestionOption']['id']);
            if ($option) {
                $ordering = $option->ordering;
                $option->delete();
                $options = QuestionOption::find()->where(["questionId"=>$_POST['QuestionOption']['questionId']])->andWhere(['>', 'ordering', $ordering])->all();
                foreach ($options as $o) {
                    $o->ordering--;
                    $o->save();
                }
                $study->save();
                $options = QuestionOption::find()->where(["questionId"=>$_POST['QuestionOption']['questionId']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($options)]);
            }
        } elseif (isset($_POST['AlterList'])) {
            if ($_POST['AlterList']['id'] != 'all') {
                $model = AlterList::findOne($_POST['AlterList']['id']);
                if ($model) {
                    $studyId = $model->studyId;
                    $ordering = $model->ordering;
                    $model->delete();
                    $study->save();
                    //AlterList::sortOrder($ordering, $studyId);
                }
            } else {
                $this->deleteAll($id);
            }
            return $this->redirect(Yii::$app->request->referrer);
        } elseif (isset($_POST['AlterPrompt']) && isset($_POST['AlterPrompt']['id'])) {
            $prompt = AlterPrompt::findOne($_POST['AlterPrompt']['id']);
            if ($prompt) {
                $prompt->delete();
                $study->save();
                $prompts = AlterPrompt::find()->where(["questionId"=>$_POST['AlterPrompt']['questionId']])->orderBy(["afterAltersEntered"=>"ASC"])->asArray()->all();
                return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($prompts)]);
            }
        } elseif (isset($_POST['expressionId'])) {
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
            foreach ($options as $o) {
                $option = QuestionOption::findOne($o['id']);
                $option->ordering = $o['ordering'];
                $option->save();
            }
            $newOptions = QuestionOption::find()->where(['questionId'=>$_POST['QuestionOption']['questionId']])->orderBy(["ordering"=>"ASC"])->asArray()->all();
            return $this->renderAjax("/layouts/ajax", ["json"=>json_encode($newOptions)]);
        } elseif (isset($_POST['questions'])) {
            foreach ($_POST['questions'] as $order=>$q) {
                $question = Question::findOne($q['id']);
                $question->ordering = $order;
                if (!$question->save()) {
                    print_r($question->errors);
                }
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


        $ego_id = Question::findOne(array("studyId"=>$study->id, "subjectType"=>"EGO_ID", "useAlterListField"=>array("name", "email", "id")));

        $headers = array();
        $headers[] = 'Study Name';
        $headers[] = "Name";
        $headers[] = "Email";
        if ($ego_id) {
            $headers[] = "Link With Key";
        }
        echo implode(',', $headers) . "\n";

        //if ($ego_id) {
        foreach ($alters as $alter) {
            $row = array();
            $key = "";
            $row[] = $study->name;
            $row[] = $alter->name;
            $row[] = $alter->email;
            if ($ego_id) {
                if ($ego_id->useAlterListField == "name") {
                    $key = md5($alter->name);
                } elseif ($ego_id->useAlterListField == "email") {
                    $key = md5($alter->email);
                } elseif ($ego_id->useAlterListField == "id") {
                    $key = md5($alter->id);
                }
                $row[] =  Url::base(true) . Url::toRoute("/interview/".$study->id."#/page/0/".$key);
            }
            echo implode(',', $row) . "\n";
        }
    }

    protected function deleteAll($id)
    {
        $models = AlterList::findAll(array('studyId'=>$id));
        foreach ($models as $model) {
            $model->delete();
        }
        $study = Study::findOne($id);
        $study->save();
        return $this->redirect(Yii::$app->request->referrer);
    }
}
