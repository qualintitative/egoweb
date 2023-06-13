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

/**
 * Import Export controller
 */
class ImportExportController extends Controller
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
     * Creates a new study in the database from a .study XML file
     */
    public function actionImportstudy()
    {
        $message = "ERROR";
        switch ($_FILES['files']['error'][0]) {
            case UPLOAD_ERR_OK:
                $message = false;
                break;
            case UPLOAD_ERR_INI_SIZE:
                $message .= ' - file(s) too large.  upload size defined in php.ini exceeded';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message .= ' - file(s) too large.  upload size defined in html exceeded';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message .= ' - file upload was not completed.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message .= ' - zero-length file uploaded.';
                break;
            default:
                $e = print_r($_FILES['files']['error']);
                $message .= ' - internal error #'. $e;
                break;
        }
        if ($message) {
            Yii::error($message);
        }
        if (!is_uploaded_file($_FILES['files']['tmp_name'][0])) { //checks that file is uploaded
            die("Error importing study: " . $message);
        }

        $newInterviewIds = array();
        $newAlterIds = array();
        $newQuestionIds = array();
        $newExpressionIds = array();
        $result = User::find()->all();
        $newOptionIds = array();
        $newUserIds = array();
        $qIds = [];
        $users = array();
        foreach ($result as $u) {
            $users[$u->email] = intval($u->id);
        }
        foreach ($_FILES['files']['tmp_name'] as $tmp_name) {
            $study = simplexml_load_file($tmp_name);
            if (!$study) {
                echo "Improperly formated XML study file";
                die();
            }
            $newStudy = new Study;
            $newAnswerIds = array();
            $merge = false;

            foreach ($study->attributes() as $key=>$value) {
                if ($key == "active") {
                    $value = intval($value);
                }
                if ($key == "id") {
                    $value = null;
                }
                if (in_array($key, array_keys($newStudy->attributes))) {
                    $newStudy->$key = html_entity_decode($value);
                }

                if ($key == "name") {
                    $oldStudy = Study::findOne(array("name"=>strval($value)));
                    if ($oldStudy && !$_POST['newName']) {
                        $merge = true;
                        $newStudy = $oldStudy;
                    }
                }
            }

            if ($merge == false) {
                foreach ($study as $key=>$value) {
                    if (count($value) == 0 && $key != "answerLists" && $key != "expressions") {
                        $newStudy->$key = html_entity_decode($value);
                    }
                }
                if (isset($_POST['newName']) && $_POST['newName']) {
                    $newStudy->name = $_POST['newName'];
                }
                $newStudy->userId = Yii::$app->user->identity->id;

                if ($newStudy->save()) {
                    $newStudyId = Yii::$app->db->getLastInsertID();
                    $newStudy->id = $newStudyId;
                } else {
                    echo "study: " . print_r($newStudy->attributes);
                    die();
                }

                if ($study->alterLists->alterList) {
                    foreach ($study->alterLists->alterList as $alterList) {
                        $newAlterList = new AlterList;
                        foreach ($alterList->attributes() as $key=>$value) {
                            if (in_array($key, array("ordering", "studyId", "interviewerId"))) {
                                $value = intval($value);
                            }
                            if ($key != "id") {
                                $newAlterList->$key = html_entity_decode($value);
                            }
                        }
                        $newAlterList->studyId = $newStudy->id;
                        if (!$newAlterList->save()) {
                            echo "Alter list: $newAlterList->name [". gettype($newAlterList->name) . "]: $newAlterList->email";
                            print_r($newAlterList->errors);
                            die();
                        }
                    }
                }
                $hasNameGen = false;
                $nameGenQId = "0";
                foreach ($study->questions->question as $question) {
                    foreach ($question->attributes() as $key=>$value) {
                        if ($value == "NAME_GENERATOR") {
                            $hasNameGen = true;
                        }
                    }
                }
                $qCount = 0;
                $egoCount = 0;
                foreach ($study->questions->question as $question) {
                    //print_r($question);
                    if (!$hasNameGen && $question->attributes()->subjectType == "ALTER") {
                        $newQuestion = new Question;
                        $hasNameGen = true;
                        $newQuestion->attributes = array(
                            'subjectType' => "NAME_GENERATOR",
                            'prompt' => $newStudy->alterPrompt,
                            'studyId' => $newStudyId,
                            'title' => "ALTER_PROMPT",
                            'answerType' => "NAME_GENERATOR",
                            'ordering' => $qCount,
                        );
                        $qCount++;
                        $newQuestion->save();
                        $nameGenQId = strval($newQuestion->id);
                    }
                    $newQuestion = new Question;
                    $qKeys = array_keys($newQuestion->attributes);

                    $newQuestion->studyId = $newStudy->id;
                    foreach ($question->attributes() as $key=>$value) {
                        if ($key == "id") {
                            $oldId = intval($value);
                        }
                        if ($key == "ordering") {
                            if ($nameGenQId == "0") {
                                $value = intval($value);
                            } else {
                                if ($question->attributes()->subjectType == "EGO_ID") {
                                    $value = $egoCount;
                                    $egoCount++;
                                } else {
                                    $value = $qCount;
                                    $qCount++;
                                }
                            }
                        }

                        if ($key!="key" && $key != "id" && in_array($key, $qKeys)) {
                            $newQuestion->$key = html_entity_decode($value);
                        }
                    }
                    if ($newQuestion->answerType == "SELECTION") {
                        $newQuestion->answerType = "MULTIPLE_SELECTION";
                        $newQuestion->minCheckableBoxes = 1;
                        $newQuestion->maxCheckableBoxes = 1;
                    }
                    $options = 0;
                    foreach ($question as $key=>$value) {
                        if ($key == "option") {
                            $options++;
                        } elseif (count($value) == 0 && $key != "option") {
                            $newQuestion->$key = html_entity_decode($value);
                        }
                    }
                    if (!$newQuestion->save()) {
                        echo "Question: " . print_r($newQuestion->getErrors());
                        die();
                    } else {
                        $newQuestionIds[$oldId] = $newQuestion->id;
                    }

                    if ($options > 0) {
                        foreach ($question->option as $option) {
                            $newOption = new QuestionOption;
                            $newOption->studyId = $newStudy->id;
                            foreach ($option->attributes() as $optionkey=>$val) {
                                if ($optionkey == "id") {
                                    $oldOptionId = intval($val);
                                }
                                if ($optionkey == "ordering" || $optionkey == "single" || $optionkey == "otherSpecify") {
                                    $val = intval($val);
                                }
                                if ($optionkey!="key" && $optionkey != "id") {
                                    $newOption->$optionkey = html_entity_decode($val);
                                }
                            }
                            $newOption->questionId = $newQuestion->id;
                            if (!$newOption->save()) {
                                echo "Option: " . print_r($newOption->getErrors());
                            } else {
                                $newOptionIds[$oldOptionId] = $newOption->id;
                            }
                        }
                    }
                }

                // loop through the questions and correct linked ids
                $newQuestions = Question::findAll(array('studyId'=>$newStudy->id));
                foreach ($newQuestions as $newQuestion) {
                    if (is_numeric($newQuestion->listRangeString) && isset($newOptionIds[intval($newQuestion->listRangeString)])) {
                        $newQuestion->listRangeString = $newOptionIds[intval($newQuestion->listRangeString)];
                    }
                    $newQuestion->save();
                }

                $newStudy = Study::findOne($newStudy->id);
                if ($newStudy->multiSessionEgoId != 0 && isset($newQuestionIds[intval($newStudy->multiSessionEgoId)])) {
                    $newStudy->multiSessionEgoId = $newQuestionIds[intval($newStudy->multiSessionEgoId)];
                    if ($newStudy->save()) {
                    } else {
                        echo "Multi-ssssion: ";
                        print_r($newStudy->getErrors());
                    }
                }

                if ($study->alterPrompts->alterPrompt) {
                    foreach ($study->alterPrompts->alterPrompt as $alterPrompt) {
                        $newAlterPrompt = new AlterPrompt;
                        foreach ($alterPrompt->attributes() as $key=>$value) {
                            if ($key == "afterAltersEntered") {
                                $value = intval($value);
                            }
                            if ($key != "id") {
                                $newAlterPrompt->$key = strval($value);
                            }
                            if ($key == "questionId") {
                                if (isset($newQuestionIds[intval($value)])) {
                                    $newAlterPrompt->questionId = $newQuestionIds[intval($value)];
                                } else {
                                    $newAlterPrompt->questionId = 0;
                                }
                            }
                        }
                        $newAlterPrompt->studyId = $newStudy->id;
                        if (!$newAlterPrompt->save()) {
                            print_r($newAlterPrompt->attributes);
                            echo "Alter prompt: $newStudy->id : $newAlterPrompt->afterAltersEntered :" . print_r($newAlterPrompt->errors);
                        }
                    }
                }

                if (count($study->expressions) != 0) {
                    foreach ($study->expressions->expression as $expression) {
                        $newExpression = new Expression;
                        $newExpression->studyId = $newStudy->id;
                        foreach ($expression->attributes() as $key=>$value) {
                            if ($key == "id") {
                                $oldExpressionId = intval($value);
                            }
                            if ($key == "ordering") {
                                $value = intval($value);
                            }
                            if ($key!="key" && $key != "id") {
                                $newExpression->$key = strval($value);
                            }
                        }
                        // reference the correct question, since we're not using old ids

                        //if ($newExpression->questionId != "" && isset($newQuestionIds[intval($newExpression->questionId)])) {
                        //    $newExpression->questionId = $newQuestionIds[intval($newExpression->questionId)];
                        //}

                        $newExpression->value = strval($expression->attributes()->value);
                        if (!$newExpression->save()) {
                            echo "Expression: " . print_r($newExpression->getErrors());
                        } else {
                            $newExpressionIds[$oldExpressionId] = $newExpression->id;
                        }
                    }
                }
                // loop through questions and relink network params
                $questions = Question::findAll(array('studyId'=>$newStudy->id));
                if (count($questions) > 0) {
                    foreach ($questions as $question) {
                        if ($question->subjectType == "NETWORK") {
                            $params = json_decode(htmlspecialchars_decode($question->networkParams), true);
                            if ($params) {
                                foreach ($params as $k => &$param) {
                                    if (isset($param['questionId']) && stristr($param['questionId'], "expression")) {
                                        list($label, $expressionId) = explode("_", $param['questionId']);
                                        if (isset($newExpressionIds[intval($expressionId)])) {
                                            $expressionId = $newExpressionIds[intval($expressionId)];
                                        }
                                        $param['questionId'] = "expression_" . $expressionId;
                                    } else {
                                        if (isset($param['questionId']) && is_numeric($param['questionId']) && isset($newQuestionIds[intval($param['questionId'])])) {
                                            $param['questionId'] = $newQuestionIds[intval($param['questionId'])];
                                        }
                                        if (isset($param['options']) && count($param['options']) > 0) {
                                            foreach ($param['options'] as &$option) {
                                                if (isset($newOptionIds[intval($option['id'])])) {
                                                    $option['id'] = $newOptionIds[intval($option['id'])];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $question->networkParams = json_encode($params);
                        }

                        if (isset($newExpressionIds[$question->answerReasonExpressionId])) {
                            $question->answerReasonExpressionId = $newExpressionIds[$question->answerReasonExpressionId];
                        } else {
                            $question->answerReasonExpressionId = "";
                        }

                        if (isset($newExpressionIds[$question->networkRelationshipExprId])) {
                            $question->networkRelationshipExprId = $newExpressionIds[$question->networkRelationshipExprId];
                        } else {
                            $question->networkRelationshipExprId = "";
                        }

                        if (isset($newExpressionIds[$question->uselfExpression])) {
                            $question->uselfExpression = $newExpressionIds[$question->uselfExpression];
                        } else {
                            $question->uselfExpression = "";
                        }

                        $question->save();
                    }
                }
            } else {
                $nameGenQId = "0";
                $eIds = [];
                $questions = Question::findAll(array('studyId'=>$newStudy->id));
                foreach ($questions as $question) {
                    if ($question->subjectType == "NAME_GENERATOR") {
                        $nameGenQId = strval($question->id);
                    }
                    $qIds[$question->title] = $question->id;
                }
                echo $newStudy->id;
                echo "<BR>";
                print_r($qIds);
                //die();
                $options = QuestionOption::findAll(array('studyId'=>$newStudy->id));
                foreach ($options as $option) {
                    $oIds[$option->questionId . "-" . $option->name] = $option->id;
                }
                $expressions = Expression::findAll(array('studyId'=>$newStudy->id));
                foreach ($expressions as $expression) {
                    $eIds[$expression->name] = $expression->id;
                }
                foreach ($study->questions->question as $question) {
                    $q_attributes = $question->attributes();
                    if(isset($qIds[strval($q_attributes['title'])]))
                        $newQuestionIds[intval($q_attributes['id'])] = $qIds[strval($q_attributes['title'])];
                    else
                        continue;
                    if (isset($question->option)) {
                        foreach ($question->option as $option) {
                            $o_attributes = $option->attributes();
                            if(isset( $oIds[strval($qIds[strval($q_attributes['title'])] . "-" .$o_attributes['name'])]))
                                $newOptionIds[intval($o_attributes['id'])] = $oIds[strval($qIds[strval($q_attributes['title'])] . "-" .$o_attributes['name'])];
                        }
                    }
                }
                if (count($study->expressions) != 0) {
                    foreach ($study->expressions->expression as $expression) {
                        $e_attributes = $expression->attributes();
                        if(isset( $eIds[strval($e_attributes['name'])]))
                            $newExpressionIds[intval($e_attributes['id'])] = $eIds[strval($e_attributes['name'])];
                    }
                }
            }



            if (count($study->interviews) != 0) {
                foreach ($study->interviews->interview as $interview) {
                    $newInterview = new Interview;
                    foreach ($interview->attributes() as $key=>$value) {
                        if ($key == "id") {
                            $oldInterviewId = intval($value);
                        }
                        if ($key!="key" && $key != "id") {
                            $newInterview->$key = strval($value);
                        }
                    }
                    $newInterview->studyId = $newStudy->id;
                    if (!$newInterview->save()) {
                        echo "New interview error: " .  print_r($newInterview->errors);
                        die();
                    } else {
                        $newInterviewId = Yii::$app->db->getLastInsertID();
                        $newInterviewIds[intval($oldInterviewId)] =  $newInterviewId;
                    }

                    if ($interview->matchedAlters->matchedAlter && count($interview->matchedAlters->matchedAlter) > 0) {
                        foreach ($interview->matchedAlters->matchedAlter as $match) {
                            $newMatch = new MatchedAlters;
                            foreach ($match->attributes() as $key=>$value) {
                                if (in_array($key, array("ordering", "studyId", "interviewId1", "interviewId2", "alterId1", "alterId2", "userId"))) {
                                    $value = intval($value);
                                }
                                if ($key != "id") {
                                    $newMatch->$key = strval($value);
                                }
                            }
                            $newMatch->studyId = $newStudy->id;
                            if (!$newMatch->save()) {
                                echo "Matched Alter Error: $newMatch->interviewId1 :" . $newMatch->interviewId2 . ":" . $newMatch->matchedName . ":" . is_string( $newMatch->matchedName) . gettype($newMatch->matchedName);
                                print_r($newMatch->errors);
                                die();
                            }
                        }
                    }
                    if ($interview->users && count($interview->users->user) > 0) {
                        foreach ($interview->users->user as $u) {
                            $userExists = false;
                            foreach ($u->attributes() as $key=>$value) {
                                if ($key == "email" && in_array(strval($value), array_keys($users))) {
                                    $userExists = strval($value);
                                }
                                if ($key == "id") {
                                    $oldUserId = intval($value);
                                }
                            }
                            if ($userExists != false) {
                                $newUserIds[$oldUserId] = $users[$userExists];
                                //echo $users[$userExists];
                                continue;
                            }
                            $newUser = new User;
                            foreach ($u->attributes() as $key=>$value) {
                                if (in_array($key, array("ordering", "studyId", "interviewId1", "interviewId2", "alterId1", "alterId2", "userId"))) {
                                    $value = intval($value);
                                }
                                if ($key != "id") {
                                    $newUser->$key = $value;
                                }
                                if ($key == "email") {
                                    if (strval($value) == "") {
                                        $email = false;
                                    } else {
                                        $email = strval($value);
                                    }
                                }
                            }
                            //$newUser->confirm = $newUser->password;
                            if ($email == false) {
                                continue;
                            }
                            if (!$newUser->save()) {
                                print_r($users);
                                echo $newUser->email;
                                echo "User Error: $newUser->id :" . print_r($newUser->errors);
                                die();
                            } else {
                                $newUserId = Yii::$app->db->getLastInsertID();
                                $newUserIds[$oldUserId] = $newUserId;
                                $users[$email] = $newUserId;
                            }
                        }
                    }
                    if (count($interview->alters->alter) != 0) {
                        foreach ($interview->alters->alter as $alter) {
                            $newAlter = new Alters;
                            foreach ($alter->attributes() as $key=>$value) {
                                if ($key == "id") {
                                    $thisAlterId = $value;
                                }
                                if ($key!="key" && $key != "id") {
                                    $newAlter->$key = strval($value);
                                }
                            }
                            //skip alter if it's already exists in array
                            if (in_array(intval($thisAlterId), array_keys($newAlterIds))) {
                                continue;
                            }
                            if (!$newAlter->nameGenQIds) {
                                $newAlter->nameGenQIds = $nameGenQId;
                            }
                            if (!preg_match("/,/", $newAlter->interviewId)) {
                                $newAlter->interviewId = $newInterviewId;
                            }

                            if (!$newAlter->save()) {
                                echo "Alter: ";
                                print_r($newAlter->getErrors());
                                die();
                            } else {
                                $newAlterIds[intval($thisAlterId)] = $newAlter->id;
                            }
                        }
                    }

                    if (count($interview->notes->note) != 0) {
                        foreach ($interview->notes->note as $note) {
                            $newNote = new Note;
                            foreach ($note->attributes() as $key=>$value) {
                                if ($key!="key" && $key != "id") {
                                    $newNote->$key = strval($value);
                                }
                            }
                            if (!preg_match("/,/", $newNote->interviewId)) {
                                $newNote->interviewId = $newInterviewId;
                            }

                            if (!isset($newExpressionIds[intval($newNote->expressionId)]) || !isset($newAlterIds[intval($newNote->alterId)])) {
                                continue;
                            }

                            $newNote->expressionId = $newExpressionIds[intval($newNote->expressionId)];
                            $newNote->alterId = strval($newAlterIds[intval($newNote->alterId)]);

                            if (!$newNote->save()) {
                                echo "Note: ";
                                print_r($newNote->errors);
                                die();
                            }
                        }
                    }

                    if (count($interview->graphs->graph) != 0) {
                        foreach ($interview->graphs->graph as $graph) {
                            $newGraph = new Graph;
                            foreach ($graph->attributes() as $key=>$value) {
                                if ($key!="key" && $key != "id") {
                                    if ($key == "params") {
                                        $params = json_decode(htmlspecialchars_decode($value), true);
                                        if ($params) {
                                            foreach ($params as $k => &$param) {
                                                if (isset($param['questionId']) && is_numeric($param['questionId']) && isset($newQuestionIds[intval($param['questionId'])])) {
                                                    $param['questionId'] = $newQuestionIds[intval($param['questionId'])];
                                                }
                                                if (isset($param['options']) && count($param['options']) > 0) {
                                                    foreach ($param['options'] as &$option) {
                                                        if (isset($newOptionIds[intval($option['id'])])) {
                                                            $option['id'] = $newOptionIds[intval($option['id'])];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $value = json_encode($params);
                                    }
                                    if ($key == "nodes") {
                                        $nodes = json_decode(htmlspecialchars_decode($value), true);
                                        foreach ($nodes as $node) {
                                            $oldNodeId = $node['id'];
                                            if (is_numeric($node['id']) && isset($newAlterIds[intval($node['id'])])) {
                                                $node['id'] =  $newAlterIds[intval($node['id'])];
                                            }
                                            $nodes[$node['id']] = $node;
                                            unset($nodes[$oldNodeId]);
                                        }
                                        $value = json_encode($nodes);
                                    }
                                    $newGraph->$key = $value;
                                }
                            }
                            if (!preg_match("/,/", $newGraph->interviewId)) {
                                $newGraph->interviewId = $newInterviewId;
                            }

                            if (isset($newExpressionIds[intval($newGraph->expressionId)])) {
                                $newGraph->expressionId = $newExpressionIds[intval($newGraph->expressionId)];
                            }

                            if (!$newGraph->save()) {
                                print_r($newExpressionIds);
                                print_r($newGraph->expressionId);
                                echo "Graph: " . print_r($newGraph->errors);
                                die();
                            }
                        }
                    }

                    if (count($interview->answers->answer) != 0) {
                        foreach ($interview->answers->answer as $answer) {
                            $newAnswer = new Answer;

                            foreach ($answer->attributes() as $key=>$value) {
                                if ($key!="key" && $key != "id") {
                                    $value = htmlspecialchars_decode($value);
                                    $newAnswer->$key = $value;
                                }
                                if ($key == "alterId1" && isset($newAlterIds[intval($value)])) {
                                    $newAnswer->alterId1 = $newAlterIds[intval($value)];
                                }
                                if ($key == "alterId2" && isset($newAlterIds[intval($value)])) {
                                    $newAnswer->alterId2 = $newAlterIds[intval($value)];
                                }


                                if ($key == "questionId") {
                                    if (isset($newQuestionIds[intval($value)])) {
                                        $newAnswer->questionId = $newQuestionIds[intval($value)];
                                        $oldQId = intval($value);
                                    }
                                }

                                if ($key == "answerType") {
                                    $answerType = $value;
                                }
                            }

                            $newAnswer->value = html_entity_decode($newAnswer->value, ENT_QUOTES);


                            if ($answerType == "MULTIPLE_SELECTION" && !in_array($newAnswer->value, array($newStudy->valueRefusal,$newStudy->valueDontKnow,$newStudy->valueLogicalSkip,$newStudy->valueNotYetAnswered))) {
                                $values = explode(',', $newAnswer->value);
                                foreach ($values as &$value) {
                                    if (isset($newOptionIds[intval($value)])) {
                                        $value = $newOptionIds[intval($value)];
                                    }
                                }
                                $newAnswer->value = implode(',', $values);
                            }

                            if ($newAnswer->otherSpecifyText != "") {
                                $otherSpecifies = array();
                                foreach (preg_split('/;;/', $newAnswer->otherSpecifyText) as $otherSpecify) {
                                    if (strstr($otherSpecify, ':')) {
                                        list($optionId, $val) = preg_split('/:/', $otherSpecify);
                                        if (isset($newOptionIds[intval($optionId)])) {
                                            $optionId = $newOptionIds[intval($optionId)];
                                        }
                                        $otherSpecifies[] = $optionId.":".html_entity_decode($val, ENT_QUOTES);
                                    }
                                }
                                if (count($otherSpecifies) > 0) {
                                    $newAnswer->otherSpecifyText = implode(";;", $otherSpecifies);
                                }
                            }

                            $newAnswer->studyId = $newStudy->id;
                            $newAnswer->interviewId = $newInterviewId;

                            if (!isset($oldQId)  || !isset($newQuestionIds[$oldQId]) || !$newQuestionIds[$oldQId]) {
                                continue;
                            }

                        
                            if (!$newAnswer->save()) {
                                echo "new answer:";
                                echo $oldQId . "<br>";
                                echo $newQuestionIds[$oldQId]."<br>";
                                print_r($newQuestionIds);
                                print_r($newAnswer->errors);
                                die();
                            }
                        }
                    }
                }
            }

            if (count($study->answerLists) != 0) {
                foreach ($study->answerLists->answerList as $answerList) {
                    $newAnswerList = new AnswerList;
                    $newAnswerList->studyId = $newStudy->id;
                    foreach ($answerList->attributes() as $key=>$value) {
                        if ($key!="key" && $key != "id") {
                            $newAnswerList->$key = $value;
                        }
                    }
                    if (!$newAnswerList->save()) {
                        echo "AnswerList: " .  print_r($newAnswerList->getErrors());
                    }
                }
            }
        }
        // second loop to replace old ids in Expression->value
        foreach ($newExpressionIds as $oldExpId=>$newExpId) {
            $newExpression = Expression::findOne($newExpId);
            if ($newExpression->questionId != "" && isset($newQuestionIds[intval($newExpression->questionId)])) {
                $newExpression->questionId = $newQuestionIds[intval($newExpression->questionId)];
            }
            // reference the correct question, since we're not using old ids
            if ($newExpression->type == "Selection") {
                $optionIds = explode(',', $newExpression->value);
                foreach ($optionIds as &$optionId) {
                    if (is_numeric($optionId) && isset($newOptionIds[intval($optionId)])) {
                        //  echo $newOptionIds[$optionId];
                        $optionId = $newOptionIds[intval($optionId)];
                    }
                }
                $newExpression->value = implode(',', $optionIds);
            } elseif ($newExpression->type == "Counting") {
                if (!strstr($newExpression->value, ':')) {
                    continue;
                }
                list($times, $expressionIds, $questionIds) = explode(':', $newExpression->value);
                if ($expressionIds != "") {
                    $expressionIds = explode(',', $expressionIds);
                    foreach ($expressionIds as &$expressionId) {
                        $expressionId = $newExpressionIds[intval($expressionId)];
                    }
                    $expressionIds = implode(',', $expressionIds);
                }
                if ($questionIds != "") {
                    $questionIds = explode(',', $questionIds);
                    foreach ($questionIds as &$questionId) {
                        if (isset($newQuestionIds[intval($questionId)])) {
                            $questionId = $newQuestionIds[intval($questionId)];
                        } else {
                            $questionId = '';
                        }
                    }
                    $questionIds = implode(',', $questionIds);
                }
                $newExpression->value = $times . ":" .  $expressionIds . ":" . $questionIds;
            } elseif ($newExpression->type == "Comparison") {
                list($value, $expressionId) = explode(':', $newExpression->value);
                $newExpression->value = $value . ":" . $newExpressionIds[intval($expressionId)];
            } elseif ($newExpression->type == "Compound") {
                $expressionIds = explode(',', $newExpression->value);
                foreach ($expressionIds as &$expressionId) {
                    if (is_numeric($expressionId) && isset($newExpressionIds[intval($expressionId)])) {
                        $expressionId = $newExpressionIds[intval($expressionId)];
                    }
                }
                $newExpression->value = implode(',', $expressionIds);
            } elseif ($newExpression->type == "Name Generator") {
                if ($newExpression->value != "") {
                    $questionIds = explode(',', $newExpression->value);
                    foreach ($questionIds as &$questionId) {
                        if (isset($newQuestionIds[intval($questionId)])) {
                            $questionId = $newQuestionIds[intval($questionId)];
                        } else {
                            $questionId = '';
                        }
                    }
                    $newExpression->value = implode(',', $questionIds);
                }
            }
            if (!$newExpression->save()) {
                print_r($newExpression->errors);
                die();
            }
        }
        foreach ($newAlterIds as $oldId=>$newId) {
            $alter = Alters::findOne($newId);
            if (preg_match("/,/", $alter->interviewId)) {
                $values = explode(',', $alter->interviewId);
                foreach ($values as &$value) {
                    if (isset($newInterviewIds[intval($value)])) {
                        $value = $newInterviewIds[intval($value)];
                    }
                }
                $alter->interviewId = implode(',', $values);
            }
            if (preg_match("/,/", $alter->alterListId)) {
                $values = explode(',', $alter->alterListId);
                $vs = array();
                foreach ($values as $value) {
                    if (isset($newInterviewIds[intval($value)])) {
                        $vs[] = $newInterviewIds[intval($value)];
                    }
                    if (isset($newAlterIds[intval($value)])) {
                        $vs[] = $newAlterIds[intval($value)];
                    }
                }
                $alter->alterListId = implode(',', $vs);
            } elseif (isset($newInterviewIds[intval($alter->alterListId)])) {
                $alter->alterListId = $newInterviewIds[intval($alter->alterListId)];
            } elseif (isset($newAlterIds[intval($alter->alterListId)])) {
                $alter->alterListId = $newAlterIds[intval($alter->alterListId)];
            }

            if (stristr($alter->nameGenQIds, ",")) {
                $nQIds = explode(",", $alter->nameGenQIds);
                foreach ($nQIds as &$nQId) {
                    $nQId = intval($nQId);
                    if (isset($newQuestionIds[$nQId])) {
                        $nQId = $newQuestionIds[$nQId];
                    }
                }
                $alter->nameGenQIds = implode(",", $nQIds);
            } else {
                if (isset($newQuestionIds[intval($alter->nameGenQIds)])) {
                    $alter->nameGenQIds = strval($newQuestionIds[intval($alter->nameGenQIds)]);
                }
            }

            if (stristr($alter->ordering, "{")) {
                $nGorder = json_decode($alter->ordering, true);
                $newOrder = array();
                foreach ($nGorder as $nQid=>$norder) {
                    if (isset($newQuestionIds[intval($nQid)])) {
                        $newOrder[$newQuestionIds[intval($nQid)]] = $norder;
                    } else {
                        $newOrder[$nQid] = $norder;
                    }
                }
                $alter->ordering = json_encode($newOrder);
            }
            $alter->alterListId = strval($alter->alterListId);
            if (!$alter->save()) {
                print_r($alter->errors);
                echo $alter->alterListId;
                die();
            }
        }
        
        foreach ($newInterviewIds as $oldId=>$newId) {
            $matches = MatchedAlters::findAll(array("interviewId1"=>$oldId));
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    if (isset($newInterviewIds[intval($match->interviewId1)])) {
                        $match->interviewId1 = $newInterviewIds[intval($match->interviewId1)];
                    }
                    if (isset($newInterviewIds[intval($match->interviewId2)])) {
                        $match->interviewId2 = $newInterviewIds[intval($match->interviewId2)];
                    }
                    if (isset($newAlterIds[intval($match->alterId1)])) {
                        $match->alterId1 = $newAlterIds[intval($match->alterId1)];
                    }
                    if (isset($newAlterIds[intval($match->alterId2)])) {
                        $match->alterId2 = $newAlterIds[intval($match->alterId2)];
                    }
                    if (isset($newUserIds[intval($match->userId)])) {
                        $match->userId = $newUserIds[intval($match->userId)];
                    }
                    $match->save();
                }
            }
            
            $matches = MatchedAlters::findAll(array("interviewId2"=>$oldId));
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    if (isset($newInterviewIds[intval($match->interviewId1)])) {
                        $match->interviewId1 = $newInterviewIds[intval($match->interviewId1)];
                    }
                    if (isset($newInterviewIds[intval($match->interviewId2)])) {
                        $match->interviewId2 = $newInterviewIds[intval($match->interviewId2)];
                    }
                    if (isset($newAlterIds[intval($match->alterId1)])) {
                        $match->alterId1 = $newAlterIds[intval($match->alterId1)];
                    }
                    if (isset($newAlterIds[intval($match->alterId2)])) {
                        $match->alterId2 = $newAlterIds[intval($match->alterId2)];
                    }
                    if (isset($newUserIds[intval($match->userId)])) {
                        $match->userId = $newUserIds[intval($match->userId)];
                    }
                    $match->save();
                }
            }
            $prevAnswers = Answer::findAll(array("interviewId"=>$newId, "questionType"=>"PREVIOUS_ALTER"));
            if (count($prevAnswers) > 0) {
                foreach ($prevAnswers as $prevAnswer) {
                    if(!isset($newAlterIds[intval($prevAnswer->alterId1)]))
                        continue;
                    $prevAnswer->alterId1 = $newAlterIds[intval($prevAnswer->alterId1)];
                    $prevAnswer->save();
                }
            }
        }
        return $this->redirect(Url::toRoute('/authoring/' . $newStudy->id));
    }

    public function actionIndex()
    {
        $this->view->title = "EgoWeb 2.0";
        if (isset($_POST['Server'])) {
            $server = new Server;
            $server->attributes = $_POST['Server'];
            $server->userId = Yii::$app->user->identity->id;
            $server->save();
            return $this->response->redirect(Url::toRoute('/import-export'));
        }
        if (Yii::$app->user->identity->isSuperAdmin()) {
            $result = Server::find()->all();
        } else {
            $result = Server::findAll(array("userId"=>Yii::$app->user->identity->id));
        }
        $servers = array();
        foreach ($result as $server) {
            $servers[$server->id] = Tools::mtoA($server);
        }

        $studies = Yii::$app->user->identity->studies;
        return $this->render('index', ["servers"=>$servers, "studies"=>$studies]);
    }

    public function actionAjaxinterviews($id)
    {
        $study = Study::findOne($id);
        $interviews = Interview::findAll(array('studyId'=>$id));
        $allInterviewIds = [];
        $result = Answer::findAll([
            "studyId"=>$study->id,
            "questionType"=>"EGO_ID",
        ]);
        $egoid_answers = array();
        foreach ($result as $answer) {
            if($answer->answerType == "RANDOM_NUMBER" || $answer->answerType == "STORED_VALUE")
                continue;
            if(!isset($egoid_answers[$answer->interviewId]))
                $egoid_answers[$answer->interviewId] = [];
            $egoid_answers[$answer->interviewId][] = $answer->value;
        }
        $egoIds = [];
        foreach($interviews as $interview){
            $alters[$interview->id] = 0;
            if(!isset($egoid_answers[$interview->id]))
                $egoid_answers[$interview->id] = ["error"];
            $egoIds[$interview->id] = implode("_", $egoid_answers[$interview->id]);
            $allInterviewIds[] = $interview->id;
        }
        return $this->renderAjax(
            '_interviews',
            array(
                'study'=>$study,
                'interviews'=>$interviews,
                'egoIds'=>$egoIds,
            ),
            false,
            true
        );
    }

    public function actionAjaxexport()
    {
        $interview = Interview::findOne($_POST['interviewId']);
        if ($interview) {
            $filePath = getcwd() . "/assets/" .  $interview->studyId . "/";
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
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
            $columns['matchedAlters'] = Yii::$app->db->getTableSchema("matchedAlters")->getColumnNames();
            $columns['user'] = Yii::$app->db->getTableSchema("user")->getColumnNames();
            $file = fopen($filePath . "/" .  $interview->id . ".xml", "w") or die("Unable to open file!");
            fclose($file);
            $interview->exportStudyInterview($filePath . "/" .  $interview->id . ".xml", $columns);
        }
        echo "success";
    }

    public function actionExportstudy()
    {
        if (!isset($_POST['studyId']) || $_POST['studyId'] == "") {
            die("nothing to export");
        }

        $study = Study::findOne($_POST['studyId']);

        $filePath = getcwd() . "/assets/" . $_POST['studyId'];
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fp = fopen($filePath  . "/$study->name.study", 'w');

        $interviewIds = [];
        if (isset($_POST['export'])) {
            $interviewIds = $_POST['export'];
        }

        $questions = Question::find()->where(array('studyId'=>$study->id))->orderBy(array('subjectType'=>'ASC', 'ordering'=>'ASC'))->all();
        $expressions = Expression::findAll(array('studyId'=>$study->id));
        $alterLists = AlterList::findAll(array("studyId"=>$study->id));
        $alterPrompts = AlterPrompt::findAll(array("studyId"=>$study->id));

        $study->introduction = Tools::sanitizeXml($study->introduction);
        $study->egoIdPrompt = Tools::sanitizeXml($study->egoIdPrompt);
        $study->alterPrompt = Tools::sanitizeXml($study->alterPrompt);
        $study->conclusion = Tools::sanitizeXml($study->conclusion);

        if (count($interviewIds) > 0) {
            $interviews = Interview::findAll(array("id"=>$interviewIds));
            foreach ($interviews as $result) {
                $interview[$result->id] = $result;
                $answer = Answer::findAll(array("interviewId"=>$result->id));
                $answers[$result->id] = $answer;
                $alter = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $result->id .", interviewId)"))
                ->orderBy(['ordering'=>'ASC'])
                ->all();
                $alters[$result->id] = $alter;
                $graph = Graph::findAll(array("interviewId"=>$result->id));
                $graphs[$result->id] = $graph;
                $note = Note::findAll(array("interviewId"=>$result->id));
                $notes[$result->id] = $note;
                $other = array();
                $others[$result->id] = $other;
            }
        }
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
        $exclude = array("studyId", "active");
        $x=new \XMLWriter();
        $x->openMemory();
        $x->setIndent(true);
        $x->startDocument('1.0', 'UTF-8');
        $x->startElement('study');
        foreach ($columns['study'] as $attr) {
            $x->writeAttribute($attr, $study->$attr);
        }
        $x->writeElement('introduction', $study->introduction);
        $x->writeElement('egoIdPrompt', $study->egoIdPrompt);
        $x->writeElement('alterPrompt', $study->alterPrompt);
        $x->writeElement('conclusion', $study->conclusion);
        if (count($alterLists) > 0) {
            $x->startElement('alterLists');
            foreach ($alterLists as $alterList) {
                $x->startElement('alterList');
                foreach ($columns['alterList'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $alterList->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (count($alterPrompts) > 0) {
            $x->startElement('alterPrompts');
            foreach ($alterPrompts as $alterPrompt) {
                $x->startElement('alterPrompt');
                foreach ($columns['alterPrompt'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $alterPrompt->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (count($questions) > 0) {
            $x->startElement('questions');
            foreach ($questions as $question) {
                $x->startElement('question');
                foreach ($columns['question'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $question->$attr);
                    }
                }
                if ($question->answerType == "SELECTION" || $question->answerType == "MULTIPLE_SELECTION") {
                    $options = QuestionOption::findAll(
                        array("studyId"=>$_POST['studyId'], "questionId"=>$question->id)
                    );
                    foreach ($options as $option) {
                        $x->startElement('option');
                        foreach ($columns['questionOption'] as $attr) {
                            if (!in_array($attr, $exclude)) {
                                $x->writeAttribute($attr, $option->$attr);
                            }
                        }
                        $x->endElement();
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (count($expressions) > 0) {
            $x->startElement('expressions');
            foreach ($expressions as $expression) {
                $x->startElement('expression');
                foreach ($columns['expression'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $expression->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        $text = $x->outputMemory();
        fputs($fp, $text);

        if (count($interviewIds) > 0) {
            fputs($fp, "<interviews>\r\n");
            foreach ($interviewIds as $interviewId) {
                $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/". $interviewId . ".xml";
                if (file_exists($filePath)) {
                    fputs($fp, file_get_contents($filePath));
                    unlink($filePath);
                }
            }
            fputs($fp, "</interviews>\r\n");
        }
        fputs($fp, "</study>\r\n");
        fclose($fp);

        $filePath = getcwd() . "/assets/" . $_POST['studyId'] . "/$study->name.study";

        return Yii::$app->response->sendFile($filePath, "$study->name.study")
        ->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) {
            unlink($event->data);
        }, $filePath);
    }

    public function actionSend($id)
    {
        $study = Study::findOne($id);
        $expressions = array();
        $results = Expression::findAll(array("studyId"=>$id));
        foreach ($results as $result) {
            $expressions[] = Tools::mToA($result);
        }
        $questions = array();
        $results = Question::findAll(array("studyId"=>$id), array('order'=>'ordering'));
        foreach ($results as $result) {
            $questions[] = Tools::mToA($result);
        }
        $results = QuestionOption::findAll(array("studyId"=>$id));
        $options = [];
        foreach ($results as $result) {
            $options[] = Tools::mToA($result);
        }
        $participantList = array();
        $results = AlterList::findAll(array("studyId"=>$id));
        foreach ($results as $result) {
            if ($result->name) {
                $participantList['name'][] = $result->name;
            }
            if ($result->email) {
                $participantList['email'][] = $result->email;
            }
        }
        $alterPrompts = array();
        $results = AlterPrompt::findAll(array("studyId"=>$id));
        foreach ($results as $result) {
            if (!$result->questionId) {
                $result->questionId = 0;
            }
            $alterPrompts[] = Tools::mToA($result);
        }
        if (is_array($_POST['export'])) {
            $interviewIds = $_POST['export'];
        } else {
            $interviewIds = array(0);
        }
        if (count($options) == 0) {
            $options = (object)[];
        }
        $studies = array();
        $interviews = array();
        $answers = array();
        $alters = array();
        $graphs = array();
        $notes = array();
        foreach ($interviewIds as $interviewId) {
            if ($interviewId != 0) {
                $interview = Interview::findOne($interviewId);
                $interviewData = Tools::mToA($interview);
                $interviewData['EGOID'] = $interview->egoId;
                $interviews[] = $interviewData;

                $results = Answer::findAll(array('interviewId'=>$interviewId));
                foreach ($results as $result) {
                    $answers[] = Tools::mToA($result);
                }
                $results = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId .", interviewId)"))
                ->orderBy(['ordering'=>'ASC'])
                ->all();
                foreach ($results as $result) {
                    $alters[] = Tools::mToA($result);
                }
                $results = Graph::findAll(array('interviewId'=>$interviewId));
                foreach ($results as $result) {
                    $graphs[] = Tools::mToA($result);
                }
                $results = Note::findAll(array("interviewId"=>$interviewId));
                foreach ($results as $result) {
                    $notes[] = $result->notes;
                }
            }
            $studies[] = array(
             "study"=>Tools::mToA($study),
             "questions"=>$questions,
             "expressions"=>$expressions,
             "questionOptions"=>$options,
             "alterPrompts"=>$alterPrompts,
             "participantList"=>$participantList,
             "interviews" => $interviews,
             "answers"=>$answers,
             "alters"=>$alters,
             "graphs"=>$graphs,
             "notes"=>$notes,
         );
        }
        if (count($alters) == 0) {
            $alters = (object)[];
        }
        if (count($studies) > 1) {
            $data = $studies;
        } else {
            $data = $studies[0];
        }
        return $this->renderAjax('/layouts/ajax', ['json'=>json_encode($data)]);
    }

    public function actionDeleteserver()
    {
        if (isset($_POST['serverId'])) {
            $server = Server::findOne($_POST['serverId']);
            if ($server) {
                $server->delete();
                echo "success";
            } else {
                echo "fail";
            }
        } else {
            echo "fail";
        }
    }
}
