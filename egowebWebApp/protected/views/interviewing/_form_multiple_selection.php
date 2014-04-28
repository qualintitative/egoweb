<?php
// converts multiple selection checkboxes into answer value
Yii::app()->clientScript->registerScript('optionsToValue-'.$array_id, "
$('.multiselect-".$array_id."').change(function() {
	var values = $('#Answer_".$array_id."_value').val().split(',');
		current = $(this);
	$('#Answer_".$array_id."_skipReason').val('NONE');
	$('.multiselect-".$array_id."').each(function() {
		if($(this).is(':checked')){
			$('#Answer_".$array_id."_otherSpecifyText').val('');
			$('#Answer_".$array_id."_otherSpecifyText').hide();
			$('#".$array_id."_other').prop('checked', false);
			$('.".$array_id."-skipReason').prop('checked', false);
			if(values.indexOf($(this).val()) == -1)
				values.push($(this).val());
		}else{
			if(values.indexOf($(this).val()) != -1)
				values.splice(values.indexOf(current.val()),1);
		}
	});
	if(values.length > parseInt(".$question->maxCheckableBoxes.")){
		value = values.shift();
		$('input.multiselect-".$array_id."[value=\"' + value + '\"]').prop('checked', false);
	}
	$('#Answer_".$array_id."_value').val(values.join(','));
});
");


// explode already selected options (if any) into array
$selected = explode(',', $model[$array_id]->value);
$options = QuestionOption::model()->findAllByAttributes(array('questionId'=>$question->id), $params=array('order'=>'ordering'));

$skipList = array();
if($question->dontKnowButton)
	$skipList['DONT_KNOW'] = ($rowColor && $question->askingStyleList) ? "" : "Don't Know";
if($question->refuseButton)
	$skipList['REFUSE'] = ($rowColor && $question->askingStyleList) ? "" : "Refuse";


if($rowColor != "" && $question->askingStyleList){
	$columns = count($options) + count($skipList);
	$maxwidth = 180;
	if($columns != 0)
		$maxwidth = intval(620 / $columns);
	if($maxwidth > 180)
		$maxwidth = 180;

	if($question->subjectType == "ALTER")
		$name = Alters::getName($question->alterId1);
	elseif($question->subjectType == "ALTER_PAIR")
		$name = Alters::getName($question->alterId2);
	else
		$name = $question->citation;
	echo "<br clear=all><div class='multiRow ".$rowColor."' style='width:180px; text-align:left'>".$name."</div><div class='multiRow ".$rowColor."' style='width:".$maxwidth."px'>".CHtml::checkBoxList(
			'multiselect-'.$array_id,
			$selected,
			CHtml::listData($options, 'id', ''),
			array(
				'class'=>'multiselect-'.$array_id,
				'container'=>'',
				'separator'=>"</div><div class='multiRow ".$rowColor."'  style='width:".$maxwidth."px'>",
			)
		) . "</div>";
}else{
	echo CHtml::checkBoxList(
	    'multiselect-'.$array_id,
	    $selected,
	    CHtml::listData($options, 'id', function($data){
	    	return $data->name .(file_exists(Yii::app()->basePath."/../audio/".$data->studyId . "/OPTION/" . $data->id . ".mp3") ? '<script>var optionAudio_' . $data->id . ' = loadAudio("/audio/' . $data->studyId  . "/OPTION/"  . $data->id . '.mp3");</script>'. "<a class=\"playSound\" onclick=\"playSound('/audio/" . $data->studyId  . "/OPTION/"  . $data->id . ".mp3')\" href=\"#\"><span class=\"fui-volume play-sound\"></span></a>": "");}),
	    array('class'=>'multiselect-'.$array_id)
	);
	echo "<br>";
}

echo $form->hiddenField($model[$array_id],  '['.$array_id.']value',array('value'=>$model[$array_id]->value, 'class'=>$array_id));
echo $form->hiddenField($model[$array_id],  '['.$array_id.']otherSpecifyText',array('value'=>$model[$array_id]->otherSpecifyText));

$otherValue = array();
foreach(preg_split('/;;/', $model[$array_id]->otherSpecifyText) as $other){
  	if($other && strstr($other, ':')){
		list($key, $val) = preg_split('/:/', $other);
		$otherValue[$key] = $val;
	}
}
?>
<script>
$(function(){
	array_id = '<?php echo $array_id ?>';
	otherValue = <?php echo json_encode($otherValue); ?>;
	$('#multiselect-' + array_id + ' label').each(function(index){
		if($(this).html().match(/OTHER SPECIFY/i)){
			display = '';
			val = '';
			if($('#' + $(this).attr('for')).prop('checked') != true)
				display = 'style="display:none"';
			else
				val = otherValue[$('#' + $(this).attr('for')).val()];
			$(this).after(
			'<input id="' + $('#' + $(this).attr('for')).val() + '" class="' + array_id +'_other" ' + display+ ' onchange="changeOther('+array_id+')" value="'+  val + '" style="margin:5px"/>'
			);
			$('#' + $(this).attr('for')).click(function(){
				toggleOther($('#' + $(this).val()));
			});
		}
	});
});
</script>