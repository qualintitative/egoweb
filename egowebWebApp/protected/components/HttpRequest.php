<?php
class HttpRequest extends CHttpRequest {
    public $noCsrfValidationRoutes = array();

    protected function normalizeRequest() {

        parent::normalizeRequest();
        if ($this->getIsPostRequest()) {
            if($this->enableCsrfValidation &&  $this->checkPaths() !== false)
                Yii::app()->detachEventHandler('onbeginRequest',array($this,'validateCsrfToken'));
        }
    }

    private function checkPaths() {

        foreach ($this->noCsrfValidationRoutes as $checkPath) {
            // allows * in check path
            if(strstr($checkPath, "*")) {
                $pos = strpos($checkPath, "*");
                $checkPath = substr($checkPath, 0, $pos);
                if(strstr($this->pathInfo, $checkPath)) {
                    return true;
                }
            } else {
                if($this->pathInfo == $checkPath) {
                    return true;
                }
            }
        }
        return false;
    }
}
