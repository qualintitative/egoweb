<?php
class ApiController extends Controller
{
	// Members
	/**
	 * Key which has to be in HTTP USERNAME and PASSWORD headers
	 */
	Const APPLICATION_ID = 'EGOWEB';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
	    return array();
	}

    private function checkAPIheader(){
        $headers = array();
        foreach( $_SERVER as $key => $value ) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace( ' ', '-', str_replace( '_', ' ', strtolower( substr( $key, 5 ) ) ) );
            $headers[$header] = $value;
        }

        if( !isset( $headers['api-key'] ) ){
            $this->_sendResponse( 422, "Missing API Key" );
            exit();
        }

        if( $headers['api-key'] != Yii::app()->params['apiKey'] ){
            $this->_sendResponse( 421, "Invalid API Key" );
            exit();
        }
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
            default:
                $this->_sendResponse( 405 );
                break;
        }
    }

    private function createSurvey(){
		if( !isset($_POST['survey_id']) || !isset($_POST['user_id'] ) ){
			$msg = "Missing survey_id and/or user_id parameter";
			return $this->_sendResponse( 419, $msg );
		}

        $study = Study::model()->findByPk( (int)$_POST['survey_id'] );
        if( !$study ){
            $msg = "Invalid survey_id";
            return $this->_sendResponse( 418, $msg );
        }

        $interview = Interview::getInterviewFromPrimekey( $study->id, $_POST['user_id'] );

        if( !$interview ){
            $msg = "Unable to find user_id and/or survey_id combination";
            return $this->_sendResponse( 404, $msg );
        }
        else if( $interview->completed == -1 ){
            $msg = "User already completed survey";
            return $this->_sendResponse( 420, $msg );
        }
        else{
            $data = array(
                            'redirect_url'=>Yii::app()->createUrl(  'interviewing/'.$study->id.'?'.
                                                                    'interviewId='.$interview->id.'&'.
                                                                    'page='.$interview->completed )
                    );
            return $this->_sendResponse( 201, $data );
        }
	}

    /**
     * @todo fill in 'fields' response attribute
     */
    private function getSurvey()
	{
		if( !isset( $_GET['survey_id'] ) ){
			$msg = "Missing survey_id parameter";
			$this->_sendResponse( 419, $msg );
		}
		else{
			$study = Study::model()->findByPK((int)$_GET['survey_id']);
			if(!$study){
				$msg = "Survey: ".$_GET['survey_id'] . " not found";
				$this->_sendResponse( 404, $msg );
			}
			$data = array(
                        'survey'=>array(
                            'id'=>$study->id,
                            'name'=>$study->name,
                            'closed'=> $study->closed_date ? date('m/d/Y', $study->closed_date) : null,
                            'created'=> $study->created_date ? date('m/d/Y', $study->created_date) : null,
                            'num_completed'=>$study->completed,
                            'num_started'=>$study->started,
                            'status'=>$study->status,
                            'fields'=>array()
                        ),
			        );
			$this->_sendResponse( 200, $data );
		}
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
                $this->_sendResponse( 405 );
                break;
        }
    }

    public function getUser()
	{
		if( !isset( $_GET['user_id'] ) ){
			$msg = "Missing user_id parameter";
			$this->_sendResponse( 419, $msg );
		}
		else{
			$questionIds = q( "SELECT id FROM question where lower(title) = 'mmic_prime_key'" )->queryColumn();
            $interviews = Answer::model()->findAllByAttributes( array( 'questionId'=>$questionIds ) );

            $surveys_completed = array();
            $surveys_started = array();
            $userFound = false;

			foreach( $interviews as $intv ){
                if( $intv->value == $_GET['user_id'] ){
                    $userFound = true;
				    $interview = Interview::model()->findByPk( $intv->interviewId );
                    $study = Study::model()->findByPk( $intv->studyId );
                    if( $interview->complete_date )
                        $surveys_completed[$study->id] = date( 'm/d/Y',$interview->complete_date );
                    if( $interview->start_date )
                        $surveys_started[$study->id] = date( 'm/d/Y',$interview->start_date );
                }
			}

            if( !$userFound ){
                $msg = $_GET['user_id'] . " not found";
                $this->_sendResponse(404, $msg );
            }

			$data = array(
				'user'=>array(
					'id'=>$_GET['user_id'],
					'surveys_completed'=>$surveys_completed,
					'surveys_started'=>$surveys_started,
				),
			);
			$this->_sendResponse( 200, $data );
		}
	}

    /**
     * @param $status
     * @return string
     */
    private function _getStatusCodeMessage( $status )
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
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
		return ( isset( $codes[$status] ) ) ? $codes[$status] : '';
	}

    /**
     * @param int $status
     * @param string $body
     * @param string $content_type
     */
    private function _sendResponse( $status = 200, $body = '', $content_type = 'application/json' )
	{
		// set the status
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
		header($status_header);
		// and the content type
		header('Content-type: ' . $content_type);

		// pages with body are easy
		if($body != '')
		{
			// send the body
            if( $content_type == 'application/json' ){
                if( $status != 200 && $status != 201 ){
                    $body = array( 'error'=> $body );
                }
                echo json_encode( $body );
            }
            else{
                echo $body;
            }
		}
		// we need to create the body if none is passed
		else
		{
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
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
                            <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                        </head>
                        <body>
                            <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                            <p>' . $message . '</p>
                            <hr />
                            <address>' . $signature . '</address>
                        </body>
                        </html>';

            echo $body;
		}
		Yii::app()->end();
	}
}
