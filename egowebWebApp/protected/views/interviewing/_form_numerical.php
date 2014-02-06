<?php 
Yii::app()->clientScript->registerScript('focus-'.$array_id, "
jQuery(document).ready(function(){
	$('#Answer_".$array_id."_value').focus();
});
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
}else{
	echo $form->textField($model[$array_id], '['.$array_id.']value',array('style'=>'width:100px'));
	echo "<br style='clear:both'>";
}
?>