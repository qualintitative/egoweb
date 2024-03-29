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
 * API controller
 * handles 3rd party integration with Confirmit
 */
class ApiController extends Controller
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
    // Members
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers
     */
    const APPLICATION_ID = 'EGOWEB';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array();
    }

    private function checkAPIheader()
    {
        $headers = array();
        foreach (apache_request_headers() as $key => $value) {
            $header = strtolower($key) ;
            $headers[$header] = $value;
        }

        if (isset($_POST['api_key'])) {
            $headers['api_key'] = $_POST['api_key'];
        }

        if (!isset($headers['api_key'])) {
            return $this->sendResponse(422, "Missing API Key");
        }

        if ($headers['api_key'] != Yii::$app->params['apiKey']) {
            return $this->sendResponse(421, "Invalid API Key");
        }
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    // Survey Actions
    public function actionSurvey()
    {
        $this->checkAPIheader();

        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->getSurvey();
                break;
            case 'POST':
                $this->createSurvey();
                break;
            case 'PUT':
                $this->editSurvey();
                break;
            default:
                $this->sendResponse(405);
                break;
        }
    }

    private function createSurvey()
    {
        if (!isset($_POST['survey_id']) || !isset($_POST['user_id'])) {
            $msg = "Missing survey_id and/or user_id parameter";
            return $this->sendResponse(419, $msg);
        }

        $prefill = null;
        if (isset($_POST['prefill'])) {
            $prefill = $_POST['prefill'];
        }

        $questions = null;
        if (isset($_POST['questions'])) {
            $questions = $_POST['questions'];
        }

        $redirect = null;
        if (isset($_POST['redirect'])) {
            $redirect = $_POST['redirect'];
        }

        SurveyController::createSurvey($_POST['survey_id'], $_POST['user_id'], $prefill, $questions, $redirect);
    }

    /**
     * @todo fill in 'fields' response attribute
     */
    private function getSurvey()
    {
        if (!isset($_GET['survey_id'])) {
            $msg = "Missing survey_id parameter";
            return $this->sendResponse(419, $msg);
        }

        $study = Study::findOne($_GET['survey_id']);
        if (!$study) {
            $msg = "Survey: ".$_GET['survey_id'] . " not found";
            $this->sendResponse(404, $msg);
        }

        $questions = Question::findAll(array("studyId"=>$study->id));

        $started = count(Interview::findAll(array("studyId"=>$study->id)));
        $completed = count(Interview::findAll(array("studyId"=>$study->id,"completed"=>-1)));

        $data = array(
                    'survey'=>array(
                        'id'=>$study->id,
                        'name'=>$study->name,
                        'closed'=> $study->closed_date ? date('m/d/Y', $study->closed_date) : null,
                        'created'=> $study->created_date ? date('m/d/Y', $study->created_date) : null,
                        'num_completed'=>$completed,
                        'num_started'=>$started,
                        'status'=>$study->status,
                        'fields'=>$questions
                    ),
                );
        return $this->sendResponse(200, $data);
    }

    private function editSurvey()
    {
        parse_str(file_get_contents('php://input'), $put_vars);

        if (!isset($put_vars['survey_id'])) {
            $msg = "Missing survey_id parameter";
            return $this->sendResponse(419, $msg);
        }

        if (!isset($put_vars['status'])) {
            $msg = "Missing status parameter";
            return $this->sendResponse(419, $msg);
        }

        $study = Study::findOne($put_vars['survey_id']);
        if (!$study) {
            $msg = "Survey: ".$put_vars['survey_id'] . " not found";
            return $this->sendResponse(404, $msg);
        }

        $study->status = $put_vars['status'];
        $saved = $study->save();

        if (!$saved) {
            return $this->sendResponse(500, 'Unable to to update survey.');
        }

        return $this->sendResponse(200, 'Success');
    }

    // User Actions
    public function actionUser()
    {
        $this->checkAPIheader();

        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->getUser();
                break;
            default:
                $this->sendResponse(405);
                break;
        }
    }

    private function getUser()
    {
        if (!isset($_GET['user_id'])) {
            $msg = "Missing user_id parameter";
            return $this->sendResponse(419, $msg);
        }

        $questionIds = array();
        $criteria = new CDbCriteria;
        $questions = Question::find()
        ->where(new \yii\db\Expression('lower(title) = "mmic_prime_key"'))
        ->orderBy(['ordering'=>'ASC'])
        ->all();
        foreach ($questions as $question) {
            $questionIds[] = $question->id;
        }

        $interviews = Answer::findAll(array( 'questionId'=>$questionIds ));

        $surveys_completed = array();
        $surveys_started = array();
        $userFound = false;

        foreach ($interviews as $intv) {
            if ($intv->value == $_GET['user_id']) {
                $userFound = true;
                $interview = Interview::findOne($intv->interviewId);
                $study = Study::findOne($intv->studyId);
                if ($interview->complete_date) {
                    $surveys_completed[$study->id] = date('m/d/Y', $interview->complete_date);
                }
                if ($interview->start_date) {
                    $surveys_started[$study->id] = date('m/d/Y', $interview->start_date);
                }
            }
        }

        if (!$userFound) {
            $msg = $_GET['user_id'] . " not found";
            return $this->sendResponse(404, $msg);
        }

        $data = array(
            'user'=>array(
                'id'=>$_GET['user_id'],
                'surveys_completed'=>$surveys_completed,
                'surveys_started'=>$surveys_started,
            ),
        );

        return $this->sendResponse(200, $data);
    }

    public function actionSurvey_user()
    {
        $this->checkAPIheader();

        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->getSurveyUser();
                break;
            default:
                $this->sendResponse(405);
                break;
        }
    }


    private function getSurveyUser()
    {
        if (!isset($_POST['user_id']) || !isset($_POST['survey_id'])) {
            $msg = "Missing user_id or survey_id parameter";
            return $this->sendResponse(419, $msg);
        }

        $question = Question::find()
        ->where(new \yii\db\Expression('lower(title) = "mmic_prime_key" and studyId = ' . $_GET['survey_id']))
        ->orderBy(['ordering'=>'ASC'])
        ->one();
        $questionId = false;
        if ($question) {
            $questionId = $question->id;
        }

        if (!$questionId) {
            $msg = "Study not found";
            return $this->sendResponse(419, $msg);
        }

        $interview = Answer::findOne(array( 'questionId'=>$questionId));

        if (!$interview) {
            $msg = "Interview not found";
            return $this->sendResponse(419, $msg);
        }

        $responses = Answer::findAll(array( 'interviewId'=>$interview->id));
        $questions = Question::findAll(array('studyId'=>$interview->studyId));
        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET($interview->id, interviewId))"))
        ->orderBy(['ordering'=>'ASC'])
        ->all();
        $answers = array();
        $fields = array();

        foreach ($responses as $response) {
            if ($response->questionType == "ALTER") {
                $answers[$response->questionId . "-" . $response->alterId1] = $response->value;
            } elseif ($response->questionType == "ALTER_PAIR") {
                $answers[$response->questionId . "-" . $response->alterId1 . "and" . $response->alterId2] = $response->value;
            } else {
                $answers[$response->questionId] = $response->value;
            }
        }


        foreach ($questions as $question) {
            if ($question->subjectType == "ALTER") {
                foreach ($alters as $alter) {
                    if (isset($answers[$question->id . "-" . $alter->id])) {
                        $fields[$question->title.":".$alter->name] = $answers[$question->id . "-" . $alter->id];
                    }
                }
            } elseif ($question->subjectType == "ALTER_PAIR") {
                foreach ($alters as $alter) {
                    foreach ($alters as $alter2) {
                        if (isset($answers[$question->id . "-" . $alter->id . "and" . $alter2->id])) {
                            $fields[$question->title.":".$alter->name . " and " . $alter2->name] = $answers[$question->id . "-" . $alter->id . "and" . $alter2->id];
                        }
                    }
                }
            } else {
                $fields[$question->title] =  $answers[$question->id];
            }
        }

        $data = array(
            'user'=>array(
                'id'=>$_GET['user_id'],
                'fields'=>$fields,
            ),
        );

        return $this->sendResponse(200, $data);
    }

    public function actionSurveys()
    {
        $this->checkAPIheader();

        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->getSurveys();
                break;
            default:
                $this->sendResponse(405);
                break;
        }
    }

    private function getSurveys()
    {
        $studyIds = array();
        $questions = Question::find()
        ->where(new \yii\db\Expression('lower(title) = "mmic_prime_key"'))
        ->all();
        foreach ($questions as $question) {
            $studyIds[] = $question->studyId;
        }

        if (count($studyIds) == 0) {
            $msg = "No MMIC surveys found";
            return $this->sendResponse(419, $msg);
        }

        $studies = Study::findAll(array( 'id'=>$studyIds ));

        $data = array();

        foreach ($studies as $study) {
            $started = count(Interview::findAll(array("studyId"=>$study->id)));
            $completed = count(Interview::findAll(array("studyId"=>$study->id,"completed"=>-1)));
            $data[] = array(
                'id'=>$study->id,
                'name'=>$study->name,
                'closed'=> $study->closed_date ? date('m/d/Y', $study->closed_date) : null,
                'created'=> $study->created_date ? date('m/d/Y', $study->created_date) : null,
                'num_completed'=>$completed,
                'num_started'=>$started,
                'status'=>$study->status
            );
        }
        return $this->sendResponse(200, $data);
    }

    /**
     * @param $status
     * @return string
     */
    public static function getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     * @param int $status
     * @param string $body
     * @param string $content_type
     */
    public static function sendResponse($status = 200, $body = '', $content_type = 'application/json')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
        //header($status_header);
        // and the content type
        //header('Content-type: ' . $content_type);
        //\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // pages with body are easy
        if ($body != '') {
            // send the body
            if ($content_type == 'application/json') {
                if ($status != 200 && $status != 201) {
                    $body = array( 'error'=> $body );
                }
                return \Yii::createObject([
                    'class' => 'yii\web\Response',
                    'format' => \yii\web\Response::FORMAT_JSON,
                    'data' =>  $body,
                    'statusCode' => $status,
                    'statusText' => self::getStatusCodeMessage($status),
                ]);
            } else {
                return $body;
            }
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
						<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
						<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
							<title>' . $status . ' ' . self::getStatusCodeMessage($status) . '</title>
						</head>
						<body>
							<h1>' . self::getStatusCodeMessage($status) . '</h1>
							<p>' . $message . '</p>
							<hr />
							<address>' . $signature . '</address>
						</body>
						</html>';

            echo $body;
        }
    }
}
