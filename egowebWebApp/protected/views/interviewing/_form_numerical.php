<?php
Yii::app()->clientScript->registerScript('focus-'.$array_id, "
$('#Answer_".$array_id."_value').change(function(){
	if($('#Answer_".$array_id."_value').val() != ''){
		$('.".$array_id."-skipReason').prop('checked', false);
		$('#Answer_".$array_id."_skipReason').val('NONE');
	}
});
");
if($rowColor != "" && $question->askingStyleList){
	if($question->subjectType == "ALTER")
		$name = Alters::getName($question->alterId1);
	elseif($question->subjectType == "ALTER_PAIR")
		$name = Alters::getName($question->alterId2);
	else
		$name = $question->citation;
	echo "<div class='multiRow ".$rowColor."' style='width:180px;  text-align:left'>".$name."</div>";
	echo "<div class='multiRow ".$rowColor."'>" . $form->textField($model[$array_id], '['.$array_id.']'.'value',array('class'=>$array_id));
	echo "</div>";
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
	echo $form->textField($model[$array_id], '['.$array_id.']value',array('class'=>'answerInput','style'=>'width:100px'));
		if(count($skipList) != 0){
            echo "<br style='clear:both'><div>".
					CHtml::checkBoxList($array_id."_skip", array($model[$array_id]->skipReason), $skipList, array('class'=>"answerInput " .$array_id.'-skipReason'))
					."</div>";
		}
	echo "<br style='clear:both'>";
}

?>