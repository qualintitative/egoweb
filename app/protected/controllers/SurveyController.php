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

class SurveyController extends Controller
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

	public function beforeAction($action) {
		$this->enableCsrfValidation = false;	
		return parent::beforeAction($action);
	}

	public function actionIndex(){
		$input = null;

		//Look for payload as form value in the request
		if( array_key_exists( "payload", $_REQUEST ) ){
			$input = trim( $_REQUEST["payload"] );
		}
		//Otherwise get the file from the raw request body itself
		else{
			$input = trim( file_get_contents( 'php://input' ) );
		}
		if( empty( $input ) && !empty($_SERVER['QUERY_STRING']) ) {
			$queries = array();
			parse_str($_SERVER['QUERY_STRING'], $queries);
			if (!empty($queries['payload']))$input = $queries['payload'];
		}

		if( empty( $input ) ) {
			$msg = 'Missing input data';
			return ApiController::sendResponse( 419, $msg );
		}

		$decoded = json_decode( $input, true );

		if( empty( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode input:'.$input );
		}

		if( !is_array( $decoded ) ){
			return ApiController::sendResponse( 423, 'invalid input' );
		}

		if( !array_key_exists( "payload", $decoded ) ){
			return ApiController::sendResponse( 424, 'payload attribute not set' );
		}

		return $this->receive( $decoded["payload"] );
	}

	/**
	 *
	 */
	public function actionGetlink(){
		$input = file_get_contents('php://input');
		if(empty( $input ) ){
			return ApiController::sendResponse( 419, 'Missing payload' );
		}
		$decoded = json_decode( trim( $input ), true );
		if( !isset( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode payload' );
		}

		//test for the password and make sure it has been changed from the default
		if(empty( $decoded['password']) || $decoded['password'] != Yii::$app->params['APIPassword'] || Yii::$app->params['APIPassword'] == 'yourpasswordhere'){
			return ApiController::sendResponse( 401, 'Please provide a valid password to access this feature.' );
		}

		if( self::checkSurveyId($decoded['survey_id']) ){
            $link = $this->generateSurveyURL();
			$payload = $this->encryptPayload($decoded);
            return ApiController::sendResponse( 200, array( 'link'=>$link, 'payload'=>$payload ) );
        }
	}

	public function actionGetStatus(){
		$input = file_get_contents('php://input');
		if(empty( $input ) ){
			return ApiController::sendResponse( 419, 'Missing payload' );
		}
		$decoded = json_decode( trim( $input ), true );
		if( !isset( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode payload' );
		}

		if(empty( $decoded['password']) || $decoded['password'] != Yii::$app->params['APIPassword'] || Yii::$app->params['APIPassword'] == 'yourpasswordhere'){
			return ApiController::sendResponse( 401, 'Please provide a valid password to access this feature.' );
		}

		if (empty($decoded['user_id']) || empty($decoded['survey_id'])){
			return ApiController::sendResponse( 422, 'Missing user_id and/or survey_id');
		}

		$interview = Interview::getInterviewFromPrimekey($decoded['survey_id'], $decoded['user_id'],array());
		if (empty($interview)){
			return ApiController::sendResponse( 422, 'Invalid survey_id');
		}
		echo json_encode(array(
				'active' => $interview->active,
	            'completed' => $interview->completed,
	            'start_date' => $interview->start_date,
	            'complete_date' => $interview->complete_date,
				'status' => empty($interview->start_date) ? 'not started' : empty($interview->complete_date) ? 'started' : 'completed'
	        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	}

	public function actionGetData(){
		$input = file_get_contents('php://input');
		if(empty( $input ) ){
			return ApiController::sendResponse( 419, 'Missing payload' );
		}
		$decoded = json_decode( trim( $input ), true );
		if( !isset( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode payload' );
		}

		if(empty( $decoded['password']) || $decoded['password'] != Yii::$app->params['APIPassword'] || Yii::$app->params['APIPassword'] == 'yourpasswordhere'){
			return ApiController::sendResponse( 401, 'Please provide a valid password to access this feature.' );
		}

		if (empty($decoded['user_id']) || empty($decoded['survey_id'])){
			return ApiController::sendResponse( 422, 'Missing user_id and/or survey_id');
		}

		$interview = Interview::getInterviewFromPrimekey($decoded['survey_id'], $decoded['user_id'],array());
		if (empty($interview)){
			return ApiController::sendResponse( 422, 'Invalid survey_id');
		}
		$data = $interview->exportEgoAlterDataJSON();
		echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	}


	/**
	 * handles the request payload
	 * @param string $payload
	 * @return array
	 */
	public function receive( $payload ){
		$plain = Tools::decrypt( $payload);
		if( !isset( $plain ) ){
			return ApiController::sendResponse( 422, 'Unable to decrypt payload' );
		}

		$decoded = json_decode( trim( $plain ), true );
		if( !isset( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode payload' );
		}

		if( !array_key_exists ( 'action', $decoded ) ){
			return ApiController::sendResponse( 422, 'No action in payload' );
		}

		if( ( $decoded['action'] != 'login' ) && ( ( $decoded['action'] != 'passthrough' ) ) ){
			return ApiController::sendResponse( 422, 'Invalid action in payload' );
		}

		if( array_key_exists ( 'redirect', $decoded ) ){
			Yii::$app->session['redirect'] = $decoded['redirect'];
		}

		if( ( $decoded['action'] == 'login' ) ) {
			if( !array_key_exists ( 'email', $decoded ) ){
				return ApiController::sendResponse( 422, 'No email in payload' );
			}

			if( !array_key_exists ( 'password', $decoded ) ){
				return ApiController::sendResponse( 422, 'No password in payload' );
			}

			return $this->_handleLogin( $decoded['email'], $decoded['password'] );
		}

		if( ( $decoded['action'] == 'passthrough' ) ) {
			if( !array_key_exists ( 'user_id', $decoded ) ){
				return ApiController::sendResponse( 422, 'No user_id in payload' );
			}

			if( !array_key_exists ( 'survey_id', $decoded ) ){
				return ApiController::sendResponse( 422, 'No survey_id in payload' );
			}

			$prefill = null;
            $questions = array();

			if( array_key_exists ( 'prefill', $decoded ) ) $prefill = $decoded['prefill'];
			if( array_key_exists ( 'questions', $decoded ) ) $questions = $decoded['questions'];

            $this->createSurvey( $decoded['survey_id'], $decoded['user_id'], $prefill, $questions, $decoded['redirect']);
		}
	}

	/**
	 * @param $email
	 * @param $password
	 */
	private function _handleLogin( $email, $password ){
		$login = new LoginForm;
		$login->username = $email;
		$login->password = $password;
		if( $login->validate() && $login->login() ){
			if(Yii::$app->user->isGuest)
				$this->redirect(Url::to('/'));
			else
				$this->redirect(Url::to('admin/'));
		}
	}

	/**
	 * @return string
	 */
	public function generateSurveyURL( ){
		return Yii::$app->params['surveyURL'];
	}

	/**
	 * @param $payload
	 * @return string
	 */
	public function encryptPayload( $payload ){

		$plain = json_encode( $payload );
		$encrypted = Tools::encrypt( $plain );

		return $encrypted;
	}

    /**
     * @param $surveyId
     * @param $userId
     * @param null $prefill
     * @param null $redirect
     */
    public function createSurvey( $surveyId, $userId, $prefill=null, $questions=array(), $redirect=null ){

        $study = Study::findOne( $surveyId );
        if( !$study ){
            $msg = "Invalid survey_id";
            return ApiController::sendResponse( 418, $msg );
        }

        $interview = Interview::getInterviewFromPrimekey( $study->id, $userId, $prefill, $questions);

        if( !$interview ){
            $msg = "Unable to find user_id and/or survey_id combination";
            return ApiController::sendResponse( 404, $msg );
        }
        else if( $interview->completed == -1 ){
			if ($redirect){
				Yii::$app->response->redirect($redirect, 301)->send();
			}
            $msg = "User already completed survey";
            return ApiController::sendResponse( 420, $msg );
        }
        else{
            if( isset( $redirect ) )
                Yii::$app->session['redirect'] = $redirect;
				
            $url = Url::base(true);
			Yii::$app->response->redirect($url  .  "/interview/".$study->id."/".
				$interview->id.
				"#/page/".$interview->completed, 301
				)->send();
        }
    }

    public static function checkSurveyId($surveyId){
        $study = Study::findOne( $surveyId );
        if( !$study ){
            $msg = "Invalid survey_id";
            return ApiController::sendResponse( 418, $msg );
        }
        return True;
    }

}
