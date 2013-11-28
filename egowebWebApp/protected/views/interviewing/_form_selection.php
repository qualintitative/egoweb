<?php
Yii::app()->clientScript->registerScript('optionsToValue-'.$array_id, "
jQuery('.multiselect-".$array_id."').change(function() {
	$('.multiselect-".$array_id."').each(function() {
		if($(this).is(':checked')){
	        $('.".$array_id."-skipReason').prop('checked', false);
	        $('#Answer_".$array_id."_skipReason').val('NONE');
	       }
	});
});
");

$options = QuestionOption::model()->findAllByAttributes(array('questionId'=>$question->id), $params=array('order'=>'ordering'));

if($rowColor != "" && $question->askingStyleList){
    $columns = count($options) + count($skipList);
    $maxwidth = intval(620 / $columns);
    if($maxwidth > 180)
    	$maxwidth = 180;
    if($question->subjectType == "ALTER")
    	$name = Alters::getName($question->alterId1);
    elseif($question->subjectType == "ALTER_PAIR")
    	$name = Alters::getName($question->alterId2);
    else
    	$name = $question->citation;
    echo "<br clear=all><div class='multiRow ".$rowColor."' style='width:180px;  text-align:left'>".$name."</div><div class='multiRow ".$rowColor."' style='width:".$maxwidth."px'>".
    	$form->radioButtonList(
    	$model[$array_id],
    	'['.$array_id.']value',
    	CHtml::listData($options, 'id', ''),
    	array('class'=>'multiselect-'.$array_id, 'container'=>'', 'separator'=>"</div><div class='multiRow ".$rowColor."'  style='width:".$maxwidth."px'>")
    ) . "</div>";
}else{
    echo $form->radioButtonList(
    	$model[$array_id],
    	'['.$array_id.']value',
    	CHtml::listData($options, 'id', 'name')
    );
    echo "<br>";
}

if($question->otherSpecify){
	if($rowColor == ""){
		echo $form->labelEx($model[$array_id],'otherSpecifyText');
		echo $form->textField($model[$array_id],  '['.$array_id.']otherSpecifyText');
	} else {
		echo "<div class='multiRow ".$rowColor."'>Other ".  $form->textField($model[$array_id],  '['.$array_id.']otherSpecifyText') ."</div>";
	}
}
?>