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
use app\models\Alters;
use app\models\Question;
use app\models\QuestionOption;
use app\models\Expression;
use app\models\AlterList;
use app\models\AlterPrompt;
use app\models\Interview;
use app\models\Graph;
use app\models\Note;

use app\models\Answer;

use yii\helpers\Url;

/**
 * Interview controller
 */
class InterviewController extends Controller
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
                        'actions' => ['view', 'save', 'alter', 'deletealter', 'graph'],
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
                    'save' => ['post'],
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
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * This is the main page.  Fetches the study, questions, responses, and other relevant data and displays page.
     * /interview/{studyId}/{interviewId}
     */
    public function actionView($studyId, $interviewId = null)
    {
        // check for IPSOS / KnowledgePanel API integration
        if ($studyId == 0 && isset($_GET["study"])) {
            $study = Study::findOne(["name"=>$_GET["study"]]);
            $q = Question::find()->where(array("subjectType"=>"EGO_ID", "studyId"=>$study->id))->orderBy(['ordering'=>'ASC'])->one();
            if (isset($_GET[$q->title])) {
                $answers = Answer::find()->where(array("studyId"=>$study->id, "questionId"=>$q->id))->all();
                foreach ($answers as $a) {
                    if ($a->value == $_GET[$q->title]) {
                        $interview = Interview::findOne($a->interviewId);
                        if($interview){
                            $page = 0;
                            if ($interview->completed && $study->active == 1)
                                $page = $interview->completed;
                        }
                    }
                }
            }
            if (isset($_GET['redirect_url'])) {
                Yii::$app->session->set('redirect', $_GET['redirect_url']);
            }
            if (!isset($interview)) {
                $interview = new Interview;
                $interview->studyId = $study->id;
                $page = 1;
                $answers = [];
                if ($interview->save()) {
                    $interviewId = $interview->id;
                    $egoQs = Question::find()->where(array("subjectType"=>"EGO_ID", "studyId"=>$study->id))->all();
                    foreach ($egoQs as $q) {
                        if ($q->answerType != "RANDOM_NUMBER" && !isset($_GET[$q->title])) {
                            continue;
                        }
                        $a = $q->id;
                        $answers[$a] = new Answer;
                        $answers[$a]->interviewId = $interview->id;
                        $answers[$a]->studyId = $study->id;
                        $answers[$a]->questionType = "EGO_ID";
                        $answers[$a]->answerType = $q->answerType;
                        $answers[$a]->questionId = $q->id;
                        $answers[$a]->skipReason = "NONE";
                        if ($q->answerType == "RANDOM_NUMBER") {
                            $answers[$a]->value = mt_rand($q->minLiteral, $q->maxLiteral);
                        } else {
                            $answers[$a]->value = $_GET[$q->title];
                        }
                        $answers[$a]->save();
                    }
                }
            }
            $this->redirect("/interview/".$study->id."/". $interview->id . "#/page/" . $page . "/");
        }


        // load study along with attached multi session studies
        if(!isset($study))
            $study = Study::findOne($studyId);
        $this->view->title = $study->name;
        $multiStudyIds = $study->multiStudyIds();


        // load questions
        $audio = array();
        $questions = array();
        $questionList = array();
        $network_questions = array();
        $autocompleteList = false;
        $interview = false;
        $prevAlters = array();
        $otherGraphs = array();
        if($interviewId != null){
            $interview = Interview::findOne($interviewId);
            $interviewIds = $interview->multiInterviewIds();
            $prevIds = array_diff($interviewIds, array($interviewId));
            $prevAlterObjs = [];
            if (count($prevIds) > 0) {
                foreach ($prevIds as $i_id) {

                    // load previous alters
                    $results = Alters::find()
                    ->where(new \yii\db\Expression("FIND_IN_SET(" . $i_id .", interviewId)"))
                    ->all();
                    foreach ($results as $result) {
                        $prevAlters[$result->id] = Tools::mToA($result);
                        $prevAlterObjs[$result->id] = $result;
                    }

                    // load previous graphs
                    foreach ($network_questions as $nq) {
                        if (!isset($otherGraphs[$nq['TITLE']])) {
                            $otherGraphs[$nq['TITLE']] = array();
                        }
                        $oldInterview = Interview::findOne($i_id);
                        $graph = "";
                        $oldStudy = Study::findOne($oldInterview->studyId);
                        $question = Question::findOne(["title"=>$nq['TITLE'], "studyId"=>$oldStudy->id]);
                        if(!$question)
                            continue;
                        $networkExprId = $question->networkRelationshipExprId;
                        if ($networkExprId) {
                            $graph = Graph::findOne(["expressionId"=>$networkExprId, "interviewId"=>$i_id]);
                        }
                        if ($graph) {
                            $otherGraphs[$nq['TITLE']][] = array(
                                "id" => $graph->id,
                                "interviewId" => $i_id,
                                "expressionId" => $networkExprId,
                                "studyName" => $oldStudy->name,
                                "questionId" => $question->id,
                                "params"=> $question->networkParams,
                            );
                        }
                    }
                }
            }
        }
        $results = Question::find()->where(["studyId"=>$multiStudyIds])->orderBy(["ordering"=>"ASC"])->all();
        foreach ($results as $result) {
            $questions[$result->id] = Tools::mToA($result);
            if ($study->id == $result->studyId) {
                if ($result->subjectType == "EGO_ID") {
                    $ego_id_questions[] = Tools::mToA($result);
                }else{
                    $questionList[] = Tools::mToA($result);
                }
                if ($result->subjectType == "NAME_GENERATOR" && $interviewId) {
                    if ($result->autocompleteList) {
                        $autocompleteList = true;
                    }
                    // pre-fill alter list if exists
                    if ($result->prefillList) {
                        $check = Alters::find()->where(["interviewId"=>$interviewId])->all();
                        if (count($check) == 0) {
                            $alterList = AlterList::find()->where(["studyId"=>$study->id])->all();
                            $count = 0;
                            foreach ($alterList as $a) {
                                $alter = new Alters;
                                $ordering = [$result->id => $count];
                                $alter->ordering = json_encode($ordering);
                                $alter->interviewId = $_GET['interviewId'];
                                $alter->name = $a->name;
                                $alter->nameGenQIds = $a->nameGenQIds;
                                $alter->save();
                                $count++;
                            }
                        }
                    }
                    if ($result->prefillPrev) {
                        $check = Alters::find()
                        ->where(new \yii\db\Expression("FIND_IN_SET(:interviewId, interviewId)"))
                        ->addParams([':interviewId' => $interviewId])
                        ->all();
                        if (count($check) == 0) {
                            $count = 0;
                            foreach ($prevAlterObjs as $a) {
                                $nameGenQIds = explode(",", $a->nameGenQIds);
                                $nameGenQIds[] = $result->id;
                                $a->nameGenQIds = implode(",", $nameGenQIds);
                                $interviewIds = explode(",", $a->interviewId);
                                $interviewIds[] = $interviewId;
                                $a->interviewId = implode(",", $interviewIds);
                                if (!is_numeric($a->ordering)) {
                                    $ordering = json_decode($a->ordering, true);
                                }else{
                                    $ordering = [];
                                }
                                $ordering[$result->id] = $count;
                                $a->ordering = json_encode($ordering);
                                $a->save();
                                $count++;
                            }
                            $prevAlters = [];
                        }
                    }
                }
                if ($result->subjectType == "NETWORK") {
                    $network_questions[] = Tools::mToA($result);
                }
            }
        }


        // load options
        $options = array();
        $results = QuestionOption::find()->where(array("studyId"=>$multiStudyIds))->all();
        foreach ($results as $result) {
            $options[$result->questionId][$result->ordering] = Tools::mToA($result);
        }


        // load expressions
        $expressions = array();
        $results = Expression::find()->where(array("studyId"=>$multiStudyIds))->all();
        foreach ($results as $result) {
            $expressions[$result->id] = Tools::mToA($result);
        }


        // load ego id answers
        $ego_id_answers = array();
        $results = Answer::findAll(array("studyId"=>$study->id, "questionType"=>"EGO_ID"));
        foreach ($results as $a) {
            $ego_id_answers[] = $a->value;
        }

        // load participant list
        $participantList = array();
        $results = AlterList::findAll(array("studyId"=>$study->id));
        if (count($results) > 0) {
            foreach ($results as $result) {
                if ($autocompleteList == false && (!$result->interviewerId || (Yii::$app->user->identity != null && (Yii::$app->user->identity->isSuperAdmin() || $result->interviewerId == Yii::$app->user->identity->id)))) {
                    if (!in_array($result->name, $ego_id_answers) && !in_array($result->email, $ego_id_answers)) {
                        $participantList[] = Tools::mToA($result);
                    }
                } elseif ($autocompleteList == true) {
                    $participantList[] = Tools::mToA($result);
                }
            }
        }

        // load alter prompts
        $alterPrompts = array();
        $results = AlterPrompt::findAll(array("studyId"=>$study->id));
        foreach ($results as $result) {
            if (!$result->questionId) {
                $result->questionId = 0;
            }
            $alterPrompts[$result->questionId][$result->afterAltersEntered] = $result->display;
        }


        // load remaining data
        $answers = array();
        $alters = array();
        $graphs = array();
        $notes = array();
        if ($interview) {
            if($interview && $interview->completed == -1 && Yii::$app->user->isGuest){
                return $this->response->redirect(Url::toRoute('/admin'));
            }
            


            // load answers
            $answers = $interview->getAnswers(true);


            // load alters for current interview
            $results = Alters::find()
            ->where(new \yii\db\Expression("FIND_IN_SET(:interviewId, interviewId)"))
            ->addParams([':interviewId' => $interviewId])
            ->all();
            foreach ($results as $result) {
                if (isset($prevAlters[$result->id])) {
                    unset($prevAlters[$result->id]);
                }
                $alters[$result->id] = Tools::mToA($result);
            }


            // load graphs
            $results = Graph::find()->where(array('interviewId'=>$interviewId))->all();
            foreach ($results as $result) {
                $graphs[$result->expressionId] = Tools::mToA($result);
            }


            // load notes
            $results = Note::find()->where(array("interviewId"=>$interviewId))->all();
            foreach ($results as $result) {
                $notes[$result->expressionId][$result->alterId] = $result->notes;
            }
        }


        // generate view with loaded data
        return $this->render(
            'view',
            array(
                "study"=>json_encode(Tools::mToA($study)),
                "ego_id_string"=>($interview ? $interview->egoid : ""),
                "questions"=>json_encode($questions),
                "questionList"=>json_encode($questionList),
                "questionTitles"=>json_encode($study->questionTitles()),
                "ego_id_questions"=>json_encode($ego_id_questions),
                "options"=>json_encode($options),
                "expressions"=>json_encode($expressions),
                "participantList"=> (count($participantList) != 0 ? json_encode($participantList) : "[]"),
                "alterPrompts"=>json_encode($alterPrompts),
                "interviewId" => $interviewId,
                "interview" => json_encode($interview ? Tools::mToA($interview) : false),
                "prevAlters"=>(count($prevAlters) != 0 ? json_encode($prevAlters) : "{}"),
                "otherGraphs"=>json_encode($otherGraphs),
                "answers"=>json_encode($answers),
                "alters"=>(count($alters) != 0 ? json_encode($alters) : "{}"),
                "graphs"=>json_encode($graphs),
                "allNotes"=>json_encode($notes),
                "audio"=>json_encode($audio),
            )
        );
    }


    /**
     * Saves response data after each interview page (cliking on Next)
     * /interview/save
     */
    public function actionSave()
    {
        $errorMsg = "";
        if (isset($_POST["studyId"]))
            $study = Study::findOne($_POST["studyId"]);
        else
            $errorMsg = "Missing study ID";
        $interview = null;
        $firstAnswer = $_POST['Answer'][array_keys($_POST['Answer'])[0]];
        if($firstAnswer['interviewId'])
            $interview = Interview::findOne($firstAnswer['interviewId']);

        
        // verify guest user with hash key / create new interview
        if ($firstAnswer['questionType'] == "EGO_ID" && isset($firstAnswer['value']) && $firstAnswer['value'] != "" && !$interview) {
            $hashKey = "";
            if (isset($_POST["hashKey"])) 
                $hashKey = $_POST["hashKey"];
            $interview = $this->initiateInterview($_POST['Answer'], $hashKey);

            if(is_string($interview)){
                $errorMsg = $interview;
                $interview = null;
            }
        }


        // save submited answers
        if($interview)
            $answers = $interview->answers;
        foreach ($_POST['Answer'] as $Answer) {
            if (!isset($Answer['questionType'])){
                $_POST['page'] = intval($_POST['page']) + 1;
                continue;
            }

            if ($Answer['questionType'] == "ALTER" || $Answer['questionType'] == "PREVIOUS_ALTER") {
                $array_id = $Answer['questionId'] . "-" . $Answer['alterId1'];
            } elseif ($Answer['questionType'] == "ALTER_PAIR") {
                $array_id = $Answer['questionId'] . "-" . $Answer['alterId1'] . "and" . $Answer['alterId2'];
            } else {
                $array_id = $Answer['questionId'];
            }

            if (!isset($answers[$array_id]))
                $answers[$array_id] = new Answer;

            $answers[$array_id]->attributes = $Answer;
            if ($interview) {
                $answers[$array_id]->interviewId = $interview->id;
                if ($Answer['questionType'] == "MERGE_ALTER") {
                    // handle the "merge alter" question type separately
                    $this->mergeAlters($Answer, $interview->id);
                    continue;
                } else {
                    if ($answers[$array_id]->save()) {
                        if($answers[$array_id]->value)
                            $answers[$array_id]->value = Tools::decrypt($answers[$array_id]->value);
                        if($answers[$array_id]->otherSpecifyText)
                            $answers[$array_id]->otherSpecifyText = Tools::decrypt($answers[$array_id]->otherSpecifyText);
                    } else {
                        print_r($answers[$array_id]->errors);
                        die();
                    }
                }
            }
        }
        foreach ($answers as $index => $answer) {
            $json["answers"][$index] = Tools::mToA($answer);
        }


        // update interview object
        if ($interview) {
            if ($interview->completed != -1 && is_numeric($_POST['page'])) {
                $interview->completed = (int)$_POST['page'];
                $interview->save();
            }
            $json["interview"] = Tools::mToA($interview);
            if (isset($_POST['conclusion'])) {
                $interview->completed = -1;
                $interview->complete_date = time();
                $interview->save();
                if (isset(Yii::$app->params['exportFilePath']) && Yii::$app->params['exportFilePath']) {
                    $this->exportInterview($interview->id);
                }
            }
        }


        // return json
        if (!$errorMsg)
            $json = json_encode($json);
        else
            $json = "{\"error\":\"$errorMsg\"}";
        return $this->renderAjax("/layouts/ajax", ['json'=>$json]);
    }

    // initate interview from answer data
    private function initiateInterview($Answers, $hashKey = "")
    {
        $study = false;
        $keystr = false;
        foreach ($Answers as $ego_id) {
            if(!$study)
                $study = Study::findOne($ego_id['studyId']);
            $Answer = $ego_id;
            $ego_id_q = Question::findOne($ego_id['questionId']);
            if (in_array($ego_id_q->useAlterListField, array("name", "email"))) {
                $keystr = $ego_id['value'];
                break;
            }
        }
        if (!$keystr)
            $ego_id_q = false;

        if ($ego_id_q && !$hashKey){
            $checkIntId = 0;
            if (!Yii::$app->user->isGuest)
                $checkIntId = Yii::$app->user->identity->id;
            $participantList = AlterList::findAll(array("studyId"=>$study->id, "interviewerId"=>array(0, $checkIntId)));     
            $ego_id_a = Answer::findAll(array("studyId"=>$study->id, "questionType"=>"EGO_ID"));
            $ego_id_answers = array();
            foreach ($ego_id_a as $a) {
                if ($a->questionId == $ego_id_q->id) {
                    $ego_id_answers[] = $a->value;
                }
            }
            if (count($participantList) == 0 && !Yii::$app->user->isGuest) {
                return "$keystr is either not in the participant list or has been assigned to another interviewer";
            } else {
                $check = false;
                $prop = $ego_id_q->useAlterListField;
                foreach ($participantList as $participant) {
                    if (in_array($keystr, $ego_id_answers)) {
                        return "$keystr has already been used in an interview";
                    }
                    if ((($participant->name == $keystr && $prop == "name") || ($participant->email == $keystr && $prop == "email")) && !in_array($keystr, $ego_id_answers)) {
                        $check = true;
                    }
                }
                if (Yii::$app->user->isGuest && ($prop == "name" || $prop == "email")) {
                    $check = true;
                }
                if ($check == false) {
                    return "$keystr is either not in the participant list or has been assigned to another interviewer";
                }
            }
        }

        if (Yii::$app->user->isGuest) {
            if ($hashKey != "") {
                if (!$hashKey || ($hashKey && md5($keystr) != $hashKey)) {
                    return "Participant not found";
                }
            } else {
                if ($ego_id_q->restrictList == true || !$ego_id_q->useAlterListField) {
                    return "Participant not found";
                }
            }
        }

        if ($keystr) {
            $interview = Interview::getInterviewFromEmail($Answer['studyId'], $keystr);
            if (!$interview) {
                $interview = new Interview;
                $interview->studyId = $Answer['studyId'];
            } else {
                if (!Yii::$app->user->isGuest) {
                    return "Participant already in existing interview";
                }
            }
        } else {
            $interview = new Interview;
            $interview->studyId = $Answer['studyId'];
        }

        if (!$interview->id && $interview->save()) {
            $randoms = Question::findAll(array("answerType"=>"RANDOM_NUMBER", "studyId"=>$Answer['studyId']));
            foreach ($randoms as $q) {
                $a = $q->id;
                $answers[$a] = new Answer;
                $answers[$a]->interviewId = $interview->id;
                $answers[$a]->studyId = $Answer['studyId'];
                $answers[$a]->questionType = "EGO_ID";
                $answers[$a]->answerType = "RANDOM_NUMBER";
                $answers[$a]->questionId = $q->id;
                $answers[$a]->skipReason = "NONE";
                $answers[$a]->value = mt_rand($q->minLiteral, $q->maxLiteral);
                $answers[$a]->save();
            }
        } else {
            print_r($interview->errors);
            die();
        }
        return $interview;
    }

    // merge alter question
    private function mergeAlters($Answer, $interviewId)
    {
        $prevAlter = Alters::findOne($Answer['alterId2']);
        $alter = Alters::findOne($Answer['alterId1']);
        if ($Answer['value'] == "MATCH") {
            $intIds = explode(",", $prevAlter->interviewId);
            $intIds = array_unique($intIds);
            $intIds = array_filter($intIds, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $prevNameQIds = explode(",", $prevAlter->nameGenQIds);
            $prevNameQIds = array_unique($prevNameQIds);
            $nameQIds = explode(",", $alter->nameGenQIds);
            if (stristr($prevAlter->ordering, "{")) {
                $prevOrdering = json_decode($prevAlter->ordering, true);
            } else {
                $prevOrdering = array();
                foreach ($intIds as $intId) {
                    $results = Alters::find()
                    ->where(new \yii\db\Expression("FIND_IN_SET(" . $intId .", interviewId)"))
                    ->all();
                    foreach ($results as $index=>$result) {
                        if ($result->name == $prevAlter->name) {
                            $rNameQIds = explode(",", $result->nameGenQIds);
                            $rNameQIds = array_unique($rNameQIds);
                            $prevOrdering[implode(",", $rNameQIds)] = $index;
                        }
                    }
                }
            }
            $ordering = json_decode($alter->ordering, true);
            $alterListIds = explode(",", $prevAlter->alterListId);
            $alterListIds = array_filter($alterListIds, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $alterListIds[] = $interviewId;
            $alterListIds = array_unique($alterListIds);
            $prevAlter->alterListId =  implode(",", $alterListIds);
            $alterListIds[] = $interviewId;

            if (!in_array($alter->interviewId, $intIds)) {
                $intIds[] = $alter->interviewId;
            }
            $prevAlter->interviewId = implode(",", $intIds);
            foreach ($nameQIds as $unQId) {
                if (!in_array($unQId, $prevNameQIds) && isset($ordering[$unQId])) {
                    $prevNameQIds[] = $unQId;
                    $prevOrdering[$unQId] = $ordering[$unQId];
                }
            }
            $prevAlter->ordering = json_encode($prevOrdering);
            $prevAlter->nameGenQIds = implode(",", $prevNameQIds);
            $prevAlter->save();
            if ($alter) {
                $alter->delete();
            }
        } else {
            if (strtolower($alter->name) == strtolower($prevAlter->name)) {
                if ($Answer['value'] == "NEW_NAME") {
                    $alter->name = str_replace("NEW_NAME:", "", $Answer['otherSpecifyText']);
                } else {
                    $alter->name = str_replace("UNMATCH:", "", $Answer['otherSpecifyText']);
                }
                if ($alter->name != "" && strtolower($alter->name) != strtolower($prevAlter->name)) {
                    $alterListIds = explode(",", $alter->alterListId);
                    $alterListIds = array_filter($alterListIds, function ($value) {
                        return !is_null($value) && $value !== '';
                    });
                    if (!$alterListIds) {
                        $alterListIds = array();
                    }
                    $alterListIds[] = $interviewId;
                    $alterListIds = array_unique($alterListIds);
                    $alter->alterListId =  implode(",", $alterListIds);
                    $alter->save();
                } else {
                    echo "{\"error\":\"Please modify the name so it's not identical to the previous name entered.\"}";
                    die();
                }
            } else {
                if (!isset($alterListIds)) {
                    $alterListIds = array();
                }
                if ($Answer['value'] == "NEW_NAME") {
                    $alterListIds = explode(",", $alter->alterListId);
                    $alterListIds = array_filter($alterListIds, function ($value) {
                        return !is_null($value) && $value !== '';
                    });
                    $alterListIds[] = $interviewId;
                    $alterListIds = array_unique($alterListIds);
                    $alter->alterListId =  implode(",", $alterListIds);
                    $alter->save();
                } else {
                    $alterListIds = explode(",", $prevAlter->alterListId);
                    $alterListIds = array_filter($alterListIds, function ($value) {
                        return !is_null($value) && $value !== '';
                    });
                    $alterListIds[] = $alter->id;
                    $alterListIds = array_unique($alterListIds);
                    $prevAlter->alterListId = implode(",", $alterListIds);
                    $prevAlter->save();
                }
            }
        }
    }


    /**
     * Saves new alter to database
     * /interview/alter
     */
    public function actionAlter()
    {
        if (isset($_POST['Alters'])) {
            $interview = Interview::findOne($_POST['Alters']['interviewId']);
            $studyId = $interview->studyId;
            $nameGenQ = Question::findOne($_POST['Alters']['nameGenQIds']);
            $alters = json_decode($_POST['currentAlters'], true);
            $prevAlters = json_decode($_POST['prevAlters'], true);
            $newAlterId = false;
            $alterNames = array();
            $alterGroups = array();
            $prev_names = array();
            $preset_names = array();
            foreach ($alters as $alter) {
                $alterNames[$alter['ID']] = strtolower($alter['NAME']);
                $alterGroups[$alter['NAME']] = explode(",", $alter['NAMEGENQIDS']);
            }
            if($nameGenQ->restrictPrev == true){
                foreach ($prevAlters as $alter) {
                    //$alterNames[$alter['ID']] = strtolower($alter['NAME']);
                    //$alterGroups[$alter['NAME']] = explode(",", $alter['NAMEGENQIDS']);
                    $prev_names[] = $alter['NAME'];
                }
            //  if($nameGenQ->restrictPrev == true)
            //      $restrictList = false;
            }
            $model = new Alters;
            $model->attributes = $_POST['Alters'];
            $ordering = array($_POST['Alters']['nameGenQIds'] => intval($_POST['Alters']['ordering']));
            if (in_array(strtolower($_POST['Alters']['name']), $alterNames)) {
                if (!in_array($_POST['Alters']['nameGenQIds'], $alterGroups[$_POST['Alters']['name']])) {
                    $model = Alters::findOne(array_search(strtolower($_POST['Alters']['name']), $alterNames));
                    $newAlterId = $model->id;
                    $alterGroups[$_POST['Alters']['name']][] = $_POST['Alters']['nameGenQIds'];
                    $model->nameGenQIds = implode(",", $alterGroups[$_POST['Alters']['name']]);
                    if($model->interviewId != $_POST['Alters']['interviewId']){
                        $interviewIds = explode(",", $model->interviewId);
                        if(!in_array($_POST['Alters']['interviewId'], $interviewIds)){
                            $interviewIds[] = $_POST['Alters']['interviewId'];
                            $model->interviewId = implode(",", $interviewIds);
                        }
                    }
                    if (!is_numeric($model->ordering)) {
                        $ordering = json_decode($model->ordering, true);
                        $ordering[$_POST['Alters']['nameGenQIds']] = intval($_POST['Alters']['ordering']);
                    } else {
                        $ordering = array();
                        $ordering[$_POST['Alters']['nameGenQIds']] = intval($_POST['Alters']['ordering']);
                    }
                } else {
                    $model->addError('name', $_POST['Alters']['name']. ' has already been added!');
                   // echo "name already in list:" . $_POST['Alters']['nameGenQIds'];
                    //print_r($alterGroups[$_POST['Alters']['name']]);
                   // die();
                }
            }

            $preset_alters = AlterList::findAll(array("studyId"=>$studyId));
            foreach ($preset_alters as $alter) {
                $preset_names[] = $alter->name;
            }
            //$study = Study::findOne($studyId);
            /*
            $restrictList = false;
            $results = Question::find()->where(array("studyId"=>$studyId, "subjectType"=>"NAME_GENERATOR"))->orderBy(['ordering'=>'ASC'])->all();
            foreach ($results as $result) {
                if ($result->restrictList == true) {
                    $restrictList = true;
                }
            }*/
            // check to see if pre-defined alters exist.  If they do exist, check name against list
            if ($nameGenQ->restrictPrev) {
                if (count($prev_names) > 0) {
                    if (!in_array($_POST['Alters']['name'], $prev_names)) {
                        $model->addError('name', $_POST['Alters']['name']. ' is not in our list of previous alters');
                    }
                }
            }

            if ($nameGenQ->restrictList) {
                if (count($preset_names) > 0) {
                    if (!in_array($_POST['Alters']['name'], $preset_names)) {
                        $model->addError('name', $_POST['Alters']['name']. ' is not in our list of previous alters');
                    }
                }
            }

            $foundAlter = false;

            $model->ordering = json_encode($ordering);
            if (!isset($model->errors['name']) && $foundAlter == false) {
                if (!$model->save()) {
                    print_r($model->errors);
                    die();
                } else {
                    if($newAlterId == false)
                        $newAlterId = Yii::$app->db->getLastInsertID();
                    $result = Alters::findOne($newAlterId);
                    $model->id = $newAlterId;
                    $model->name = Tools::decrypt($model->name);
                    $alters[$newAlterId] = Tools::mToA($model);
                    $json = json_encode($alters);
                    return $this->renderAjax("/layouts/ajax", ["json"=>$json]);
                }
            }
        }
    }

    /**
     * Deletes alter
     * /interview/deletealter
     */
    public function actionDeletealter()
    {
        if (isset($_POST['Alters'])) {
            $model = Alters::findOne($_POST['Alters']['id']);
            $interviewId = $_POST['Alters']['interviewId'];
            $nameQId = $_POST['Alters']['nameGenQId'];
            $interview = Interview::findOne($interviewId);
            $name_gen_questions = Question::findAll(array("studyId"=>$interview->studyId,"subjectType"=>"NAME_GENERATOR"));
            $nameQIds = array();
            foreach ($name_gen_questions as $question) {
                $nameQIds[] = $question->id;
            }
            if ($model) {
                //$nGorder = json_decode($model->ordering, true);
                //$model->ordering = json_encode($nGorder);
                if (strstr($model->interviewId, ",")) {
                    $nameGenQIds = explode(",", $model->nameGenQIds);
                    $checkRemain = false;
                    foreach ($nameGenQIds as $nameGenQId) {
                        if ($nameGenQId != $nameQId && in_array($nameGenQId, $nameQIds)) {
                            $checkRemain = true;
                        }
                    }
                    $nameGenQIds = array_diff($nameGenQIds, array($nameQId));
                    $model->nameGenQIds = implode(",", $nameGenQIds);
                    if ($checkRemain == false) {
                        $interviewIds = explode(",", $model->interviewId);
                        $interviewIds = array_diff($interviewIds, array($interviewId));
                        $model->interviewId = implode(",", $interviewIds);
                    }
                    $nGorder = json_decode($model->ordering, true);
                    if (!is_numeric($model->ordering)) {
                        if (isset($nGorder[$nameQId])) {
                            $ordering = $nGorder[$nameQId];
                            unset($nGorder[$nameQId]);
                        }
                        $model->ordering = json_encode($nGorder);
                    } else {
                        $ordering = $model->ordering;
                    }
                    $model->alterListId = '';
                    $model->save();
                } else {
                    if (strstr($model->nameGenQIds, ",")) {
                        $nameGenQIds = explode(",", $model->nameGenQIds);
                        $nameGenQIds = array_diff($nameGenQIds, array($nameQId));
                        $model->nameGenQIds = implode(",", $nameGenQIds);
                        $nGorder = json_decode($model->ordering, true);
                        if (!is_numeric($model->ordering)) {
                            if (isset($nGorder[$nameQId])) {
                                $ordering = $nGorder[$nameQId];
                                unset($nGorder[$nameQId]);
                            }
                            $model->ordering = json_encode($nGorder);
                        } else {
                            $ordering = $model->ordering;
                        }
                        $model->save();
                    } else {
                        $nGorder = json_decode($model->ordering, true);
                        if (!is_numeric($model->ordering)) {
                            if (isset($nGorder[$nameQId])) {
                                $ordering = $nGorder[$nameQId];
                                unset($nGorder[$nameQId]);
                            }
                        } else {
                            $ordering = $model->ordering;
                        }
                        $model->delete();
                    }
                }
                //if (is_numeric($ordering)) {
                //    Alters::sortOrder($ordering, $interviewId, $nameQId);
                //}
            }

            $alters = array();
            $results = Alters::find()
            ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId .", interviewId)"))
            ->all();
            $newOrdering = [];
            foreach ($results as $index=>$model) {
                if (is_numeric($model->ordering)) {
                    $nGorder = array($nameQId=>$index);
                    $model->ordering = json_encode($nGorder);
                    $model->save();
                }else{
                    $ordering = json_decode($model->ordering, true);
                    if(isset($ordering[$nameQId])){
                        $newOrdering[$ordering[$nameQId]] = $model;
                    }else{
                        $alters[$model->id] = Tools::mToA($model);
                    }
                }
            }
            ksort($newOrdering);
            $count = 0;
            foreach($newOrdering as $model){
                $ordering = json_decode($model->ordering, true);
                if(isset($ordering[$nameQId])){
                    if($ordering[$nameQId] != $count){
                        $ordering[$nameQId] = $count;
                        $model->ordering = json_encode($ordering);
                        $model->save();
                        $model->name = Tools::decrypt($model->name);
                    }
                    $count++;
                }
                $alters[$model->id] = Tools::mToA($model);
            }
            $json = json_encode($alters);
            return $this->renderAjax("/layouts/ajax", ["json"=>$json]);
        }
    }

    /**
     * Loads graph
     * /interview/graph/{interviewId}/{graphId}/{questionId}
     */
    public function actionGraph($interviewId, $graphId, $questionId)
    {
        $graph = false;
        $result = Graph::find()->where(array('interviewId'=>$interviewId, "id"=>$graphId))->one();
        $question = Tools::mToA(Question::findOne($questionId));
        if ($result) {
            $graphs = array($result->expressionId=>Tools::mToA($result));
        }
        $alters = array();
        $results = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(:interviewId, interviewId)"))
        ->addParams([':interviewId' => $interviewId])
        ->all();
        foreach ($results as $result) {
            if (isset($prevAlters[$result->id])) {
                unset($prevAlters[$result->id]);
            }
            $alters[$result->id] = Tools::mToA($result);
        }
        $interview = Interview::findOne($interviewId);
        $results = Question::find()->where(["studyId"=>$interview->studyId])->orderBy(["id"=>"ASC"])->all();
        $questions = [];
        foreach ($results as $result) {
            $questions[$result->id] = Tools::mToA($result);
        }
        $expressions = array();
        $results = Expression::find()->where(array("studyId"=>$interview->studyId))->all();
        foreach ($results as $result) {
            $expressions[$result->id] = Tools::mToA($result);
        }
        $answerList = Answer::findAll(array('interviewId'=>$interviewId));
        $answers = [];
        foreach ($answerList as $answer) {
            if ($answer->alterId1 && $answer->alterId2) {
                $array_id = $answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2;
            } elseif ($answer->alterId1 && ! $answer->alterId2) {
                $array_id = $answer->questionId . "-" . $answer->alterId1;
            } else {
                $array_id = $answer->questionId;
            }
            $answers[$array_id] = Tools::mToA($answer);
        }
        $study = Study::findOne($interview->studyId);
        $notes = Note::findAll(array("interviewId"=>$interviewId, "expressionId"=>$question['NETWORKRELATIONSHIPEXPRID']));
        return $this->renderAjax("graph", ["study"=>$study,"interview"=>$interview, "graphId"=>$graphId, "question"=>json_encode($question, true), "graphs"=>json_encode($graphs, true), "alters"=>$alters,"questions"=>json_encode($questions, true),"expressions"=>json_encode($expressions, true),"answers"=>json_encode($answers), "notes"=>$notes]);
    }
}
