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
use app\models\QuestionOption;
use app\models\Expression;
use yii\helpers\ArrayHelper;
use app\models\Interview;
use app\models\Answer;
use app\models\Alters;
use app\models\Note;
use app\models\Graph;
use app\models\MatchedAlters;

/**
 * Data controller
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
                        'actions' => ['savegraph'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
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

    public function actionEdit($id)
    {
        $interview = Interview::findOne($id);
        $interview->completed = 0;
        $interview->save();
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Lists interviews of a given study for further data processing
     * /data/{id}
     * @return mixed
     */
    public function actionIndex($id)
    {
        $study = Study::findOne($id);
        $this->view->title = $study->name;
        $questionIds = [];

        if ($study->multiSessionEgoId) {
            $all_studies = [];
            $multiStudyIds = $study->multiStudyIds();
            $questions = Question::findAll(array("subjectType" => "ALTER_PAIR", "studyId" => $multiStudyIds));
            foreach ($questions as $question) {
                $questionIds[] = $question->id;
            }
            $result = Study::find()->where(["id" => $multiStudyIds])->all();
            foreach ($result as $s) {
                $all_studies[$s->id] = $s->name;
            }
        } else {
            $questions = Question::findAll(array("subjectType" => "ALTER_PAIR", "studyId" => $id));
            foreach ($questions as $question) {
                $questionIds[] = $question->id;
            }
            $multiStudyIds = [$id];
            $all_studies[$id] = $study->name;
        }
        $expressions = [];
        foreach ($multiStudyIds as $studyId) {
            $result = Expression::findAll(["studyId" => $studyId, "questionId" => $questionIds]);
            if (!isset($expressions[$studyId]))
                $expressions[$studyId] = [];
            foreach ($result as $e) {
                $expressions[$studyId][$e->id] = $e->name;
            }
        }

        $alters = [];
        $allInterviewIds = [];
        $interviews = Interview::find()->where(["studyId" => $multiStudyIds])->all();
        $result = Answer::findAll([
            "studyId" => $multiStudyIds,
            "questionType" => "EGO_ID",
        ]);
        $multiQs = $study->multiIdQs();
        $egoQs = [];
        foreach($multiQs as $multiQ){
            $egoQs[] = $multiQ->id;
        }
        $egoid_answers = array();
        $linkIds = [];
        $exists = [];

        foreach ($result as $answer) {
            if ($answer->answerType == "RANDOM_NUMBER" || $answer->answerType == "STORED_VALUE")
                continue;
            if (!isset($egoid_answers[$answer->interviewId]))
                $egoid_answers[$answer->interviewId] = [];
            if(in_array($answer->questionId, $egoQs))
                $linkIds[$answer->interviewId] = $answer->value;
            $egoid_answers[$answer->interviewId][] = $answer->value;
        }

        

        $egoIds = [];
        $dupeCount = [];
        $interviewStudyIds = [];
        foreach ($interviews as $interview) {
            $alters[$interview->id] = 0;
            if (!isset($egoid_answers[$interview->id]))
                $egoid_answers[$interview->id] = ["error"];
            $ego_id_string = implode("_", $egoid_answers[$interview->id]);
            $egoIds[$interview->id] = $ego_id_string;
            if(!isset( $dupeCount[$interview->studyId."_".$ego_id_string]))
                $dupeCount[$interview->studyId."_".$ego_id_string] = 1;
            else
                $dupeCount[$interview->studyId."_".$ego_id_string]++;
            $allInterviewIds[] = $interview->id;
            $interviewStudyIds[$interview->id] = $interview->studyId;
        }
        if(count($allInterviewIds) > 0){
            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("SELECT count(*) as count, interviewId FROM answer where interviewId  IN (". implode(",",$allInterviewIds).") AND questionType != 'EGO_ID' GROUP BY interviewId");
            $result = $command->queryAll();
            foreach ($result as $row) {
                if(!in_array($row['interviewId'], $exists))
                    $exists[] = $row['interviewId'];
            }
        }
        $isDupe = [];
        foreach($allInterviewIds as $interviewId){
            $dupeId = $interviewStudyIds[$interviewId] . "_" . $egoIds[$interviewId];
            if(isset($linkIds[$interviewId]) && isset($dupeCount[$dupeId]) && $dupeCount[$dupeId] > 1 && !in_array($linkIds[$interviewId], $isDupe)){
                $isDupe[] = $linkIds[$interviewId];
            }
        }
        $allAlters = (new \yii\db\Query())
            ->select(['interviewId'])
            ->from('alters')
            ->all();
        foreach ($allAlters as $alter) {
            $interviewIds = explode(",", $alter['interviewId']);
            foreach ($interviewIds as $interviewId) {
                if (in_array($interviewId, $allInterviewIds)) {
                    if (isset($alters[$interviewId]))
                        $alters[$interviewId]++;
                }
            }
        }
        return $this->render('index', [
            'study' => $study,
            'all_studies' => $all_studies,
            'interviewStudyIds' => $interviewStudyIds,
            'multiStudyIds' => $multiStudyIds,
            'expressions' => $expressions,
            'interviews' => $interviews,
            'alters' => $alters,
            'egoIds' => $egoIds,
            'isDupe'=>$isDupe,
            'linkIds'=> $linkIds,
            'exists'=>$exists
        ]);
    }


    /**
     * Generates network graph using alter data from an interview
     * /data/visualize/{id}
     */
    public function actionVisualize($id)
    {
        $graphs = array();
        if (isset($id)) {
            $interview = Interview::findOne($id);
            $studyId = $interview->studyId;
            $study = Study::findOne($studyId);
            $this->view->title = $study->name;
            $questions = Question::findAll(["subjectType" => "ALTER_PAIR", "studyId" => $studyId]);
            $questionIds = array();
            foreach ($questions as $question) {
                $questionIds[] = $question->id;
            }
            $questionIds = implode(",", $questionIds);
            if (!$questionIds) {
                $questionIds = 0;
            }
            $alter_pair_expressions = Expression::findAll(["questionId" => $questionIds, "studyId" => $studyId]);

            $result = Question::find()->where(["studyId" => $studyId])->andWhere(['!=', 'subjectType', 'EGO_ID'])->orderBy(["ordering" => "ASC"])->asArray()->all();
            $questions = [];
            $notes = [];
            foreach ($result as $question) {
                $question['options'] = QuestionOption::find()->where(['questionId' => $question['id']])->orderBy(["ordering" => "ASC"])->asArray()->all();
                $questions[$question['id']] = $question;
            }
            $new_question = new Question;
            $new_question->studyId = $study->id;
            $new_question->subjectType = "NETWORK";
            $new_question = $new_question->toArray();
            $expressions = [];
            $results = Expression::find()->where(["studyId" => $study->id])->asArray()->all();
            foreach ($results as $expression) {
                $expressions[$expression['id']] = $expression;
            }
            $answerList = Answer::findAll(array('interviewId' => $id));
            foreach ($answerList as $answer) {
                if ($answer->alterId1 && $answer->alterId2) {
                    $array_id = $answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2;
                } elseif ($answer->alterId1 && !$answer->alterId2) {
                    $array_id = $answer->questionId . "-" . $answer->alterId1;
                } else {
                    $array_id = $answer->questionId;
                }
                $answers[$array_id] = Tools::mToA($answer);
            }
            $alters = array();
            $results = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(:interviewId, interviewId)"))
                ->addParams([':interviewId' => $id])
                ->all();
            foreach ($results as $result) {
                $alters[$result->id] = Tools::mToA($result);
            }
            $results = Graph::find()->where(array('interviewId' => $id))->all();
            foreach ($results as $result) {
                $graphs[$result->expressionId] = Tools::mToA($result);
            }
            $results = Note::find()->where(array("interviewId" => $id))->all();
            foreach ($results as $result) {
                $notes[$result->expressionId][$result->alterId] = $result->notes;
            }
            if (isset($_GET['print'])) {
                $this->renderPartial(
                    'print',
                    array(
                        'graphs' => $graphs,
                        'studyId' => $studyId,
                        'alter_pair_expressions' => $alter_pair_expressions,
                        'interviewId' => $_GET['interviewId'],
                    ),
                    false,
                    true
                );
            } else {
                return $this->render(
                    'visualize',
                    array(
                        'graphs' => $graphs,
                        'study' => $study,
                        'interview' => $interview,
                        'studyId' => $studyId,
                        'alter_pair_expressions' => $alter_pair_expressions,
                        'interviewId' => $id,
                        'questions' => $questions,
                        'expressions' => $expressions,
                        'new_question' => $new_question,
                        "answers" => json_encode($answers),
                        "alters" => json_encode($alters),
                        "graphs" => json_encode($graphs),
                        "allNotes" => json_encode($notes),
                    )
                );
            }
        }
    }

    /**
     * Exports ego-alter data from single interview
     * /data/expertegoalter
     */
    public function actionExportegoalter()
    {
        $interview = Interview::findOne($_POST['interviewId']);
        $filePath = getcwd() . "/assets/" . $interview->studyId;
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-ego-alter.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $multiSesh = true;
        if (isset($_POST['multiSession'])) {
            $multiSesh = boolval($_POST['multiSession']);
        }

        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-ego-alter.csv", "w") or die("Unable to open file!");
            $interview->exportEgoAlterData($file, $withAlters, $multiSesh, $_POST['studyOrder']);
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json" => "fail"]);
    }

    public function actionExportegoalterall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $multiSesh = false;
        if (isset($_POST['multiSession'])) {
            $multiSesh = boolval($_POST['multiSession']);
        }

        $headers = array();
        $studyIds = [];
        $studyNames = [];
        $study = Study::findOne($_POST['studyId']);
        if ($study->multiSessionEgoId && $multiSesh) {
            $multiQs = $study->multiIdQs();
            $studyOrder =  $_POST['studyOrder'];
            if ($studyOrder && stristr($studyOrder, ","))
                $studyOrder = explode(",", $studyOrder);
            foreach ($multiQs as $q) {
                $s = Study::findOne($q->studyId);
                $studyNames[$s->id] = $s->name;
                if ($studyOrder && stristr($_POST['studyOrder'], ",")) {
                    $studyIds[array_search($q->studyId, $studyOrder)] =  $q->studyId;
                } else {
                    $studyIds[] = $q->studyId;
                }
            }
        } else {
            $studyIds[] = $study->id;
        }
        ksort($studyIds);

        if ($multiSesh)
            $headers[] =  "Link ID";
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $multi_graph_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach ($studyIds as $index => $studyId) {
            $all_questions = Question::find()->where(["studyId" => $studyId])->orderBy(["ordering" => "ASC"])->all();
            $ego_id_questions[$studyId] = [];
            $ego_questions[$studyId]  = [];
            $alter_questions[$studyId]  = [];
            $network_questions[$studyId]  = [];
            $name_gen_questions[$studyId]  = [];
            $previous_questions[$studyId]  = [];
            $multi_graph_questions[$studyId] = [];

            foreach ($all_questions as $question) {
                if ($question->subjectType == "EGO_ID") {
                    $ego_id_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "EGO") {
                    $ego_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "ALTER") {
                    $alter_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "NETWORK") {
                    $network_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "MULTI_GRAPH") {
                    $multi_graph_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "NAME_GENERATOR") {
                    $name_gen_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "PREVIOUS_ALTER") {
                    $previous_questions[$studyId][] = $question;
                }
            }
        }
        foreach ($studyIds as $index => $studyId) {
            $hCount = 0;
            $counter = "";
            if ($multiSesh)
                $counter = "_" .  ($index + 1);
            $headers[] = 'EgoID' . $counter;
            $headers[] = 'Interview ID' . $counter;
            $headers[] = 'Start Time' . $counter;
            $headers[] =  'End Time' . $counter;

            foreach ($ego_id_questions[$studyId] as $question) {
                $headers[] =  $question->title  . $counter;
                $hCount++;
            }
            foreach ($ego_questions[$studyId] as $question) {
                $headers[] = $question->title  . $counter;
                $hCount++;
            }
            foreach ($network_questions[$studyId] as $question) {
                $headers[] =  $question->title  . $counter;
                $hCount++;
            }
            foreach ($multi_graph_questions[$studyId] as $question) {
                $headers[] =  $question->title  . $counter;
                $hCount++;
            }

            if (isset($_POST[$studyId . '_expressionId']) && $_POST[$studyId . '_expressionId'] != "") {
                $headers[] =  "Density"  . $counter;
                $headers[] = "Max Degree Value"  . $counter;
                $headers[] =  "Max Betweenness Value"  . $counter;
                $headers[] = "Max Eigenvector Value"  . $counter;
                $headers[] = "Degree Centralization"  . $counter;
                $headers[] =  "Betweenness Centralization"  . $counter;
                $headers[] = "Components"  . $counter;
                $headers[] =  "Dyads"  . $counter;
                $headers[] =  "Isolates"  . $counter;
                $hCount += 9;
            }
        }
        $matchAtAll = MatchedAlters::find()->where(["studyId" => $studyId])->one();
        if ($matchAtAll) {
            $headers[] =  "Dyad Match ID"  . $counter;
            $headers[] =  "Match User"  . $counter;
            $headers[] =  "Alter Number"  . $counter;
            $hCount += 3;
            if ($withAlters) {
                $headers[] =  "Alter Name"  . $counter;
                $headers[] =  "Matched Alter Name"  . $counter;
                $hCount += 2;
            }
            $headers[] =   "Alter Pair ID"  . $counter;
            $hCount++;
        } else {
            $headers[] =  "Alter Number";
            $hCount++;
            if ($withAlters) {
                $headers[] =  "Alter Name";
                $hCount++;
            }
        }

        foreach ($studyIds as $index => $studyId) {
            $counter = "";
            if ($multiSesh)
                $counter = "_" .  ($index + 1);
            foreach ($name_gen_questions[$studyId] as $question) {
                $headers[] =  $question->title  . $counter;
            }
            foreach ($previous_questions[$studyId] as $question) {
                $headers[] = $question->title  . $counter;
            }
            foreach ($alter_questions[$studyId] as $question) {
                $headers[] = $question->title  . $counter;
            }
            if (isset($_POST[$studyId . '_expressionId']) && $_POST[$studyId . '_expressionId'] != "") {
                $headers[] =  "Degree"  . $counter;
                $headers[] =  "Betweenness"  . $counter;
                $headers[] =   "Eigenvector"  . $counter;
            }

        }
        if ($multiSesh &&  $study->multiSessionEgoId) {
            $headers[] =  'Alter ID';
            foreach ($studyIds as $index => $studyId) {
                $counter = "";
                if ($multiSesh)
                    $counter = "_" .  ($index + 1);
                $headers[] = $studyNames[$studyId] . $counter;
            }
        }

        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);
        $interviews = Interview::findAll(["id" => $interviewIds]);


        $text = implode(',', $headers) . "\n";
        $exported = [];
        foreach ($interviews as $interview) {
            $filePath = getcwd() . "/assets/" . $interview->studyId . "/" . $interview->id . "-ego-alter.csv";
            if (file_exists($filePath)) {
                //   if (array_search($interview->studyId, $studyIds) > 0) {
                // indent file output
                $rows = explode("\n", file_get_contents($filePath));
                $cols = explode(",", $rows[0]);
                if (!in_array($cols[0], $exported)) {
                    if($multiSesh)
                        $exported[] = $cols[0];
                } else {
                    continue;
                }
                foreach ($rows as $row) {

                    $cols = explode(",", $row);
                    $line = [];

                    foreach ($cols as $index => $col) {
                        $line[] = $col;
                    }
                    if ($row != "")
                        $text .= implode(",", $line) . "\n";
                }
                unlink($filePath);
                //    } else {
                // $text .= file_get_contents($filePath);
                //   unlink($filePath);
                // }
            }
        }
        if (isset($_POST['filename']) && $_POST['filename'] && $multiSesh)
            $filename = $_POST['filename']  . '-ego-alter';
        else
            $filename = $study->name . '-ego-alter';
        return $this->response->sendContentAsFile($text, $filename . '.csv', "text/csv; charset=UTF-8")->send();
    }

    public function actionExportegolevel()
    {
        if (!isset($_POST['studyId'])) {
            die("no study selected");
        }

        $filePath = getcwd() . "/assets/" . $_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-ego-level.csv")) {
            echo "success";
        }

        $multiSesh = false;
        if (isset($_POST['multiSession'])) {
            $multiSesh = boolval($_POST['multiSession']);
        }


        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $studyOrder = [];
        if(isset($_POST['studyOrder']))
            $studyOrder =  $_POST['studyOrder'];

        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-ego-level.csv", "w") or die("Unable to open file!");
            $interview->exportEgoLevel($file, $multiSesh, $studyOrder);
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json" => "fail"]);
    }

    public function actionExportegolevelall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $headers = [];
        $studyIds = [];
        $studyNames = [];
        $multiSesh = false;
        if (isset($_POST['multiSession'])) {
            $multiSesh = boolval($_POST['multiSession']);
        }

        $study = Study::findOne($_POST['studyId']);
        if ($study->multiSessionEgoId && $multiSesh) {
            $multiQs = $study->multiIdQs();
            $studyOrder =  $_POST['studyOrder'];
            if ($studyOrder && stristr($studyOrder, ","))
                $studyOrder = explode(",", $studyOrder);
            foreach ($multiQs as $q) {
                $s = Study::findOne($q->studyId);
                $studyNames[$s->id] = $s->name;
                if ($studyOrder && stristr($_POST['studyOrder'], ",")) {
                    $studyIds[array_search($q->studyId, $studyOrder)] =  $q->studyId;
                } else {
                    $studyIds[] = $q->studyId;
                }
            }
        } else {
            $studyIds[] = $study->id;
        }
        ksort($studyIds);

        if ($multiSesh)
            $headers[] =  "Link ID";
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach ($studyIds as $index => $studyId) {
            $all_questions = Question::find()->where(["studyId" => $studyId])->orderBy(["ordering" => "ASC"])->all();
            $ego_id_questions[$studyId] = [];
            $ego_questions[$studyId]  = [];
            $alter_questions[$studyId]  = [];
            $network_questions[$studyId]  = [];
            $name_gen_questions[$studyId]  = [];
            $previous_questions[$studyId]  = [];
            foreach ($all_questions as $question) {
                if ($question->subjectType == "EGO_ID") {
                    $ego_id_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "EGO") {
                    $ego_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "ALTER") {
                    $alter_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "NETWORK") {
                    $network_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "NAME_GENERATOR") {
                    $name_gen_questions[$studyId][] = $question;
                }
                if ($question->subjectType == "PREVIOUS_ALTER") {
                    $previous_questions[$studyId][] = $question;
                }
            }
        }
        foreach ($studyIds as $index => $studyId) {
            $hCount = 0;
            $counter = "";
            if ($multiSesh)
                $counter = "_" .  ($index + 1);
            $headers[] = 'EgoID' . $counter;
            $headers[] = 'Interview ID' . $counter;
            $headers[] = 'Start Time' . $counter;
            $headers[] =  'End Time' . $counter;

            foreach ($ego_id_questions[$studyId] as $question) {
                $headers[] =  $question->title  . $counter;
                $hCount++;
            }
            foreach ($ego_questions[$studyId] as $question) {
                $headers[] = $question->title  . $counter;
                $hCount++;
            }
            foreach ($network_questions[$studyId] as $question) {
                $headers[] =  $question->title  . $counter;
                $hCount++;
            }
        }


        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);

        $text = implode(',', $headers) . "\n";
        $exported = [];
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/" . $interviewId . "-ego-level.csv";
            if (file_exists($filePath)) {
                $rows = explode("\n", file_get_contents($filePath));
                $cols = explode(",", $rows[0]);
                if (!in_array($cols[0], $exported)) {
                    if($multiSesh)
                        $exported[] = $cols[0];
                } else {
                    continue;
                }
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        if (isset($_POST['filename']) && $_POST['filename'] && $multiSesh)
            $filename = $_POST['filename']  . '-ego-level';
        else
            $filename = $study->name . '-ego-level';
        return $this->response->sendContentAsFile($text, $filename . '.csv', "text/csv; charset=UTF-8")->send();
    }

    public function actionExportalterpair()
    {
        if (!isset($_POST['studyId'])) {
            die("no study selected");
        }

        $study = Study::findOne($_POST['studyId']);
        $multiStudyIds = $study->multiStudyIds();

        $filePath = getcwd() . "/assets/" . $_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-alter-pair.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $withAlters = false;
        if (isset($_POST['withAlters']))
            $withAlters = boolval($_POST['withAlters']);

        $multiSesh = false;
        if (isset($_POST['multiSession']))
            $multiSesh = boolval($_POST['multiSession']);

        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-alter-pair.csv", "w") or die("Unable to open file!");
            $interview->exportAlterPairData($file, $study, $withAlters, $multiSesh, $_POST['studyOrder']);
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json" => "fail"]);
    }

    public function actionExportalterpairall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("no study selected");
        }

        $filePath = getcwd() . "/assets/" . $_POST['studyId'];

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }
        $multiSesh = false;
        if (isset($_POST['multiSession'])) {
            $multiSesh = boolval($_POST['multiSession']);
        }
        $study = Study::findOne($_POST['studyId']);
        $studyIds = [];
        if ($study->multiSessionEgoId && $multiSesh) {
            $multiStudyIds = $study->multiStudyIds();
            $studyOrder =  $_POST['studyOrder'];
            if ($studyOrder && stristr($studyOrder, ","))
                $studyOrder = explode(",", $studyOrder);
            foreach ($multiStudyIds as $studyId) {
                if ($studyOrder && stristr($_POST['studyOrder'], ",")) {
                    $studyIds[array_search($studyId, $studyOrder)] =  $studyId;
                } else {
                    $studyIds[] = $studyId;
                }
            }
        } else {
            $studyIds[] = $study->id;
        }
        ksort($studyIds);

        $alter_pair_questions = [];
        $studyNames = [];
        foreach ($studyIds as $studyId) {
            $s = Study::findOne($studyId);
            $studyNames[$studyId] = $s->name;
            $alter_pair_questions[$studyId] = Question::findAll(["studyId" => $studyId, "subjectType" => "ALTER_PAIR"]);
        }

        $headers = array();
        if ($multiSesh)
            $headers[] =  'Link ID';
        foreach ($studyIds as $index => $studyId) {
            $counter = "";
            if ($multiSesh)
                $counter = "_" .  ($index + 1);
            $headers[] =  'Interview ID'  . $counter;
            $headers[] =  'EgoID'  . $counter;
        }
        foreach ($studyIds as $index => $studyId) {
            $counter = "";
            if ($multiSesh)
                $counter =  "_" . ($index + 1);
            if ($index == 0) {
                $headers[] = "Alter 1 Number";

                if ($withAlters) {
                    $headers[] = "Alter 1 Name";
                }
                $headers[] = "Alter 2 Number";

                if ($withAlters) {
                    $headers[] = "Alter 2 Name";
                }
            }
            foreach ($alter_pair_questions[$studyId] as $question) {

                $headers[] = $question->title  . $counter;
            }
        }

        $headers[] = "Alter 1 ID";
        $headers[] = "Alter 2 ID";

        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);

        $text = implode(',', $headers) . "\n";
        $exported = [];
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/" . $interviewId . "-alter-pair.csv";
            if (file_exists($filePath)) {
                $rows = explode("\n", file_get_contents($filePath));
                $cols = explode(",", $rows[0]);
                $len = count($cols);
                if($len < 2)
                    continue;
                $array_id = $cols[$len - 2] . "and" . $cols[$len - 1];
                if (!in_array($array_id, $exported)) {
                    if($multiSesh)
                        $exported[] = $array_id;
                } else {
                    continue;
                }
                foreach ($rows as $row) {
                    $cols = explode(",", $row);
                    $line = [];
                    foreach ($cols as $index => $col) {
                        $line[] = $col;
                    }
                    if ($row != "")
                        $text .= implode(",", $line) . "\n";
                }
                unlink($filePath);
            }
        }
        if (isset($_POST['filename']) && $_POST['filename'] && $multiSesh)
            $filename = $_POST['filename']  . '-alter-pair';
        else
            $filename = $study->name . '-alter-pair';
        return $this->response->sendContentAsFile($text, $filename . '.csv', "text/csv; charset=UTF-8")->send();
    }

    public function actionExportother()
    {
        $filePath = getcwd() . "/assets/" . $_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-other-specify.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $study = Study::findOne($_POST['studyId']);
        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-other-specify.csv", "w") or die("Unable to open file!");
            $interview->exportOtherData($file, $study);
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json" => "fail"]);
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
        $interviewIds = explode(",", $_POST['interviewIds']);
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/" . $interviewId . "-other-specify.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-other-specify.csv', "text/csv; charset=UTF-8")->send();
    }

    public function actionExportcompletion()
    {
        $filePath = getcwd() . "/assets/" . $_POST['studyId'];
        if (file_exists($filePath . "/" . $_POST['interviewId'] . "-completion-time.csv")) {
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $study = Study::findOne($_POST['studyId']);
        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-completion-time.csv", "w") or die("Unable to open file!");
            $interview->exportCompletionData($file, $study);
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json" => "fail"]);
    }


    public function actionExportcompletionall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $study = Study::findOne($_POST['studyId']);

        $headers = array();
        $headers[] = 'INTERVIEW ID';
        $headers[] = "EGO ID";
        $all_questions = Question::find()->where(["studyId" => $_POST['studyId']])->orderBy(["ordering" => "ASC"])->all();
        foreach ($all_questions as $question) {
            $headers[] = $question->title;
        }
        $text = implode(',', $headers) . "\n";
        $interviewIds = explode(",", $_POST['interviewIds']);
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/" . $interviewId . "-completion-time.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-completion-time.csv')->send();
    }

    // export codebook
    public function actionCodebook($id)
    {
        $text = "";
        $rows = [];
        $study = Study::findOne($id);
        $studyIds = [];
        $multiSesh = false;
        if (isset($_GET['multiSession'])) {
            $multiSesh = boolval($_GET['multiSession']);
        }
        if ($study->multiSessionEgoId && $multiSesh) {
            $multiStudyIds = $study->multiStudyIds();
            $studyOrder =  $_GET['studyOrder'];
            if ($studyOrder && stristr($studyOrder, ","))
                $studyOrder = explode(",", $studyOrder);
            foreach ($multiStudyIds as $studyId) {
                if ($studyOrder && stristr($_GET['studyOrder'], ",")) {
                    $studyIds[array_search($studyId, $studyOrder)] =  $studyId;
                } else {
                    $studyIds[] = $studyId;
                }
            }
        } else {
            $studyIds[] = $study->id;
        }
        ksort($studyIds);

        $fields = ["Question Order", "Question Title", "Question Prompt", "Stem and Leaf", "Subject Type", "Response Type", "Min", "Max", "Options", "Skip Logic Expression"];
        $rows[] = implode(",", $fields);
        foreach($studyIds as $index=>$studyId){
            $counter = "";
            if ($multiSesh)
                $counter = "_" .  ($index + 1);
            $results = Expression::find()->where(["studyId" => $studyId])->all();
            foreach ($results as $expression) {
                $expressions[$expression->id] = $expression->name;
            }
            $questions = Question::find()->where(["studyId" => $studyId])->orderBy(["ordering" => "ASC"])->all();
            $questionTitles = [];
            $allQuestions = [];
            foreach ($questions as $question) {
                $questionTitles[$question->id] = $question->title;
                if ($question->subjectType == "EGO_ID")
                    $allQuestions[] = $question;
            }
            foreach ($questions as $question) {
                if ($question->subjectType != "EGO_ID")
                    $allQuestions[] = $question;
            }
            $egoIdCount = 0;
            foreach ($allQuestions as $question) {
                $question->prompt = str_replace('’', "'", $question->prompt);
                $question->prompt = preg_replace('/[[:^print:]]/', " ", $question->prompt);
                if($question->citation){
                    $question->citation = str_replace('’', "'", $question->citation);
                    $question->citation = preg_replace('/[[:^print:]]/', " ", $question->citation);
                }
                $fields = [];
                $fields[] = $question->ordering + 1 + ($question->subjectType == "EGO_ID" ? 0 : $egoIdCount);
                $fields[] = $question->title . $counter;
                $fields[] = '"' . strip_tags(str_replace('"', "'", $question->prompt)) . '"';
                if($question->citation)
                    $fields[] = '"' . strip_tags(str_replace('"', "'", $question->citation)) . '"';
                else
                    $fields[] = "";
                $fields[] = $question->subjectType;
                $fields[] = $question->answerType;
                if ($question->answerType == "MULTIPLE_SELECTION") {
                    $fields[] = $question->minCheckableBoxes;
                    $fields[] = $question->maxCheckableBoxes;
                    $optionString = [];
                    $options = QuestionOption::find()->where(["questionId" => $question->id])->orderBy(["ordering" => "ASC"])->all();
                    foreach ($options as $option) {
                        $optionString[] = "'" . $option->name . "'" .  ' = ' . $option->value;
                    }
                    if ($question->dontKnowButton)
                        $optionString[] = "'" . ($question->dontKnowText ? $question->dontKnowText : "Don't Know") . "'" .  ' = ' . $study->valueDontKnow;
                    if ($question->refuseButton)
                        $optionString[] = "'" . ($question->refuseText ? $question->refuseText : "Refuse") . "'" .  ' = ' . $study->valueRefusal;

                    $fields[] = '"' . implode("; ", $optionString) . '"';
                } elseif ($question->answerType == "NUMERICAL") {
                    if ($question->minLimitType == "NLT_LITERAL")
                        $fields[] = $question->minLiteral;
                    if (isset($questionTitles[$question->minPrevQues]) && $question->minLimitType == "NLT_PREVQUES")
                        $fields[] = $questionTitles[$question->minPrevQues];
                    if ($question->maxLimitType == "NLT_LITERAL")
                        $fields[] = $question->maxLiteral;
                    if (isset($questionTitles[$question->maxPrevQues]) && $question->maxLimitType == "NLT_PREVQUES")
                        $fields[] = $questionTitles[$question->maxPrevQues];
                    $fields[] = "";
                } else {
                    $fields[] = "";
                    $fields[] = "";
                    $fields[] = "";
                }
                if ($question->answerReasonExpressionId) {
                    $fields[] = $expressions[$question->answerReasonExpressionId];
                }
                $rows[] = implode(",", $fields);
                if ($question->subjectType == "EGO_ID")
                    $egoIdCount++;
            }
        }
        $text = implode("\r\n", $rows);
        return $this->response->sendContentAsFile($text, $study->name . '-codebook.csv')->send();
    }

    public function actionSavegraph()
    {
        if ($_POST['Graph']) {
            $graph = Graph::findOne(array("interviewId" => $_POST['Graph']['interviewId'], "expressionId" => $_POST['Graph']['expressionId']));
            if (!$graph) {
                $graph = new Graph;
            }
            $graph->attributes = $_POST['Graph'];
            if ($graph->save()) {
                //echo "success";
                $graphs = array();
                $results = Graph::findAll(array('interviewId' => $_POST['Graph']['interviewId']));
                foreach ($results as $result) {
                    $graphs[$result->expressionId] = Tools::mToA($result);
                }
                return $this->renderAjax("/layouts/ajax", ["json" => json_encode($graphs)]);
            }
        }
    }

    public function actionDeletegraph()
    {
        if (isset($_GET['id'])) {
            $graph = Graph::findOne($_GET['id']);
            if ($graph) {
                $graph->delete();
            }
        }
    }

    public function actionGetnote()
    {
        if (isset($_GET['interviewId']) && isset($_GET['expressionId']) && isset($_GET['alterId'])) {
            $model = Note::findOne(array(
                'interviewId' => (int)$_GET['interviewId'],
                'expressionId' => (int)$_GET['expressionId'],
                'alterId' => $_GET['alterId']
            ));
            if (!$model) {
                $model = new Note;
                $model->interviewId = $_GET['interviewId'];
                $model->expressionId = $_GET['expressionId'];
                $model->alterId = $_GET['alterId'];
            }
            return $this->renderAjax('_form_note', array('model' => $model));
        }
    }

    public function actionSavenote()
    {
        if (isset($_POST['Note'])) {
            $new = false;
            if ($_POST['Note']['id']) {
                $note = Note::findOne($_POST['Note']['id']);
            } else {
                $note = new Note;
                $new = true;
            }
            $note->attributes = $_POST['Note'];
            if (!$note->save()) {
                print_r($note->errors);
            }

            echo $note->alterId;
        }
    }

    public function actionDeletenote()
    {
        if (isset($_POST['Note'])) {
            $note = Note::findOne($_POST['Note']['id']);
            $alterId = $note->alterId;
            if ($note) {
                $note->delete();
                echo $alterId;
            }
        }
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
                $answers = Answer::findAll(array("interviewId" => $interviewId));
                foreach ($answers as $answer) {
                    $answer->delete();
                }
                $alters = Alters::find()
                    ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId . ", interviewId)"))
                    ->all();
                foreach ($alters as $alter) {
                    if (strstr($alter->interviewId, ",")) {
                        $interviewIds = explode(",", $alter->interviewId);
                        $interviewIds = array_diff($interviewIds, array($interviewId));
                        $alter->interviewId = implode(",", $interviewIds);
                        if($alter->alterListId != null){
                            $alterListIds = explode(",", $alter->alterListId);
                            $alterListIds = array_filter($alterListIds, function ($value) {
                                return !is_null($value) && $value !== '';
                            });
                            $alterListIds = array_diff($alterListIds, [$interviewId]);
                            $alterListIds = array_unique($alterListIds);
                            $alter->alterListId =  implode(",", $alterListIds);
                        }
                        $alter->save();
                    } else {
                        $alter->delete();
                    }
                }
                $alters = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId . ", alterListId)"))
                ->all();
                foreach ($alters as $alter) {
                    if (strstr($alter->alterListId, ",")) {
                        $alterListIds = explode(",", $alter->alterListId);
                        $alterListIds = array_filter($alterListIds, function ($value) {
                            return !is_null($value) && $value !== '';
                        });
                    }else{
                        $alterListIds = [$alter->alterListId];
                    }
                    $alterListIds = array_diff($alterListIds, [$interviewId]);
                    $alterListIds = array_unique($alterListIds);
                    $alter->alterListId =  implode(",", $alterListIds);
                    $alter->save();
                }
                $interview->delete();
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
