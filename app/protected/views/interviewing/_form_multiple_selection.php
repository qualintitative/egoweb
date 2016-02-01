<?php
// converts multiple selection checkboxes into answer value
Yii::app()->clientScript->registerScript('optionsToValue-'.$array_id, "
$('.multiselect-".$array_id."').change(function() {
	var values = $('#Answer_".$array_id."_value').val().split(',');
	current = $(this);
	$('#Answer_".$array_id."_skipReason').val('NONE');
	$('.answerInput.multiselect-".$array_id."').each(function() {
		if($(this).is(':checked')){
			$('#Answer_".$array_id."_otherSpecifyText').val('');
			$('#Answer_".$array_id."_otherSpecifyText').hide();
			$('#".$array_id."_other').prop('checked', false);
			$('.".$array_id."-skipReason').prop('checked', false);
			if(values.indexOf($(this).val()) == -1)
				values.push($(this).val());

            $('#otherSpecify_' + $(this).val()).show();
		}else{
			if(values.indexOf($(this).val()) != -1)
				values.splice(values.indexOf(current.val()),1);
            $('#otherSpecify_' + $(this).val()).val('');
            $('#otherSpecify_' + $(this).val()).hide();
		}
	});
	if(values.length > parseInt(".$question->maxCheckableBoxes.")){
		value = values.shift();
		$('input.multiselect-".$array_id."[value=\"' + value + '\"]').prop('checked', false);
		$('#otherSpecify_' + value).hide();
            $('#otherSpecify_' + value).val('');
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

$legacy = false;

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
	echo "<br clear=all><div counter='$counter' class='multiRow ".$rowColor."'><label style='width:180px; text-align:left'>".$name."</label>".CHtml::checkBoxList(
			'multiselect-'.$array_id,
			$selected,
			CHtml::listData($options, 'id', ''),
			array(
				'template'=>'{input}',
				'class'=>'answerInput multiselect-'.$array_id,
				'container'=>'',
				'template'=>'{input}',
				'separator'=>"",
				'style'=>"margin-left:" . intval($maxwidth * .4) ."px; width:" . intval($maxwidth * .6) ."px",
			)
		);

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
		echo "</div>";
}else{
    $otherValue = array();
    foreach($options as $option){
        if($option->otherSpecify){
            $otherSpecify = OtherSpecify::model()->findByAttributes(array("interviewId"=>$interviewId, "optionId"=>$option->id));
                if($otherSpecify)
                    $otherValue[$option->id] = $otherSpecify->value;
                else
                    $otherValue[$option->id] = "";
        }
    }
    
    if(count($otherValue) == 0){
        $legacy = true;
        foreach(preg_split('/;;/', $model[$array_id]->otherSpecifyText) as $other){
          	if($other && strstr($other, ':')){
        		list($key, $val) = preg_split('/:/', $other);
        		$otherValue[$key] = $val;
        	}
        }
    }
	echo CHtml::checkBoxList(
	    'multiselect-'.$array_id,
	    $selected,
	    CHtml::listData($options, 'id', function($data){
    	    if(file_exists(Yii::app()->basePath."/../audio/".$data->studyId . "/OPTION/" . $data->id . ".mp3"))
        	    $name = '<script>var optionAudio_' . $data->id . ' = loadAudio("/audio/' . $data->studyId  . "/OPTION/"  . $data->id . '.mp3");</script>'. "<a class=\"playSound\" onclick=\"playSound('/audio/" . $data->studyId  . "/OPTION/"  . $data->id . ".mp3')\" href=\"#\"><span class=\"fui-volume play-sound\"></span></a>";
            else
                $name = "";
            if($data->otherSpecify)
                $name .= CHtml::textField("otherSpecify[".$data->id."]","",array("class"=>"otherSpecify")) ;
	    	return $data->name . $name;
	   }),
	    array('class'=>'answerInput multiselect-'.$array_id)
	);
		if(count($skipList) != 0){
				echo "<div clear=all>".
					CHtml::checkBoxList($array_id."_skip", array($model[$array_id]->skipReason), $skipList, array('class'=>"answerInput " .$array_id.'-skipReason'))
					."</div>";
		}
	echo "<br>";
}

echo $form->hiddenField($model[$array_id],  '['.$array_id.']value',array('value'=>$model[$array_id]->value, 'class'=>$array_id));
echo $form->hiddenField($model[$array_id],  '['.$array_id.']otherSpecifyText',array('value'=>$model[$array_id]->otherSpecifyText));


?>
<script>
otherSpecify = JSON.parse('<?php echo json_encode($otherValue) ?>');
array_id = '<?php echo $array_id ?>';
$(function(){
<?php if ($legacy): ?>
    
	$('#multiselect-' + array_id + ' label').each(function(index){
		if($(this).html().match(/OTHER \(*SPECIFY\)*/i)){
			display = '';
			val = '';
			if($('#' + $(this).attr('for')).prop('checked') != true)
				display = 'style="display:none"';
			else
				val = otherSpecify[$('#' + $(this).attr('for')).val()];
			$(this).after(
			'<input id="' + $('#' + $(this).attr('for')).val() + '" class="' + array_id +'_other" ' + display+ ' onchange="changeOther('+array_id+')" value="'+  val + '" style="margin:5px"/>'
			);
			$('#' + $(this).attr('for')).click(function(){
				toggleOther($('#' + $(this).val()));
			});
		}
	});
});
<?php else: ?>
    $(".otherSpecify").each(function(index){
        if($(this).attr("id").match(/\d+/))
            var otherId = $(this).attr("id").match(/\d+/)[0];
        if(typeof otherSpecify[otherId] != "undefined")
            $(this).val(otherSpecify[otherId]);
        if($("input[type='checkbox'][value='" + otherId + "']").prop("checked") == true)
            $(this).show();
        else
            $(this).hide();
    });
<?php endif; ?>
</script>