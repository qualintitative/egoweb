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
use yii\helpers\Url;
use app\models\Study;
use app\models\Question;
use app\models\Expression;
use yii\helpers\ArrayHelper;
use app\models\Interview;
use app\models\Answer;
use app\models\Alters;
use app\models\MatchedAlters;
/**
 * Site controller
 */
class DataController extends Controller
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
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        $questionIds = [];
        $questions = Question::findAll(array("subjectType"=>"ALTER_PAIR", "studyId"=>$id));
        foreach ($questions as $question) {
            $questionIds[] = $question->id;
        }
        $expressions = ArrayHelper::map(
            Expression::findAll(["studyId"=>$study->id, "questionId"=>$questionIds])
        , 'id','name');
        return $this->render('index',['study'=>$study, 'expressions'=>$expressions]);
    }

    public function actionVisualize()
    {
        $graphs = array();
        if (isset($_GET['interviewId'])) {
            $interview = Interview::model()->findByPK($_GET['interviewId']);
            $studyId = $interview->studyId;
            if (!$studyId) {
                echo "No studyId found for interviewId = ".$_GET['interviewId'];
                return;
            }
            $criteria = array(
                'condition'=>"subjectType = 'ALTER_PAIR' AND studyId = $studyId",
            );
            $questions = Question::model()->findAll($criteria);
            $questionIds = array();
            foreach ($questions as $question) {
                $questionIds[] = $question->id;
            }
            $questionIds = implode(",", $questionIds);
            if (!$questionIds) {
                $questionIds = 0;
            }
            $criteria = array(
                'condition'=>"studyId = $studyId AND questionId in (" . $questionIds . ")",
            );
            $alter_pair_expression = Expression::model()->findAll($criteria);
            $alter_pair_expression_ids = array();
            foreach ($alter_pair_expression as $expression) {
                $alter_pair_expression_ids[] = $expression->id;
            }
            if (count($alter_pair_expression_ids) < 1) {
                //echo "NO ALTER PAIR EXPRESSION IDS FOUND FOR QUESTION IDS ".(string)$questionIds;
                $alter_pair_expressions = array();
            } else {
                $all_expression_ids = $alter_pair_expression_ids;
                foreach ($alter_pair_expression_ids as $id) {
                    $criteria = array(
                        'condition'=>"FIND_IN_SET($id, value)",
                    );
                    $expressions = Expression::model()->findAll($criteria);
                    foreach ($expressions as $e) {
                        $all_expression_ids[] = $e->id;
                    }
                }
                $criteria = array(
                    'condition'=>"id in (" . implode(",", $all_expression_ids) . ")",
                );
                $alter_pair_expressions = Expression::model()->findAll($criteria);
            }

            if (isset($_GET['print'])) {
                $this->renderPartial(
                    'print',
                    array(
                        'graphs'=>$graphs,
                        'studyId'=>$studyId,
                        'alter_pair_expressions'=> $alter_pair_expressions,
                        'interviewId'=>$_GET['interviewId'],
                    ),
                    false,
                    true
                );
            } else {
                $this->render(
                    'visualize',
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

    public function actionExportegoalter()
    {
        if (!isset($_POST['studyId'])) {
            die("no study selected");
        }

        $filePath = getcwd()."/assets/".$_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-ego-alter.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json"=>"success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-ego-alter.csv", "w") or die("Unable to open file!");
            $interview->exportEgoAlterData($file, $withAlters);
            return $this->renderAjax("/layouts/ajax", ["json"=>"success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json"=>"fail"]);
    }

    public function actionExportegoalterall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        if (isset($_POST['expressionId'])) {
            $expressionId = $_POST['expressionId'];
        } else {
            $expressionId = '';
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $study = Study::findOne($_POST['studyId']);

        // fetch questions
        $all_questions = Question::find()->where(["studyId"=>$_POST['studyId']])->orderBy(["ordering"=>"ASC"])->all();
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach($all_questions as $question){
            if($question->subjectType == "EGO_ID")
                $ego_id_questions[] = $question;
            if($question->subjectType == "EGO")
                $ego_questions[] = $question;
            if($question->subjectType == "ALTER")
                $alter_questions[] = $question;
            if($question->subjectType == "NETWORK")
                $network_questions[] = $question;
            if($question->subjectType == "NAME_GENERATOR")
                $name_gen_questions[] = $question;
            if($question->subjectType == "PREVIOUS_ALTER")
                $previous_questions[] = $question;
        }

        $headers = array();
        $headers[] = 'Interview ID';
        $headers[] = 'Alter ID';
        $headers[] = "EgoID";
        $headers[] = 'Start Time';
        $headers[] = 'End Time';
        foreach ($ego_id_questions as $question) {
            $headers[] = $question->title;
        }
        foreach ($ego_questions as $question) {
            $headers[] = $question->title;
        }
        foreach ($network_questions as $question) {
            $headers[] = $question->title;
        }
        if ($expressionId) {
            $headers[] = "Density";
            $headers[] = "Max Degree Value";
            $headers[] = "Max Betweenness Value";
            $headers[] = "Max Eigenvector Value";
            $headers[] = "Degree Centralization";
            $headers[] = "Betweenness Centralization";
            $headers[] = "Components";
            $headers[] = "Dyads";
            $headers[] = "Isolates";
        }
        $matchAtAll = MatchedAlters::findOne(array(
            "studyId = " . $study->id,
        ));
        if(isset($study->multiSessionEgoId) && $study->multiSessionEgoId){
            $multiQs = $study->multiIdQs();
            foreach($multiQs as $q){
                $s = Study::findOne($q->studyId);
                $headers[] = $s->name;
            }
        }
        if ($matchAtAll) {
            $headers[] = "Dyad Match ID";
            $headers[] = "Match User";
            $headers[] = "Alter Number";
            if($withAlters){
                $headers[] = "Alter Name";
                $headers[] = "Matched Alter Name";
            }
            $headers[] = "Alter Pair ID";
        } else {
            $headers[] = "Alter Number";
            if($withAlters)
                $headers[] = "Alter Name";
        }
        foreach ($name_gen_questions as $question) {
            $headers[] = $question->title;
        }
        foreach ($previous_questions as $question) {
            $headers[] = $question->title;
        }
        foreach ($alter_questions as $question) {
            $headers[] = $question->title;
        }
        if ($expressionId) {
            $headers[] = "Degree";
            $headers[] = "Betweenness";
            $headers[] = "Eigenvector";
        }

        $interviewIds = array();
        $interviewIds = explode(",",$_POST['interviewIds']);
        /*
        foreach ($_POST['export'] as $key=>$value) {
            $interviewIds[] = $key;
        }
        */
        // start generating export file

        $text = implode(',', $headers) . "\n";
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/". $interviewId . "-ego-alter.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text,'test.csv')->send();
    }

    public function actionExportalterpair()
    {
        if (!isset($_POST['studyId'])) {
            die("no study selected");
        }

        $study = Study::findOne($_POST['studyId']);

        $filePath = getcwd()."/assets/".$_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-alter-pair.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json"=>"success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-alter-pair.csv", "w") or die("Unable to open file!");
            $interview->exportAlterPairData($file, $study, $withAlters);
            return $this->renderAjax("/layouts/ajax", ["json"=>"success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json"=>"fail"]);
    }

    public function actionExportalterpairall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("no study selected");
        }

        $filePath = getcwd()."/assets/".$_POST['studyId'];

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $study = Study::findOne($_POST['studyId']);

        $alter_pair_questions = Question::findAll(["studyId"=>$study->id, "subjectType"=>"ALTER_PAIR"]);

        $idNumber = "Number";

        $headers = array();
        $headers[] = 'Interview ID';
        $headers[] = 'EgoID';
        $headers[] = "Alter 1 " . $idNumber;
        if($withAlters)
            $headers[] = "Alter 1 Name";
        $headers[] = "Alter 2 " . $idNumber;
        if($withAlters)
            $headers[] = "Alter 2 Name";
        foreach ($alter_pair_questions as $question) {
            $headers[] = $question->title;
        }

        $interviewIds = array();
        $interviewIds = explode(",",$_POST['interviewIds']);
        
        $text = implode(',', $headers) . "\n";
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/". $interviewId . "-alter-pair.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-alter-pair.csv.csv')->send();
    }

    public function actionExportother()
    {
        $filePath = getcwd()."/assets/".$_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-other-specify.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json"=>"success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }
        
        $study = Study::findOne($_POST['studyId']);
        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-other-specify.csv", "w") or die("Unable to open file!");
            $interview->exportOtherData($file, $study);
            return $this->renderAjax("/layouts/ajax", ["json"=>"success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json"=>"fail"]);
    }

    public function actionExportotherall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $study = Study::findOne($_POST['studyId']);

        $headers = array();
        $headers[] = 'INTERVIEW ID';
        $headers[] = "EGO ID";
        $headers[] = "QUESTION";
        $headers[] = "ALTER ID";
        $headers[] = "RESPONSE OPTION";
        $headers[] = "TEXT";

        $text = implode(',', $headers) . "\n";
        $interviewIds = explode(",",$_POST['interviewIds']);
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/". $interviewId . "-other-specify.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-other-specify.csv')->send();
    }

    public function actionDeleteinterviews()
    {
        if (!isset($_POST['interviewIds'])) {
            return false;
        }
        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);
        foreach ($interviewIds as $interviewId) {
            $interview = Interview::findOne($interviewId);
            if ($interview) {
                $answers = Answer::findAll(array("interviewId"=>$interviewId));
                foreach ($answers as $answer) {
                    $answer->delete();
                }
                $alters = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId .", interviewId)"))
                ->all();
                foreach ($alters as $alter) {
                    if (strstr($alter->interviewId, ",")) {
                        $interviewIds = explode(",", $alter->interviewId);
                        $interviewIds = array_diff($interviewIds, array($interviewId));
                        $alter->interviewId = implode(",", $interviewIds);
                        $alter->save();
                    }else{
                        $alter->delete();
                    }
                }
                $interview->delete();
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
