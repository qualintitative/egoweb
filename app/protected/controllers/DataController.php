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
            $multiStudyIds = $id;
            $all_studies = false;
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

        $egoid_answers = array();
        foreach ($result as $answer) {
            if ($answer->answerType == "RANDOM_NUMBER" || $answer->answerType == "STORED_VALUE")
                continue;
            if (!isset($egoid_answers[$answer->interviewId]))
                $egoid_answers[$answer->interviewId] = [];
            $egoid_answers[$answer->interviewId][] = $answer->value;
        }


        $egoIds = [];
        foreach ($interviews as $interview) {
            $alters[$interview->id] = 0;
            if (!isset($egoid_answers[$interview->id]))
                $egoid_answers[$interview->id] = ["error"];
            $egoIds[$interview->id] = implode("_", $egoid_answers[$interview->id]);
            $allInterviewIds[] = $interview->id;
            $interviewStudyIds[$interview->id] = $interview->studyId;
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
        return $this->render('index', ['study' => $study, 'all_studies' => $all_studies, 'interviewStudyIds' => $interviewStudyIds, 'multiStudyIds' => $multiStudyIds, 'expressions' => $expressions, 'interviews' => $interviews, 'alters' => $alters, 'egoIds' => $egoIds]);
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
            $interview->exportEgoAlterData($file, $withAlters, $multiSesh);
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

        $multiSesh = true;
        if (isset($_POST['multiSession'])) {
            $multiSesh = boolval($_POST['multiSession']);
        }

        $headers = array();

        $study = Study::findOne($_POST['studyId']);
        if ($study->multiSessionEgoId) {
            $multiQs = $study->multiIdQs();
            foreach ($multiQs as $q) {
                $studyIds[] = $q->studyId;
            }
        } else {
            $studyIds = $study->id;
        }

        $indents = [];
        // fetch questions
        foreach ($studyIds as $studyId) {
            $hCount = 0;
            $all_questions = Question::find()->where(["studyId" => $studyId])->orderBy(["ordering" => "ASC"])->all();
            $ego_id_questions = [];
            $ego_questions = [];
            $alter_questions = [];
            $network_questions = [];
            $name_gen_questions = [];
            $previous_questions = [];
            foreach ($all_questions as $question) {
                if ($question->subjectType == "EGO_ID") {
                    $ego_id_questions[] = $question;
                }
                if ($question->subjectType == "EGO") {
                    $ego_questions[] = $question;
                }
                if ($question->subjectType == "ALTER") {
                    $alter_questions[] = $question;
                }
                if ($question->subjectType == "NETWORK") {
                    $network_questions[] = $question;
                }
                if ($question->subjectType == "NAME_GENERATOR") {
                    $name_gen_questions[] = $question;
                }
                if ($question->subjectType == "PREVIOUS_ALTER") {
                    $previous_questions[] = $question;
                }
            }
            $headers[] = 'Interview ID';
            $headers[] = "EgoID";
            $headers[] = 'Start Time';
            $headers[] = 'End Time';
    
            foreach ($ego_id_questions as $question) {
                $headers[] = $question->title;
                $hCount++;
            }
            foreach ($ego_questions as $question) {
                $headers[] = $question->title;
                $hCount++;
            }
            foreach ($network_questions as $question) {
                $headers[] = $question->title;
                $hCount++;
            }

            if (isset($_POST[$studyId . '_expressionId']) && $_POST[$studyId . '_expressionId'] != "") {
                $headers[] = "Density";
                $headers[] = "Max Degree Value";
                $headers[] = "Max Betweenness Value";
                $headers[] = "Max Eigenvector Value";
                $headers[] = "Degree Centralization";
                $headers[] = "Betweenness Centralization";
                $headers[] = "Components";
                $headers[] = "Dyads";
                $headers[] = "Isolates";
                $hCount += 9;
            }
            $matchAtAll = MatchedAlters::find()->where(["studyId" => $studyId])->one();

            if ($matchAtAll) {
                $headers[] = "Dyad Match ID";
                $headers[] = "Match User";
                $headers[] = "Alter Number";
                $hCount += 3;
                if ($withAlters) {
                    $headers[] = "Alter Name";
                    $headers[] = "Matched Alter Name";
                    $hCount += 2;
                }
                $headers[] = "Alter Pair ID";
                $hCount++;
            } else {
                $headers[] = "Alter Number";
                $hCount++;
                if ($withAlters) {
                    $headers[] = "Alter Name";
                    $hCount++;
                }
            }
            foreach ($name_gen_questions as $question) {
                $headers[] = $question->title;
                $hCount++;
            }
            foreach ($previous_questions as $question) {
                $headers[] = $question->title;
                $hCount++;
            }
            foreach ($alter_questions as $question) {
                $headers[] = $question->title;
                $hCount++;
            }
            if (isset($_POST[$studyId . '_expressionId']) && $_POST[$studyId . '_expressionId'] != "") {
                $headers[] = "Degree";
                $headers[] = "Betweenness";
                $headers[] = "Eigenvector";
                $hCount += 3;
            }

            if ($multiSesh && $study->multiSessionEgoId) {
                $headers[] = 'Alter ID';
                $hCount++;
                $multiQs = $study->multiIdQs();
                foreach ($multiQs as $q) {
                    $s = Study::findOne($studyId);
                    $headers[] = $s->name;
                    $hCount++;
                }
            }
            $indents[$studyId] = $hCount;
        }

        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);
        $interviews = Interview::findAll(["id" => $interviewIds]);


        $text = implode(',', $headers) . "\n";
        foreach ($interviews as $interview) {
            $filePath = getcwd() . "/assets/" . $interview->studyId . "/" . $interview->id . "-ego-alter.csv";
            if (file_exists($filePath)) {
                if (array_search($interview->studyId, $studyIds) > 0) {
                    // indent file output
                    $rows = explode("\n", file_get_contents($filePath));
                    foreach ($rows as $row) {
                        $cols = explode(",", $row);
                        $line = [];
                        foreach ($cols as $index => $col) {
                            $line[] = $col;
                            /*
                            if ($index == 3) {
                                for ($i = 0; $i < array_search($interview->studyId, $studyIds); $i++) {
                                    for ($j = 0; $j < $indents[$studyIds[$i]]; $j++) {
                                        $line[] = "";
                                    }
                                }
                            }*/
                        }
                        if ($row != "")
                            $text .= implode(",", $line) . "\n";
                    }
                    unlink($filePath);
                } else {
                    $text .= file_get_contents($filePath);
                    unlink($filePath);
                }
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-ego-alter.csv')->send();
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

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $withAlters = false;
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-ego-level.csv", "w") or die("Unable to open file!");
            $interview->exportEgoLevel($file);
            return $this->renderAjax("/layouts/ajax", ["json" => "success"]);
        }
        return $this->renderAjax("/layouts/ajax", ["json" => "fail"]);
    }

    public function actionExportegolevelall()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $study = Study::findOne($_POST['studyId']);
        $optionsRaw = QuestionOption::findAll(["studyId" => $study->id]);

        // create an array with option ID as key
        $options = array();
        foreach ($optionsRaw as $option) {
            $options[$option->id] = $option->value;
        }

        // fetch questions
        $all_questions = Question::find()->where(["studyId" => $_POST['studyId']])->orderBy(["ordering" => "ASC"])->all();
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        foreach ($all_questions as $question) {
            if ($question->subjectType == "EGO_ID") {
                $ego_id_questions[] = $question;
            }
            if ($question->subjectType == "EGO") {
                $ego_questions[] = $question;
            }
            if ($question->subjectType == "ALTER") {
                $alter_questions[] = $question;
            }
            if ($question->subjectType == "NETWORK") {
                $network_questions[] = $question;
            }
            if ($question->subjectType == "NAME_GENERATOR") {
                $name_gen_questions[] = $question;
            }
        }

        $headers = array();
        $headers[] = 'Interview ID';
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

        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);

        $text = implode(',', $headers) . "\n";
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/" . $interviewId . "-ego-level.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-ego-level.csv')->send();
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
        if (isset($_POST['withAlters'])) {
            $withAlters = boolval($_POST['withAlters']);
        }

        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $file = fopen($filePath . "/" . $_POST['interviewId'] . "-alter-pair.csv", "w") or die("Unable to open file!");
            $interview->exportAlterPairData($file, $study, $withAlters);
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

        $study = Study::findOne($_POST['studyId']);
        $multiStudyIds = $study->multiStudyIds();

        $alter_pair_questions = [];
        $studyNames = [];
        foreach ($multiStudyIds as $studyId) {
            $study = Study::findOne($studyId);
            $studyNames[$studyId] = $study->name;
            $alter_pair_questions[$studyId] = Question::findAll(["studyId" => $studyId, "subjectType" => "ALTER_PAIR"]);
        }

        $headers = array();
        foreach ($multiStudyIds as $studyId) {
            $headers[] = $studyNames[$studyId] . ' Interview ID';
            $headers[] = $studyNames[$studyId] . ' EgoID';
        }
        $headers[] = "Alter 1 Number";
        if ($withAlters) {
            $headers[] = "Alter 1 Name";
        }
        $headers[] = "Alter 2 Number";
        if ($withAlters) {
            $headers[] = "Alter 2 Name";
        }
        foreach ($multiStudyIds as $studyId) {
            foreach ($alter_pair_questions[$studyId] as $question) {
                $headers[] = $question->title;
            }
        }

        $interviewIds = array();
        $interviewIds = explode(",", $_POST['interviewIds']);

        $text = implode(',', $headers) . "\n";
        foreach ($interviewIds as $interviewId) {
            $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/" . $interviewId . "-alter-pair.csv";
            if (file_exists($filePath)) {
                $text .= file_get_contents($filePath);
                unlink($filePath);
            }
        }
        return $this->response->sendContentAsFile($text, $study->name . '-alter-pair.csv')->send();
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
        return $this->response->sendContentAsFile($text, $study->name . '-other-specify.csv')->send();
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
        $fields = ["Question Order", "Question Title", "Question Prompt", "Stem and Leaf", "Subject Type", "Response Type", "Min", "Max", "Options", "Skip Logic Expression"];
        $rows[] = implode(",", $fields);
        $results = Expression::find()->where(["studyId" => $id])->all();
        $expressions;
        foreach ($results as $expression) {
            $expressions[$expression->id] = $expression->name;
        }
        $questions = Question::find()->where(["studyId" => $id])->orderBy(["ordering" => "ASC"])->all();
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
            $question->citation = str_replace('’', "'", $question->citation);
            $question->citation = preg_replace('/[[:^print:]]/', " ", $question->citation);
            $fields = [];
            $fields[] = $question->ordering + 1 + ($question->subjectType == "EGO_ID" ? 0 : $egoIdCount);
            $fields[] = $question->title;
            $fields[] = '"' . strip_tags(str_replace('"', "'", $question->prompt)) . '"';
            $fields[] = '"' . strip_tags(str_replace('"', "'", $question->citation)) . '"';
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
                        $alter->save();
                    } else {
                        $alter->delete();
                    }
                }
                $interview->delete();
            }
        }
        return $this->redirect(Yii::$app->request->referrer);
    }
}
