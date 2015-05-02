<?php

$timeArray = Question::timeBits($question->timeUnits);
Yii::app()->clientScript->registerScript('timeSpanToValue-'.$array_id, "
jQuery('.time-".$array_id."').change(function() {
    var timespan = '';
    $('.time-".$array_id."').each(function(index){
        if($('.time-".$array_id.":eq('+index+')').val() != ''){
            timespan = timespan + ' ' + $('.time-".$array_id.":eq('+index+')').val() + ' '
                + $('.time-".$array_id.":eq('+index+')').attr('id');
        }
    });
    timespan = timespan + ' ';
    $('#Answer_".$array_id."_value').val(timespan);
});
");

preg_match("/(\d+) years/", $model[$array_id]->value, $years);
preg_match("/(\d+) months/", $model[$array_id]->value, $months);
preg_match("/(\d+) weeks/", $model[$array_id]->value, $weeks);
preg_match("/(\d+) days/", $model[$array_id]->value, $days);
preg_match("/(\d+) hours/", $model[$array_id]->value, $hours);
preg_match("/(\d+) minutes/", $model[$array_id]->value, $minutes);


if($rowColor != "" && $question->askingStyleList){
    $class = "multiRow";
}else{
    $class = "singleRow";
}

if($question->subjectType == "ALTER")
    $name = Alters::getName($question->alterId1);
elseif($question->subjectType == "ALTER_PAIR")
    $name = Alters::getName($question->alterId2);
else
	$name = $question->citation;

?>
        <?php if($rowColor != ""): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px; text-align:left; clear:both;'>
                <?php echo $name; ?>
            </div>
        <?php endif ?>
        <?php if(in_array("BIT_YEAR", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px'>
                <input type="text" class="time-<?php echo $array_id; ?>" id="years" style="width:30px" value="<?php if(isset($years[1])) echo $years[1]; ?>" />
                Years
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_MONTH", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px'>
                <td style="padding-left:4px; padding-right:0" ><input type="text" class="time-<?php echo $array_id; ?>" id="months" style="width:30px" value="<?php if(isset($months[1])) echo $months[1]; ?>" /></td>
                <td style="padding-left:0; padding-right:0;" align="left">Months</td>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_WEEK", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px'>
                <td style="padding-left:4px; padding-right:0;" ><input type="text" class="time-<?php echo $array_id; ?>" id="weeks" style="width:30px" value="<?php if(isset($weeks[1])) echo $weeks[1]; ?>" /></td>
                <td style="padding-left:0; padding-right:0;" align="left">Weeks</td>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_DAY", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px'>
                <td style="padding-left:4px; padding-right:0;" ><input type="text" class="time-<?php echo $array_id; ?>" id="days" style="width:30px" value="<?php if(isset($days[1])) echo $days[1]; ?>" /></td>
                <td style="padding-left:0; padding-right:0;" align="left">Days</td>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_HOUR", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px'>
                <td style="padding-left:4px; padding-right:0;"><input type="text" class="time-<?php echo $array_id; ?>" id="hours" style="width:30px" value="<?php if(isset($hours[1])) echo $hours[1]; ?>" /></td>
                <td style="padding-left:0; padding-right:0;" align="left">Hours</td>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_MINUTE", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px'>
                <td style="padding-left:4px; padding-right:0;"><input type="text" class="time-<?php echo $array_id; ?>" id="minutes" style="width:30px" value="<?php if(isset($minutes[1])) echo $minutes[1]; ?>" /></td>
                <td style="padding-left:0; padding-right:0;"align="left">Minutes</td>
            </div>
        <?php endif; ?>
<?php
        if(count($skipList) != 0){
            echo "<br style='clear:both'><div>".
				CHtml::checkBoxList($array_id."_skip", array($model[$array_id]->skipReason), $skipList, array('class'=>"answerInput " .$array_id.'-skipReason'))
				."</div>";
		}
echo $form->hiddenField($model[$array_id],  '['.$array_id.']value',array('value'=>$model[$array_id]->value, 'class'=>$array_id));
?>
