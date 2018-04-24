<?php
function seoString($string) {
	$string=strtolower($string);
	$string=preg_replace('/!*\'*\"*\.*,*\?*;*:*#*\(*\)*\[*\]*/','',$string);
	$string=preg_replace('/&/','and',$string);
	$string=preg_replace('/\//','-',$string);
	$string=preg_replace('/\s+/',' ',$string);
	$string=preg_replace('/\s/','-',$string);
	$string=preg_replace('/-+/','-',$string);
	if(substr($string,-1,1) == '-')
		$string = substr($string,0,-1);
	if(substr($string,0,1) == '-')
		$string = substr($string,1);
	return $string;
}

function sanitizeXml($string){
	return htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $string)), ENT_QUOTES, "UTF-8", false);
}

function sortByOrder($a, $b) {
    return $a['meta']['order'] - $b['meta']['order'];
}

function i($table, $columns){
	return Yii::app()->db->createCommand()->insert($table,$columns);
}

/**
 * @param $sql
 * @param array $params. Each element is an array with 3 attributes: name, value, & dataType.
 * See the section marked "Binding Parameters" here: http://www.yiiframework.com/doc/guide/1.1/en/database.dao
 * @return mixed
 */
function q($sql, $params=null){
	$cmd = Yii::app()->db->createCommand($sql);

    if(!empty($params)){
        foreach ($params as $param){
            $cmd->bindParam($param->name, $param->value, $param->dataType);
        }
    }

    return $cmd;
}

function d($table, $conditions){
	return Yii::app()->db->createCommand()->delete($table,$conditions);
}

function u($table, $columns, $conditions){
	return Yii::app()->db->createCommand()->update($table,$columns,$conditions);
}

function ftime($datetime,$format=null){
	if(!$format)
		$format="%b %e, %Y @ %I:%M %p";
	return strftime($format,strtotime($datetime));
}
function red($str){
    return '<span style="color:#f00">'.$str.'</span>';
}

function short($string, $max=60){
	if(strlen($string)>$max){
		if(strstr($string,':')){
			while(substr($string,-1,1)!=':')
				$string=substr($string,0,-1);
			$string=substr($string,0,-1);
		}else{
			if(strstr($string,' ')){
				while(substr($string,-1,1)!=' ')
					$string=substr($string,0,-1);
				$string=substr($string,0,-1);
			}
		}
	}
	return $string;
}

/**
 * @param $encrypted
 * @return mixed
 */
function decrypt( $encrypted ){
    if( strlen(trim( $encrypted )) < 1  ){
        return $encrypted;
    }

    $eKey = Yii::app()->getSecurityManager()->getEncryptionKey();
    $decrypted = Yii::app()->getSecurityManager()->decrypt(base64_decode( $encrypted), $eKey );

    return $decrypted;
}

/**
 * @param $decrypted
 * @return string
 */
function encrypt( $decrypted ){
    if( strlen(trim( $decrypted )) < 1  ){
        return $decrypted;
    }

    $eKey = Yii::app()->getSecurityManager()->getEncryptionKey();
    $encrypted = base64_encode(Yii::app()->getSecurityManager()->encrypt( $decrypted, $eKey ) );

    return $encrypted;
}

function mToA($models) {
    if (is_array($models))
        $arrayMode = TRUE;
    else {
        $models = array($models);
        $arrayMode = FALSE;
    }

    $result = array();
    foreach ($models as $model) {
        if (!is_object($model)) {
            return false;
        }
        $attributes = $model->getAttributes();
        $relations = array();
        foreach($attributes as $key=>$value){
            $attributes[strtoupper($key)] = $value;
            unset($attributes[$key]);
        }
        foreach ($model->relations() as $key => $related) {
            if ($model->hasRelated($key)) {
                $relations[$key] = convertModelToArray($model->$key);
            }
        }
        $all = array_merge($attributes, $relations);

        if ($arrayMode)
            array_push($result, $all);
        else
            $result = $all;
    }
    return $result;
}

function check_file_is_audio( $tmp )
{
    $allowed = array(
        'audio/mpeg', 'audio/x-mpeg', 'audio/mpeg3', 'audio/x-mpeg-3', 'audio/aiff',
        'audio/mid', 'audio/x-aiff', 'audio/x-mpequrl','audio/midi', 'audio/x-mid',
        'audio/x-midi','audio/wav','audio/x-wav','audio/xm','audio/x-aac','audio/basic',
        'audio/flac','audio/mp4','audio/x-matroska','audio/ogg','audio/s3m','audio/x-ms-wax',
        'audio/xm'
    );

    // check REAL MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($finfo, $tmp );
    finfo_close($finfo);

    // check to see if REAL MIME type is inside $allowed array
    if( in_array($type, $allowed) ) {
        return true;
    } else {
        return false;
    }
}
?>
