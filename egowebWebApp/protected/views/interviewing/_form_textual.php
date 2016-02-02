<?php
$maxwidth = 180;
Yii::app()->clientScript->registerScript('focus-'.$array_id, "
$('#Answer_".$array_id."_value').change(function(){
	if($('#Answer_".$array_id."_value').val() != ''){
		$('.".$array_id."-skipReason').prop('checked', false);
		$('#Answer_".$array_id."_skipReason').val('NONE');
	}
});
");
if($question->subjectType == "EGO_ID" && $question->useAlterListField != ""){
	$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
		'model' => $model[$array_id],
		'name' => 'Answer['.$array_id.'][value]',
		'value' => $model[$array_id]->value,
		'source'=>'js: function(request, response) {
			$.ajax({
				url: "'.$this->createUrl('/interviewing/autocomplete').'",
				dataType: "json",
				data: {
					term: request.term,
					field: "'.$question->useAlterListField.'",
					studyId: "'.$question->studyId.'"
				},
				success: function (data) {
					response(data);
				}
			})
		}',
		'options' => array(
			'class'=>'answerInput',
			'minLength' => 1,
			'select' => "js:function(event, ui) {
				$('#Answer_".$array_id." _value').val(ui.item.name);
			}",
		)
	));
	echo "<br clear=all>";
}else{
	if($rowColor != "" && $question->askingStyleList){
		if($question->subjectType == "ALTER")
			$name = Alters::getName($question->alterId1);
		elseif ($question->subjectType == "ALTER_PAIR")
			$name = Alters::getName($question->alterId2);
		else
			$name = $question->citation;
		echo "<div class='multiRow ".$rowColor."' style='width:180px;  text-align:left'>".$name."</div>";
		echo "<div class='multiRow ".$rowColor."'>" . $form->textField($model[$array_id], '['.$array_id.']'.'value',array('class'=>$array_id)) . "</div>";
		if(count($skipList) != 0){
			echo CHtml::checkBoxList(
				$array_id."_skip",
				array($model[$array_id]->skipReason),
				$skipList,
				array('class'=>$array_id.'-skipReason',
					'container'=>'',
					'separator'=>"",
					'template'=>'{input}',
					'style'=>"margin-left:" . intval($maxwidth * .4) ."px; width:" . intval($maxwidth * .6) ."px",
				)
			);
		}
		echo "<br clear=all>";
	}else{
		echo $form->textField($model[$array_id], '['.$array_id.']'.'value',array('class'=>$array_id . " answerInput")) . "<br style='clear:both'>";
		if(count($skipList) != 0){
            echo "<br style='clear:both'><div>".
					CHtml::checkBoxList($array_id."_skip", array($model[$array_id]->skipReason), $skipList, array('class'=>"answerInput " .$array_id.'-skipReason'))
					."</div>";
		}
	}
}

?>

