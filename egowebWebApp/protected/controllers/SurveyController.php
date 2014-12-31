<?php
/**
 * Created by PhpStorm.
 * User: sdrulea
 * Date: 12/22/14
 * Time: 5:42 PM
 */

class SurveyController extends Controller {
    private $privateKey;
    private $publicKey;
    private $iv_size;

    public function init(){
        $this->publicKey =  openssl_get_publickey( Yii::app()->params['surveyPublicKey'] );

        if( empty( $this->publicKey ) ){
            throw new \Exception('Public key invalid:' . openssl_error_string());
        }

        $this->privateKey = openssl_get_privatekey( Yii::app()->params['surveyPrivateKey'], Yii::app()->params['surveyEncryptionPassword'] );

        if( empty( $this->publicKey ) ){
            throw new \Exception('Private key invalid:' . openssl_error_string());
        }

        $this->iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    }

    public function actionIndex(){
        if( !isset( $_REQUEST['payload'] ) ){
            $msg = 'Missing payload parameter';
            return ApiController::sendResponse( 419, $msg );
        }

        if( !isset( $_REQUEST['password'] ) ){
            $msg = 'Missing payload parameter';
            return ApiController::sendResponse( 419, $msg );
        }

        return $this->receive( $_REQUEST['payload'], $_REQUEST['password'] );
    }

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

        $link = $this->generateRequestString( $decoded );

        return ApiController::sendResponse( 200, array( 'link'=>$link) );
    }

    /**
     * handles the request payload
     * @param string $payload
     * @return array
     */
    public function receive( $payload, &$password ){
        $password = $this->asymDecrypt( base64_decode( html_entity_decode( $password ) ) );
        if( !isset( $password ) ){
            return ApiController::sendResponse( 422, 'Unable to decrypt password' );
        }

        $plain = $this->decrypt( $payload, $password );
        if( !isset( $password ) ){
            return ApiController::sendResponse( 422, 'Unable to decrypt payload' );
        }

        $decoded = json_decode( trim( $plain ), true );
        if( !isset( $decoded ) ){
            return ApiController::sendResponse( 422, 'Unable to decode payload' );
        }

        if( !array_key_exists ( 'action', $decoded ) ){
            return ApiController::sendResponse( 422, 'No action key in payload' );
        }

        if( !array_key_exists ( 'email', $decoded ) ){
            return ApiController::sendResponse( 422, 'No email key in payload' );
        }

        if( !array_key_exists ( 'password', $decoded ) ){
            return ApiController::sendResponse( 422, 'No password key in payload' );
        }

        return $this->handleLogin( $decoded['email'], $decoded['password'] );
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
            $this->redirect( $this->createUrl('/admin') );
        }
    }

    /**
     * @param $encrypted
     * @return mixed
     */
    private function asymDecrypt($encrypted){
        $result = openssl_private_decrypt( $encrypted, $plain, $this->privateKey );

        return $plain;
    }

    /**
     * @param $plain
     * @return mixed
     */
    private function asymEncrypt($plain){
        $res = openssl_public_encrypt($plain, $encrypted, $this->publicKey);
        return $encrypted;
    }

    /**
     * @param $encrypted
     * @param $key
     * @return string
     */
    private function decrypt($encrypted, $key){
        $ciphertext_dec = base64_decode($encrypted);
        # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 0, $this->iv_size);

        # retrieves the cipher text (everything except the $iv_size in the front)
        $ciphertext_dec = substr($ciphertext_dec, $this->iv_size);

        # may remove 00h valued characters from end of plain text
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key,$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

        return $plaintext_dec;
    }

    /**
     * @param $l
     * @param string $c
     * @return string
     */
    function mt_rand_str ($l, $c = '0123456789ABCDEF') {
        for ($s = '', $cl = strlen($c)-1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i);
        return $s;
    }

    /**
     * @param $payload
     * @return string
     */
    public function generateRequestString( $payload ){

        $password = pack('H*', $this->mt_rand_str(64));

        $plain = json_encode( $payload );
        $content = $this->encrypt( $plain,$password );
        $p = base64_encode( $this->asymEncrypt( $password ) );

        $data = http_build_query( array( 'password' => $p, 'payload' => $content ) );

        return 'http://'.$_SERVER['HTTP_HOST'].'/survey?'.$data;
    }

    /**
     * @param $plain
     * @param $key
     * @param bool $prependIV
     * @param string $mode
     * @return string
     */
    public static function encrypt( $plain, $key, $prependIV = true, $mode = MCRYPT_MODE_CBC ){
        if (empty($plain)){
            $plain = '';
        }

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, $mode);

        # create a random IV to use with CBC encoding
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        # creates a cipher text to keep the text confidential
        # only suitable for encoded input that never ends with value 00h
        # (because of default zero padding)
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plain, $mode, $iv);

        if ($prependIV){
            # prepend the IV for it to be available for decryption
            $ciphertext = $iv . $ciphertext;
        }
        # encode the resulting cipher text so it can be represented by a string

        return base64_encode($ciphertext);
    }

} 
