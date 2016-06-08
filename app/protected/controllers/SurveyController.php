<?php
/**
 * Created by PhpStorm.
 * User: sdrulea
 * Date: 12/22/14
 * Time: 5:42 PM
 */

Yii::import('ext.httpclient.*');

class SurveyController extends Controller {

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

		if( empty( $input ) ) {
			$msg = 'Missing input data';
			return ApiController::sendResponse( 419, $msg );
		}

		$decoded = json_decode( $input, true );
		if( empty( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode input' );
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
	public function actionGetLink(){
		$input = file_get_contents('php://input');

		if( !isset( $input ) ){
			$msg = 'Missing payload';
			return ApiController::sendResponse( 419, $msg );
		}

		$decoded = json_decode( trim( $input ), true );
		if( !isset( $decoded ) ){
			return ApiController::sendResponse( 422, 'Unable to decode payload' );
		}

		$link = $this->generateSurveyURL(  );
		$payload = $this->encryptPayload( $decoded );

		return ApiController::sendResponse( 200, array( 'link'=>$link, 'payload'=>$payload ) );
	}

	/**
	 * handles the request payload
	 * @param string $payload
	 * @return array
	 */
	public function receive( $payload ){
		$plain = decrypt( $payload);
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
			Yii::app()->session['redirect'] = $decoded['redirect'];
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
			if(Yii::app()->user->isGuest)
				$this->redirect(Yii::app()->createUrl(''));
			else
				$this->redirect(Yii::app()->createUrl('admin/'));
		}
	}

	/**
	 * @return string
	 */
	public function generateSurveyURL( ){
		return Yii::app()->params['surveyURL'];
	}

	/**
	 * @param $payload
	 * @return string
	 */
	public function encryptPayload( $payload ){

		$plain = json_encode( $payload );
		$encrypted = encrypt( $plain );

		return $encrypted;
	}

    /**
     * @param $surveyId
     * @param $userId
     * @param null $prefill
     * @param null $redirect
     */
    public static function createSurvey( $surveyId, $userId, $prefill=null, $questions=array(), $redirect=null ){

        $study = Study::model()->findByPk( $surveyId );
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
            $msg = "User already completed survey";
            return ApiController::sendResponse( 420, $msg );
        }
        else{
            if( isset( $redirect ) )
                Yii::app()->session['redirect'] = $redirect;

            Yii::app()->request->redirect(Yii::app()->getBaseUrl(true)  .  "/interview/".$study->id."/".
                            $interview->id."/".
                            "#/page/".$interview->completed
                            );
        }
    }

}
