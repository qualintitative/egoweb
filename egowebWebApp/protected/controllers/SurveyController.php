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
        $input = file_get_contents('php://input');

        if( !isset( $input ) ){
            $msg = 'Missing input data';
            return ApiController::sendResponse( 419, $msg );
        }

        $decoded = json_decode( trim( $input ), true );
        if( !isset( $decoded ) ){
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
     * Redirect with POST data.
     *
     * @param string $url URL.
     * @param array $post_data POST data. Example: array('foo' => 'var', 'id' => 123)
     * @param array $headers Optional. Extra headers to send.
     * @return string
     */
    public function redirectPost($url, array $data, array $headers = null) {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        if (!is_null($headers)) {
            $params['http']['header'] = '';
            foreach ($headers as $k => $v) {
                $params['http']['header'] .= "$k: $v\n";
            }
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            return @stream_get_contents($fp);
        } else {
            // Error
            return ApiController::sendResponse( 500, 'Unable to access survey' );
            exit();
        }
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

        if( ( $decoded['action'] != 'login' ) && ( ( $decoded['action'] != 'passthrough' ) )  ){
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

            return $this->handleLogin( $decoded['email'], $decoded['password'] );
        }

        if( ( $decoded['action'] == 'passthrough' ) ) {
            if( !array_key_exists ( 'user_id', $decoded ) ){
                return ApiController::sendResponse( 422, 'No user_id in payload' );
            }

            if( !array_key_exists ( 'survey_id', $decoded ) ){
                return ApiController::sendResponse( 422, 'No survey_id in payload' );
            }

            return $this->handlePassthrough( $decoded['user_id'], $decoded['survey_id'] );
        }
    }

    /**
     * @param $email
     * @param $password
     */
    private function handleLogin( $email, $password ){
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
     * @param $userId
     * @param $surveyId
     */
    private function handlePassthrough( $userId, $surveyId ){
        $response = $this->redirectPost(   'http://'.$_SERVER['HTTP_HOST'].'/api/survey',
                                            array( 'user_id'=>$userId, 'survey_id'=>$surveyId ),
                                            array( 'api_key'=>Yii::app()->params['apiKey'] ) );

        $decoded = json_decode( $response );

        if (!($decoded)){
            return ApiController::sendResponse( 500, 'Invalid survey response' );
        }elseif( !array_key_exists ( 'redirect_url', $decoded )){
            return ApiController::sendResponse( 500, 'Invalid survey redirect' );
        }
        $this->redirect( $this->createUrl( $decoded->redirect_url ) );
    }

    /**
     * @return string
     */
    public function generateSurveyURL( ){
        return 'http://'.$_SERVER['HTTP_HOST'].'/survey';
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

} 
