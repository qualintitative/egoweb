<?php

$timeArray = Question::timeBits($question->timeUnits);
Yii::app()->clientScript->registerScript('timeSpanToValue-'.$array_id, "
jQuery('.time-".$array_id."').change(function() {
    item = '.time-".$array_id."';
    if($(item + '#minute').val() != '' && parseInt($(item + '#minute').val()) < 10)
    	$(item + '#minute').val('0' + parseInt($(item + '#minute').val()));
	date = '';
	if(typeof $(item + '#month option:selected').val() != 'undefined')
		date = $(item + '#month option:selected').val() + ' ';
	if(typeof $(item + '#day').val() != 'undefined')
    	date = date + $(item + '#day').val() + ' ';
    if(typeof $(item + '#year').val() != 'undefined')
        date = date + $(item + '#year').val() + ' ';
    if(typeof $(item + '#hour').val() != 'undefined'){
        date = date + $(item + '#hour').val() + ':';
        date = date + ($(item + '#minute').val() ? $(item + '#minute').val() : '00');
        date = date + ' ' + $(item + '#ampm:checked').val();
    }
    $('#Answer_".$array_id."_value').val(date);
});
");

preg_match("/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/", $model[$array_id]->value, $date);
preg_match("/(\d{1,2}):(\d{1,2}) (AM|PM)/", $model[$array_id]->value, $time);

$class = "multiRow";
$months = array(
    "January" => "January",
    "February" => "February",
    "March" => "March",
    "April" => "April",
    "May" => "May",
    "June" => "June",
    "July" => "July",
    "August" => "August",
    "September" => "September",
    "October" => "October",
    "November" => "November",
    "December" => "December"
);

if(isset($date[1]))
    $theMonth = $date[1];
else
    $theMonth = "";

if($question->subjectType == "ALTER")
    $name = Alters::getName($question->alterId1);
elseif($question->subjectType == "ALTER_PAIR")
    $name = Alters::getName($question->alterId2);
else
	$name = $question->citation;
?>
        <?php if($rowColor != "" && $question->askingStyleList): ?>
            <div class='<?php echo $class . " " . $rowColor ?>' style='width:180px; text-align:left;'>
                <?php echo $name; ?>
            </div>
        <?php endif ?>

        <?php if(in_array("BIT_HOUR", $timeArray) && in_array("BIT_MINUTE", $timeArray)): ?>

        <div class='<?php echo $class . " " . $rowColor ?>'>Time (HH:MM)</div>
        <?php endif; ?>

        <?php if(in_array("BIT_HOUR", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>
                <div style="padding-left:4px; padding-right:0;"><input type="text" class="time-<?php echo $array_id; ?>" id="hour" style="width:30px" value="<?php if(isset($time[1])) echo $time[1]; ?>" /></div>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_MINUTE", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>
                <div style="float:left; padding-left:4px; padding-right:0;"><input type="text" class="time-<?php echo $array_id; ?>" id="minute" style="width:30px" value="<?php if(isset($time[2])) echo $time[2]; ?>" /></div>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_HOUR", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>
        <input id="ampm" class="time-<?php echo $array_id; ?>" name="ampm-<?php echo $array_id; ?>" value="AM" type="radio" <?php if((isset($time[3]) && $time[3] == "AM") || !isset($time[3])) echo "checked"; ?>> AM </input>
        <input id="ampm" class="time-<?php echo $array_id; ?>" name="ampm-<?php echo $array_id; ?>" value="PM" type="radio" <?php if(isset($time[3]) && $time[3] == "PM") echo "checked"; ?>> PM </input>
            </div>
        <?php endif; ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>(Month,Day,Year)</div>
        <?php if(in_array("BIT_MONTH", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>
                <?php echo CHtml::dropDownList(
                "month",
                $theMonth,
                $months,
                array('class'=>"time-".$array_id)
                );?>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_DAY", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>
                <td style="padding-left:4px; padding-right:0;" ><input type="text" class="time-<?php echo $array_id; ?>" id="day" style="width:30px" value="<?php if(isset($date[2])) echo $date[2]; ?>" /></td>
            </div>
        <?php endif; ?>
        <?php if(in_array("BIT_YEAR", $timeArray)): ?>
            <div class='<?php echo $class . " " . $rowColor ?>'>
                <input type="text" class="time-<?php echo $array_id; ?>" id="year" style="width:60px" value="<?php if(isset($date[3])) echo $date[3]; ?>" />
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
<br clear=all>
