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
use app\models\AlterList;
use app\models\MatchedAlters;
use app\models\Interview;
use app\models\Answer;
use app\models\Server;
use app\models\Alters;
use app\models\Graph;
use app\models\Note;
use app\models\LoginForm;

/**
 * Site controller
 */
class MobileController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
        /**
     * {@inheritdoc}
     */
    public function behaviors()
    {        
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    // restrict access to
                    'Origin' => ['*'],
                    // Allow only POST and PUT methods
                    // 'Access-Control-Request-Method' => ['POST', 'PUT'],
                    // Allow only headers 'X-Wsse'

                    // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                    //'Access-Control-Allow-Credentials' => true,
                    // Allow OPTIONS caching
                    'Access-Control-Max-Age' => 86400,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['authenticate', 'sync-data'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                   // 'syncData' => ['post'],
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

    public $newAlterIds = array();
    public $newInterviewIds = array();

    public function actionIndex()
    {
        $filename = "EgoWebMobile.ipa";
        $date = date("F d Y", filemtime($filename));
        $filename = "EgoWebMobile.apk";
        $android_date = date("F d Y", filemtime($filename));
        return $this->render('index', array(
            'date'=>$date,
            'android_date'=>$android_date
        ));
    }

    public function actionCheck()
    {
        $json = "success";
        return $this->renderAjax('/layouts/ajax',["json"=>$json]);
    }

    public function actionAjaxstudies()
    {
        if (isset($_POST['userId'])) {
            $user = User::findOne($_POST['userId']);
            $studies = $user->studies;
            $json = [];
            foreach ($studies as $study) {
                $json[$study->id] = $study->name;
            }
            return $this->renderAjax('/layouts/ajax',["json"=>json_encode($json)]);
        }else{
            throw new \yii\web\HttpException(404,'Oops. Not logged in.');
        }
    }

    public function actionAjaxdata($id)
    {
        $study = Tools::mToA(Study::findOne($id));
        $results = Question::findAll(array("studyId"=>$id));
        $questions = array();
        foreach ($results as $result) {
            $questions[] = Tools::mToA($result);
        }
        $results = QuestionOption::findAll(array("studyId"=>$id));
        $options = array();
        foreach ($results as $result) {
            $options[] = Tools::mToA($result);
        }
        $results = Expression::findAll(array("studyId"=>$id));
        $expressions = array();
        foreach ($results as $result) {
            $expressions[] = Tools::mToA($result);
        }
        $results = AlterList::findAll(array("studyId"=>$id));
        $alterList = array();
        foreach ($results as $result) {
            $alterList[] = Tools::mToA($result);
        }
        $results = AlterPrompt::findAll(array("studyId"=>$id));
        $alterPrompts = array();
        foreach ($results as $result) {
            $alterPrompts[] = Tools::mToA($result);
        }

        $interviewIds = array();
        $audioFiles = array();

        $columns = array();
        $columns['study'] = Yii::$app->db->getTableSchema("study")->getColumnNames();
        $columns['question'] = Yii::$app->db->getTableSchema("question")->getColumnNames();
        $columns['questionOption'] = Yii::$app->db->getTableSchema("questionOption")->getColumnNames();
        $columns['expression'] = Yii::$app->db->getTableSchema("expression")->getColumnNames();
        $columns['answer'] = Yii::$app->db->getTableSchema("answer")->getColumnNames();
        $columns['alters'] = Yii::$app->db->getTableSchema("alters")->getColumnNames();
        $columns['interview'] = Yii::$app->db->getTableSchema("interview")->getColumnNames();
        $columns['alterList'] = Yii::$app->db->getTableSchema("alterList")->getColumnNames();
        $columns['alterPrompt'] = Yii::$app->db->getTableSchema("alterPrompt")->getColumnNames();
        $columns['alterList'] = Yii::$app->db->getTableSchema("alterList")->getColumnNames();
        $columns['graphs'] = Yii::$app->db->getTableSchema("graphs")->getColumnNames();
        $columns['notes'] = Yii::$app->db->getTableSchema("notes")->getColumnNames();

        foreach ($columns as &$column) {
            foreach ($column as &$label) {
                $label = strtoupper($label);
            }
        }

        $data = array(
            'study'=>$study,
            'questions'=>$questions,
            'options'=>$options,
            'expressions'=>$expressions,
            'alterList'=>$alterList,
            'alterPrompts'=>$alterPrompts,
            'audioFiles'=>$audioFiles,
            'columns'=>$columns,
        );

        return $this->renderAjax('/layouts/ajax',["json"=>json_encode($data)]);
    }

    public function actionAuthenticate()
    {
        if (isset($_POST['LoginForm'])) {
            $model = new LoginForm;
            $model->attributes = $_POST['LoginForm'];
            if ($model->validate() && $model->login()) {
                echo Yii::$app->user->identity->id;
            } else {
                echo "failed";
            }
        } else {
            throw new \yii\web\HttpException(404,'Oops. Not logged in.');
        }
    }

    public function actionGetstudies()
    {
        if (isset($_POST['LoginForm'])) {
            $model = new LoginForm;
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                $studies = Yii::$app->user->identity->studies;
                foreach ($studies as $study) {
                    $json[] = array("id"=>$study->id, "name"=>$study->name);
                }
                return $this->renderAjax('/layouts/ajax',["json"=>json_encode($json)]);
            } else {
                echo "failed";
            }
        }else{
            throw new \yii\web\HttpException(404,'Oops. Not logged in.');
        }
    }

    public function actionUploadData()
    {
        $errorMsg = "";
        if (count($_POST)) {
            $errors = 0;
            $data = json_decode($_POST['data'], true);
            if (!$data['study']['ID']) {
                throw new \yii\web\HttpException(500,'Internal Server Error');
                die();
            }
            $oldStudy = Study::findOne(array("name"=>$data['study']['NAME']));
            if ($oldStudy && $oldStudy->modified == $data['study']['MODIFIED']) {
                $this->saveAnswers($data);
            } else {
                $study = new Study;
                foreach ($study->attributes as $key=>$value) {
                    $study->$key = $data['study'][strtoupper($key)];
                }
                if ($oldStudy) {
                    $study->name = $data['study']['NAME'] . " 2";
                }
                $questions = array();
                foreach ($data['questions'] as $q) {
                    $question = new Question;
                    foreach ($question->attributes as $key=>$value) {
                        $question->$key = $q[strtoupper($key)];
                    }
                    array_push($questions, $question);
                }
                $options = array();
                foreach ($data['questionOptions'] as $o) {
                    $option = new QuestionOption;
                    foreach ($option->attributes as $key=>$value) {
                        $option->$key = $o[strtoupper($key)];
                    }
                    array_push($options, $option);
                }
                $expressions = array();
                foreach ($data['expressions'] as $e) {
                    $expression = new Expression;
                    foreach ($expression->attributes as $key=>$value) {
                        $expression->$key = $e[strtoupper($key)];
                    }
                    array_push($expressions, $expression);
                }
                echo "questions ". count($questions);
                $newData = Study::replicate($study, $questions, $options, $expressions, array());
                if ($newData) {
                    $this->saveAnswers($data, $newData);
                    $json = "Study " . $oldStudy->name . " was modified. (" . $oldStudy->modified .  ":" . $data['study']['MODIFIED'] . ")  Generated new study: " . $study->name . ". ";
                } else {
                    $json = "Error while attempting to create a new study.";
                }
            }
            if ($errors == 0) {
                $json = "Upload completed.  No Errors Found";
            } else {
                $json = "Errors encountered!";
            }
        }
        return $this->renderAjax('/layouts/ajax',["json"=>$json]);
    }

    public function actionSyncData()
    {
        $json = "fail";
        if (count($_POST)) {
            $data = json_decode($_POST['data'], true);
            if (!isset($_POST['LoginForm'])) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                die();
            }
            $egoIds = array();
            if (isset($data['study']['NAME'])) {
                $oldStudy = Study::findOne(array("name"=>$data['study']['NAME']));
                if ($oldStudy) {
                    $interviews = Interview::findAll(array("studyId"=>$oldStudy->id));
                    foreach ($interviews as $interview) {
                        $egoId = $interview->egoid;
                        if ($egoId == $data['interviews'][0]["EGOID"]) {
                            echo $egoId . ": interview already exists";
                            die();
                        }
                    }
                }
            } else {
                throw new \yii\web\HttpException(500,'Internal Server Error');
                die();
            }
            $model = new LoginForm;
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
            } else {
                print_r($model->errors);
                //throw new \yii\web\HttpException(500,'Internal Server Error');
                die();
            }
            $errors = 0;
            if (!$data['study']['ID']) {
                throw new \yii\web\HttpException(500,'Internal Server Error');
                die();
            }
            if ($oldStudy) {
                $questions = Question::findAll(array("studyId"=>$oldStudy->id));
                $newQuestionIds = array();
                $newQuestionTitles = array();
                foreach ($questions as $question) {
                    if ($question->subjectType == "NAME_GENERATOR") {
                        $nameGenQId = $question->id;
                    }
                    $newQuestionIds[$question->title] = $question->id;
                    $newQuestionTitles[$question->id] = $question->title;
                }
                $options = QuestionOption::findAll(array("studyId"=>$oldStudy->id));
                $newOptionIds = array();
                foreach ($options as $option) {
                    if(isset($newQuestionTitles[$option->questionId]))
                        $newOptionIds[$newQuestionTitles[$option->questionId]."_".$option->name] = $option->id;
                }
                echo "Merging with existing study $oldStudy->name. ";
                $data['interviews'][0]['STUDYID'] = $oldStudy->id;
                $newData = array(
					"studyId"=>$oldStudy->id,
					"newQuestionIds"=>$newQuestionIds,
					"newOptionIds"=>$newOptionIds,
					"nameGenQId"=>$nameGenQId,
				);
                $this->saveAnswersMerge($data, $newData);
            } else {
                $study = new Study;
                foreach ($study->attributes as $key=>$value) {
                    $study->$key = $data['study'][strtoupper($key)];
                }
                if ($oldStudy) {
                    $study->name = $data['study']['NAME'] . " 2";
                }
                $questions = array();
                $add = 0;
                $nameGenExists = false;
                foreach ($data['questions'] as $q) {
                    if($q['SUBJECTTYPE'] == "NAME_GENERATOR")
                        $nameGenExists = true;
                }
                foreach ($data['questions'] as $q) {
                    if($nameGenExists == false && $q['SUBJECTTYPE'] == "ALTER"){
                        $question = new Question;
                        $add = 1;
                        $question->attributes = array(
                            'subjectType' => "NAME_GENERATOR",
                            'prompt' => $study->alterPrompt,
                            'studyId' => $id,
                            'title' => "ALTER_PROMPT",
                            'answerType' => "NAME_GENERATOR",
                            'ordering' => $ordering + $add,
                        );
                        array_push($questions, $question);
                    }
                    $question = new Question;
                    foreach ($question->attributes as $key=>$value) {
                        if (isset($q[strtoupper($key)])) {
                            $question->$key = $q[strtoupper($key)];
                        }
                    }
                    $question->ordering = intval($question->ordering) + $add;
                    $ordering = $question->ordering;
                    array_push($questions, $question);
                }
                $options = array();
                foreach ($data['questionOptions'] as $o) {
                    $option = new QuestionOption;
                    foreach ($option->attributes as $key=>$value) {
                        if(isset($o[strtoupper($key)]))
                            $option->$key = $o[strtoupper($key)];
                    }
                    array_push($options, $option);
                }
                $expressions = array();
                foreach ($data['expressions'] as $e) {
                    $expression = new Expression;
                    foreach ($expression->attributes as $key=>$value) {
                        $expression->$key = $e[strtoupper($key)];
                    }
                    array_push($expressions, $expression);
                }
                $alterPrompts = array();
                if (isset($data['alterPrompts'])) {
                    foreach ($data['alterPrompts'] as $a) {
                        $alterPrompt = new AlterPrompt;
                        foreach ($alterPrompt->attributes as $key=>$value) {
                            $alterPrompt->$key = $a[strtoupper($key)];
                        }
                        array_push($alterPrompts, $alterPrompt);
                    }
                }
                $newData = Study::replicate($study, $questions, $options, $expressions, $alterPrompts);
                if ($newData) {
                    $this->saveAnswers($data, $newData);
                    $json =  "Generated new study: " . $study->name . ". ";
                } else {
                    $json =  "Error while attempting to create a new study.";
                    return $this->renderAjax('/layouts/ajax',["json"=>$json]);
                }
            }
            if ($errors == 0) {
                $json =  "Upload completed.  No Errors Found";
            } else {
                $json =  "Errors encountered!";
                return $this->renderAjax('/layouts/ajax',["json"=>$json]);
            }
        }
        return $this->renderAjax('/layouts/ajax',["json"=>$json]);
    }

    private function saveAnswers($data, $newData = null)
    {
        if (count($data['interviews']) == 0) {
            return false;
        }
        foreach ($data['interviews'] as $interview) {
            $newInterview = new Interview;
            if ($newData) {
                $newInterview->studyId = $newData['studyId'];
            } else {
                $newInterview->studyId = $interview['STUDYID'];
            }
            $newInterview->completed = $interview['COMPLETED'];
            $newInterview->start_date = $interview['START_DATE'];
            $newInterview->complete_date = $interview['COMPLETE_DATE'];
            if ($newInterview->save()) {
                $newInterviewIds[$interview['ID']] = $newInterview->id;
            } else {
                $errors++;
                print_r($newInterview->getErrors());
                die();
            }
        }

        if (isset($data['alters'])) {
            foreach ($data['alters'] as $alter) {
                $newAlter = new Alters;
                $newAlter->name = html_entity_decode($alter['NAME'], ENT_QUOTES);
                if (stristr($alter['INTERVIEWID'], ",")) {
                    $interviewIds = explode(",", $alter['INTERVIEWID']);
                    foreach ($interviewIds as &$i) {
                        $i = $newInterviewIds[$i];
                    }
                    $interviewIds = implode(",", $interviewIds);
                    if ($interviewIds != $alter['INTERVIEWID']) {
                        $newAlter->interviewId = $interviewIds;
                    } else {
                        continue;
                    }
                } else {
                    if (isset($newInterviewIds[$alter['INTERVIEWID']])) {
                        $newAlter->interviewId = strval($newInterviewIds[$alter['INTERVIEWID']]);
                    } else {
                        continue;
                    }
				}
                if (isset($alter['NAMEGENQIDS'])) {
                    if (stristr($alter['NAMEGENQIDS'], ",")) {
                        $nameGenQIds = explode(",", $alter['NAMEGENQIDS']);
                    } else {
                        $nameGenQIds = array($alter['NAMEGENQIDS']);
                    }
                    foreach ($nameGenQIds as &$nQid) {
                        if (isset($newData['newQuestionIds'][$nQid])) {
                            $nQid = $newData['newQuestionIds'][$nQid];
                        }
                    }
                    $newAlter->nameGenQIds = implode(",", $nameGenQIds);
                }else{
                    if(isset($newData['nameGenQIds'][0]))
                        $newAlter->nameGenQIds = $newData['nameGenQIds'][0];
                }
                if(!is_numeric($alter['ORDERING'])){
                    $nGorder = json_decode($alter['ORDERING'], true);
                    $newOrder = array();
                    foreach ($nGorder as $nQid=>$norder) {
                        if (isset($newData['newQuestionIds'][$nQid])) {
                            $newOrder[$newData['newQuestionIds'][$nQid]] = $norder;
                        } else {
                            $newOrder[$nQid] = $norder;
                        }
                    }
                    $newAlter->ordering = json_encode($newOrder);
                }else{
                    $newAlter->ordering = strval($alter['ORDERING']);
                }
                if (!$newAlter->save()) {
                    print_r($newAlter->getErrors());
                    die();
                } else {
                    $newAlterIds[$alter['ID']] = $newAlter->id;
                }
            }
        }
        foreach ($data['answers'] as $answer) {
            $newAnswer = new Answer;
            if ($newData) {
                if (!isset($newData['newQuestionIds'][$answer['QUESTIONID']])) {
                    continue;
                }
                $newAnswer->questionId = $newData['newQuestionIds'][$answer['QUESTIONID']];
                $newAnswer->studyId = $newData['studyId'];
                if ($answer['ANSWERTYPE'] == "MULTIPLE_SELECTION") {
                    $values = explode(',', $answer['VALUE']);
                    foreach ($values as &$value) {
                        if (isset($newData['newOptionIds'][$value])) {
                            $value = $newData['newOptionIds'][$value];
                        }
                    }
                    $answer['VALUE'] = implode(',', $values);
                }
                $newAnswer->value = html_entity_decode($answer['VALUE'], ENT_QUOTES);
                if ($answer['OTHERSPECIFYTEXT']) {
                    foreach (preg_split('/;;/', $answer['OTHERSPECIFYTEXT']) as $other) {
                        if ($other && strstr($other, ':')) {
                            list($key, $val) = preg_split('/:/', $other);
                            $responses[] = $newData['newOptionIds'][$key] . ":" .$val;
                        }
                    }
                    $answer['OTHERSPECIFYTEXT'] = implode(";;", $responses);
                }
            } else {
                if (!isset($answer['QUESTIONID'])) {
                    continue;
                }
                $newAnswer->questionId = $answer['QUESTIONID'];
                $newAnswer->studyId = $newInterview->studyId;
                $newAnswer->value = html_entity_decode($answer['VALUE'], ENT_QUOTES);
            }
            $newAnswer->questionType = $answer['QUESTIONTYPE'];
            $newAnswer->answerType = $answer['ANSWERTYPE'];
            $newAnswer->otherSpecifyText = $answer['OTHERSPECIFYTEXT'];
            $newAnswer->skipReason = $answer['SKIPREASON'];
            $newAnswer->interviewId = $newInterviewIds[$answer['INTERVIEWID']];
            if (is_numeric($answer['ALTERID1']) && isset($newAlterIds[$answer['ALTERID1']])) {
                $newAnswer->alterId1 = $newAlterIds[$answer['ALTERID1']];
            }
            if (is_numeric($answer['ALTERID2']) && isset($newAlterIds[$answer['ALTERID2']])) {
                $newAnswer->alterId2 = $newAlterIds[$answer['ALTERID2']];
            }
            if (!$newAnswer->save()) {
                print_r($newAnswer->getErrors());
                die();
            }
        }
        foreach ($data['notes'] as $note) {
            $newNote = new Note;
            $newNote->alterId = $newAlterIds[(int)$note["ALTERID"]];
            $newNote->expressionId = $note["EXPRESSIONID"];
            $newNote->interviewId = $newInterviewIds[(int)$note["INTERVIEWID"]];
            $newNote->notes = html_entity_decode($note["NOTES"], ENT_QUOTES);
            $newNote->save();
        }
        foreach ($data['graphs'] as $graph) {
            $newGraph = new Graph;
            $newGraph->expressionId = $graph["EXPRESSIONID"];
            $newGraph->interviewId = $newInterviewIds[(int)$graph["INTERVIEWID"]];
            $newGraph->params = $graph["PARAMS"];
            $nodes = json_decode($graph["NODES"]);
            $newNodes = array();
            foreach ($nodes as $index=>$node) {
                $newNodeId = $newAlterIds[(int)$index];
                $newNodes[$newNodeId] = $node;
                $newNodes[$newNodeId]->id = $newAlterIds[$index];
            }
            $newGraph->nodes = json_encode($newNodes, JSON_FORCE_OBJECT);
            if ($newGraph->save()) {
            } else {
                $errors++;
                print_r($newGraph->getErrors());
            }
        }
    }

    private function saveAnswersMerge($data, $newData)
    {
        if (count($data['interviews']) == 0) {
            return false;
        }
        foreach ($data['interviews'] as $interview) {
            $newInterview = new Interview;
            $newInterview->studyId = $newData['studyId'];
            $newInterview->completed = $interview['COMPLETED'];
            $newInterview->start_date = $interview['START_DATE'];
            $newInterview->complete_date = $interview['COMPLETE_DATE'];
            if ($newInterview->save()) {
                $newInterviewIds[$interview['ID']] = $newInterview->id;
            } else {
                $errors++;
                print_r($newInterview->getErrors());
                die();
            }
        }
        $questionTitles = array();
        foreach ($data['questions'] as $q) {
            $questionTitles[$q['ID']] = $q['TITLE'];
        }
        if (isset($data['alters'])) {
            foreach ($data['alters'] as $alter) {
                $newAlter = new Alters;
				$newAlter->name = html_entity_decode($alter['NAME'], ENT_QUOTES);
                if (stristr($alter['INTERVIEWID'], ",")) {
                    $interviewIds = explode(",", $alter['INTERVIEWID']);
                    foreach ($interviewIds as &$i) {
                        $i = $newInterviewIds[$i];
                    }
                    $interviewIds = implode(",", $interviewIds);
                    if ($interviewIds != $alter['INTERVIEWID']) {
                        $newAlter->interviewId = $interviewIds;
                    } else {
                        continue;
                    }
                } else {
                    if (isset($newInterviewIds[$alter['INTERVIEWID']])) {
                        $newAlter->interviewId = $newInterviewIds[$alter['INTERVIEWID']];
                    } else {
                        continue;
                    }
                }
                if (!isset($alter['NAMEGENQIDS'])) {
                    $newAlter->nameGenQIds = $newData["nameGenQId"];
                } else {
                    if (stristr($alter['NAMEGENQIDS'], ",")) {
                        $qIds = explode(",", $alter['NAMEGENQIDS']);
                        $newQids = array();
                        foreach ($qIds as $qId) {
                            $qTitle = $questionTitles[$qId];
                            if (isset($newData['newQuestionIds'][$qTitle])) {
                                $newQids[] = $newData['newQuestionIds'][$qTitle];
                            } else {
                                $newQids[] = $newData["nameGenQId"];
                            }
                        }
                        $newAlter->nameGenQIds = implode(",", $newQids);
                    } else {
                        $qTitle = $questionTitles[$alter['NAMEGENQIDS']];
                        if (isset($newData['newQuestionIds'][$qTitle])) {
                            $newAlter->nameGenQIds = $newData['newQuestionIds'][$qTitle];
                        } else {
                            $newAlter->nameGenQIds = $newData["nameGenQId"];
                        }
                    }
                }
                if (is_numeric($alter['ORDERING'])) {
                    $newAlter->ordering = $alter['ORDERING'];
                }else{
                    $nGorder = json_decode($alter['ORDERING'], true);
                    $newOrder = array();
                    foreach ($nGorder as $nQid=>$norder) {
                        if (isset($newData['newQuestionIds'][$nQid])) {
                            $newOrder[$newData['newQuestionIds'][$nQid]] = $norder;
                        } else {
                            $newOrder[$nQid] = $norder;
                        }
                    }
                    $newAlter->ordering = json_encode($newOrder);
                }
                if (!$newAlter->save()) {
                    echo $newData["nameGenQId"];
                    echo $alter['NAMEGENQIDS'];
                    print_r($newAlter->getErrors());
                    die();
                } else {
                    $newAlterIds[$alter['ID']] = $newAlter->id;
                }
            }
        }
        $optionNames = array();
        foreach ($data['questionOptions'] as $o) {
            $optionNames[$o['ID']] = $o['NAME'];
        }
        foreach ($data['answers'] as $answer) {
            $newAnswer = new Answer;
            if ($newData) {
                $qTitle = $questionTitles[$answer['QUESTIONID']];
                if (!isset($newData['newQuestionIds'][$qTitle])) {
                    continue;
                }
                $newAnswer->questionId = $newData['newQuestionIds'][$qTitle];
                $newAnswer->studyId = $newData['studyId'];
                if ($answer['ANSWERTYPE'] == "MULTIPLE_SELECTION") {
                    $values = explode(',', $answer['VALUE']);
                    foreach ($values as &$value) {
                        if (isset($newData['newOptionIds'][$qTitle . "_" . $optionNames[$value]])) {
                            $value = $newData['newOptionIds'][$qTitle . "_" . $optionNames[$value]];
                        }
                    }
                    $answer['VALUE'] = implode(',', $values);
                }
                $newAnswer->value = html_entity_decode($answer['VALUE'], ENT_QUOTES);
                if ($answer['OTHERSPECIFYTEXT']) {
                    foreach (preg_split('/;;/', $answer['OTHERSPECIFYTEXT']) as $other) {
                        if ($other && strstr($other, ':')) {
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
            if (is_numeric($answer['ALTERID1']) && isset($newAlterIds[$answer['ALTERID1']])) {
                $newAnswer->alterId1 = $newAlterIds[$answer['ALTERID1']];
            }
            if (is_numeric($answer['ALTERID2']) && isset($newAlterIds[$answer['ALTERID2']])) {
                $newAnswer->alterId2 = $newAlterIds[$answer['ALTERID2']];
            }
            if (!$newAnswer->save()) {
                print_r($newAnswer->getErrors());
                die();
            }
        }
        foreach ($data['notes'] as $note) {
            $newNote = new Note;
            $newNote->alterId = $newAlterIds[(int)$note["ALTERID"]];
            $newNote->expressionId = $note["EXPRESSIONID"];
            $newNote->interviewId = $newInterviewIds[(int)$note["INTERVIEWID"]];
            $newNote->notes = html_entity_decode($note["NOTES"], ENT_QUOTES);
            $newNote->save();
        }
        foreach ($data['graphs'] as $graph) {
            $newGraph = new Graph;
            $newGraph->expressionId = $note["EXPRESSIONID"];
            $newGraph->interviewId = $newInterviewIds[(int)$note["INTERVIEWID"]];
            $newGraph->params = $note["PARAMS"];
            $newGraph->nodes = $note["NODES"];
            $newGraph->save();
        }
    }

}
