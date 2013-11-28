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



function sortByOrder($a, $b) {
    return $a['meta']['order'] - $b['meta']['order'];
}

function i($table, $columns){
	return Yii::app()->db->createCommand()->insert($table,$columns);
}

function q($sql){
	return Yii::app()->db->createCommand($sql);
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
?>
