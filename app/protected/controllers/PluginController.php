<?php
// David's custom plugins controller
class PluginController extends Controller
{
	public function actions()
	{
		$id = "";
		$plugin = substr(Yii::app()->getRequest()->getRequestUri(),8);
		if(strstr($plugin, '?'))
			list($plugin, $vars) = explode('?', $plugin);
		if(strstr($plugin, '/')){
			$segment = explode('/', $plugin);
			$plugin = $segment[0];
			$method = $segment[1];
			if(isset($segment[2]))
				$id = $segment[2];
		}else{
			$method = "";
		}
		return array(
			$plugin=>array(
				'class'=>'plugins.'.$plugin,
				'method'=>$method,
				'id'=>$id,
			)
		);
	}

}