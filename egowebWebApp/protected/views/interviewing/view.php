<?php
/* @var $this InterviewingController */
/* @var $model[$array_id] Answer */
$this->pageTitle = Study::getName($studyId);
$completed = 0;
if($interviewId)
	$completed = Interview::model()->findByPk($interviewId)->completed;
$prompts = array('INTRODUCTION', 'PREFACE', 'ALTER_PROMPT', 'CONCLUSION');
if(isset($_GET['key']))
	$key = '&key='.$_GET['key'];
else
	$key = '';
?>
<script>

function toggleOther(option){
	console.log(option);
	option.toggle();
}

function changeOther(other_id){
	otherText = [];
	$('.' + array_id + '_other').each(function(index){
		if($(this).is(":visible"))
			otherText.push($(this).attr('id')+":"+$(this).val());
	});
	$('#Answer_' + other_id + '_otherSpecifyText').val(otherText.join(';;'));
}

$(function(){
	if(<?php echo $completed; ?> == -1){
		$("input").prop('disabled', true);
		$("select").prop('disabled', true);
		$(".orangebutton").prop('disabled', false);
		$(".graybutton").prop('disabled', false);
	}
	/*$('.pageLevel').change(function(){
		console.log($(this).attr("checked"));
		$('input[value="' + $(this).val() + '"]').prop("checked", $(this).is(":checked"));
		if($(this).is(":checked")){
			$( "input[class*='skipReason']").prop("checked", false);
			$( "input[class*='multiselect']").prop("checked", false);
			$( "input[value='" + $(this).val() + "']").prop("checked", true);

			if($(this).val() == "DONT_KNOW" || $(this).val() == "REFUSE")
				$( "input[name*='value']" ).val('');
			else
				$( "input[name*='value']" ).val($(this).val());
			$(".skipReasonValue").val($(this).val());
		}else{
			$(".skipReasonValue").val("NONE");
		}
	})*/
	$('.pageLevel').change(function(){
		var selected = $(this);
		if($(this).is(":checked")){
			$( "input[class*='skipReason']").prop("checked", false);
			$( "input[class*='multiselect-']").prop("checked", false);
			$( "input[value='" + selected.val() + "']").each(function(index){
				if($(this).attr('class').match(/multiselect-(.*)/) && $(this).attr('class').match(/multiselect-(.*)/).length > 1){
					var multi = $(this).attr('class').match(/multiselect-(.*)/)[1];
					var realVal = $("#Answer_" + multi + "_value");
					var values = realVal.val().split(',');
					if(realVal.val() == ""){
						$(this).prop("checked", true);
						if(selected.val() == "DONT_KNOW" || selected.val() == "REFUSE"){
							$("#Answer_" + multi + "_skipReason" ).val(selected.val());
						}else{
							$("#Answer_" + multi + "_skipReason" ).val("NONE");
						}
						values.push(selected.val());
						realVal.val(selected.val());
					}else{
						for(var k in values){
							$(".multiselect-" +  multi + "[value='" + values[k] + "']").prop("checked", true);
						}
					}
				}
			});
		}else{
			$( "input[value='" + selected.val() + "']").each(function(index){
				if($(this).attr('class').match(/multiselect-(.*)/) && $(this).attr('class').match(/multiselect-(.*)/).length > 1){
					var multi = $(this).attr('class').match(/multiselect-(.*)/)[1];
					var realVal = $("#Answer_" + multi + "_value");
					var values = realVal.val().split(',');
					for(var k in values){
						if(values[k] == selected.val()){
							$(this).prop("checked", false);
							if(selected.val() == "DONT_KNOW" || selected.val() == "REFUSE"){
								$("#Answer_" + multi + "_skipReason" ).val("NONE");
							}
							realVal.val('');
						}
					}
				}
			});
		}
	})
});
</script>

<div id="navigation">
	<div id="navbox">
		<ul></ul>
	</div>
</div>

<?php // Alter List Box for Alter Prompt Page ?>
<?php  if(isset($questions[0]) && $questions[0]->answerType == "ALTER_PROMPT"): ?>
<div id="alterListBox">
<?php
// fetch variable alter prompt
$alterPrompt = AlterPrompt::getPrompt($studyId, Interview::countAlters($interviewId));
// fetch alter list
$criteria=array(
	'condition'=>"FIND_IN_SET(" . $interviewId . ", interviewId)",
	'order'=>'ordering',
);
$dataProvider=new CActiveDataProvider('Alters',array(
	'criteria'=>$criteria,
	'pagination'=>false,
));
$alter = new Alters;
$this->renderPartial('_view_alter', array('dataProvider'=>$dataProvider, 'alterPrompt'=>$alterPrompt, 'model'=>$alter, 'studyId'=>$studyId, 'interviewId'=>$interviewId, 'ajax'=>true), false, false);
?>
</div>
<?php endif; ?>

<?php if(isset($questions[0])): ?>
	<?php if(in_array($questions[0]->answerType, $prompts)): ?>
		<div class="questionText">
		<?php echo Interview::interpretTags($questions[0]->prompt, $interviewId); ?>
		</div>
		<div class="question">
	<?php endif; ?>
	<?php if($questions[0]->answerType == 'ALTER_PROMPT'): ?>
		<?php if($study->multiSessionEgoId): ?>
		<div id="previous_alters">
		<?php
		$egoValue = q("SELECT value FROM answer WHERE interviewId = " . $interviewId . " AND questionId = " . $study->multiSessionEgoId)->queryScalar();
		$multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
		$interviewIds = q("SELECT interviewId FROM answer WHERE questionId in (" . implode(",", $multiIds) . ") AND value = '" .$egoValue . "'" )->queryColumn();
		$interviewIds = implode(",",array_diff($interviewIds, array($interviewId)));
		$alters = q("SELECt * FROM alters WHERE FIND_IN_SET(interviewId,'$interviewIds')")->queryAll();
		if($alters){
			echo "<b>Previous Alters</b><br><br>";
			foreach($alters as $oldAlter){
				echo $oldAlter['name'] . "<br>";
			}
		}
		?>
		</div>
		<?php endif; ?>
		<div id="alterPrompt" class="orangeText" style="width:500px"></div>
		<?php
		$panel = strtolower($questions[0]->answerType);
		$form=$this->beginWidget('CActiveForm', array(
			'id'=>'alter-form',
			'enableAjaxValidation'=>true,
		));

		$this->renderPartial('_form_'.$panel, array('question'=>$questions[0], 'interviewId'=>$interviewId,  'form'=>$form, 'model'=>$alter, 'study'=>$study, 'ajax'=>true), false, true);
		echo CHtml::ajaxSubmitButton ("+ Add",
			CController::createUrl('ajaxupdate'),
			array('success'=>'js:function(data){$("#alterListBox").html(data);$("#Alters_name").val("");$(".flash-error").hide()}'),
			array('id'=>uniqid(), 'live'=>false, 'class'=>"orangebutton"));

		$this->endWidget();
		?>

		<?php if(isset($model[0])): ?>
			<div class="flash-error" style="width:200px;">
				<?php echo $model[0]->getError('value'); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php endif;?>
<?php endif;?>

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'answer-form',
	'enableAjaxValidation'=>true,
	'action'=>'/interviewing/save/'.$studyId,
));
?>

<?php
// preload error message if there is one
$error_id = "";

$networkQuestion = "";

foreach($questions as $question) {
	if(is_numeric($question->alterId1) && !is_numeric($question->alterId2)){
		$array_id = $question->id . "-" . $question->alterId1;
	}else if(is_numeric($question->alterId1) && is_numeric($question->alterId2)){
		$array_id = $question->id . "-" . $question->alterId1 . "and" . $question->alterId2;
	}else{
		$array_id = $question->id;
	}
	if(!isset($model[$array_id]))
		$model[$array_id] = new Answer;
	if($model[$array_id]->getError('value')){
		$error_id = $array_id;
		break;
	}
}
?>
<?php $counter = 0; $phrase = ""; ?>
<?php foreach($questions as $question): ?>

	<?php if(!Yii::app()->user->isGuest && $counter == 0): ?>
		<?php echo $question->title . "<br style='clear:left'><br style='clear:left'>"; ?>
	<?php endif; ?>

	<?php
	if(count($questions > 1) && $counter == 0 && $question->askingStyleList)
		echo "<div class='floatingNav' style='background:#fff'>";
	?>

	<?php
	// display error
	if($counter == 0 && $error_id != ""){
		echo $form->errorSummary($model[$error_id]);
	}
	?>

	<?php if($counter == 0 && !in_array($question->answerType, $prompts)): ?>
	<div class="questionText">
		<?php
		if ($question->subjectType == "EGO_ID")
			echo Study::model()->findByPk($studyId)->egoIdPrompt;
		else
			echo Interview::interpretTags($question->prompt, $interviewId , $question->alterId1, $question->alterId2);
		?>
	</div>
	<br style="clear:left">

	<?php if ($question->answerType == "MULTIPLE_SELECTION"): ?>
		<?php
		$phrase = "Please select ";
		if($question->minCheckableBoxes != "" && $question->maxCheckableBoxes != "" && $question->minCheckableBoxes == $question->maxCheckableBoxes)
			$phrase .= $question->maxCheckableBoxes;
		else if($question->minCheckableBoxes != "" && $question->maxCheckableBoxes != "" && $question->minCheckableBoxes != $question->maxCheckableBoxes)
			$phrase .= $question->minCheckableBoxes . " to " . $question->maxCheckableBoxes;
		else if ($question->minCheckableBoxes == "" && $question->maxCheckableBoxes != "")
			$phrase .= " up to " . $question->maxCheckableBoxes ;
		else if ($question->minCheckableBoxes != "" && $question->maxCheckableBoxes == "")
			$phrase .= " at least " . $question->minCheckableBoxes ;

		if($question->maxCheckableBoxes == 1)
			$phrase .= " response";
		else
			$phrase .= " responses";
		if($question->askingStyleList && !$question->withListRange)
			$phrase .= " for each row";
		?>
		<?php endif; ?>
	<?php if ($question->answerType == "NUMERICAL" && $question->subjectType != "EGO_ID"): ?>
	<?php
		$min = ""; $max = ""; $numberErrors = 0;
		if($question->minLimitType == "NLT_LITERAL"){
			$min = $question->minLiteral;
		}else if($question->minLimitType == "NLT_PREVQUES"){
			$min = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->minPrevQues));
			if($min)
				$min = $min->value;
			else
				$min = "";
		}
		if($question->maxLimitType == "NLT_LITERAL"){
			$max = $question->maxLiteral;
		}else if($question->maxLimitType == "NLT_PREVQUES"){
			$max = Answer::model()->findByAttributes(array('interviewId'=>$interviewId,'questionId'=>$question->maxPrevQues));
			if($max)
				$max = $max->value;
			else
				$max = "";
		}
		if($min != "")
			$numberErrors++;
		if($max != "")
			$numberErrors = $numberErrors + 2;

		if($numberErrors == 3)
			$phrase = "Please enter a number from " . $min . " to " . $max .".";
		else if ($numberErrors == 2)
			$phrase = "Please enter a number (" . $max . " or lower).";
		else if ($numberErrors == 1)
			$phrase = "Please enter a number (" . $min . " or higher).";
		if($question->askingStyleList && !$question->withListRange)
			$phrase .= " for each row";
	?>
	<?php endif; ?>
	<?php endif; ?>


	<?php if ($question->subjectType == "EGO_ID"): ?>
		<?php if($counter == 0): ?>
		<div class="orangeText" style="padding: 0 0 0 20px"><?php echo $question->prompt; ?></div>
		<?php else: ?>
		<div class="orangeText"><?php echo $question->prompt; ?></div>
		<?php endif; ?>
		<br clear=all>
	<?php endif; ?>

	<?php if($phrase != "" && $counter == 0): ?>
		<div class="orangeText" style="padding: 0 0 20px 20px"><?php echo $phrase; ?></div>
		<br clear=all>
	<?php endif; ?>

	<?php
		if($question->subjectType == "NETWORK" && is_numeric($question->networkRelationshipExprId))
			$networkQuestion = $question;
	?>

	<?php
	// sets row color, which determines formatting of list style questions
	if($question->askingStyleList){
		if($counter & 1){
			$rowColor = "colorA";
		}else{
			$rowColor = "colorB";
		}
	}else{
		$rowColor = "";
	}
	?>

	<?php
	if(is_numeric($question->alterId1) && !is_numeric($question->alterId2)){
		$array_id = $question->id . "-" . $question->alterId1;
	}else if(is_numeric($question->alterId1) && is_numeric($question->alterId2)){
		$array_id = $question->id . "-" . $question->alterId1 . "and" . $question->alterId2;
	}else{
		$array_id = $question->id;
	}
	$panel = strtolower($question->answerType);
	if($model[$array_id]->skipReason == "")
		$model[$array_id]->skipReason = "NONE";

	// either set empty values for prompt / preface page, or display the question
	if(in_array($question->answerType, $prompts)){
		$model = new Answer;
		echo $form->hiddenField($model, '[0]'. 'questionId',array('value'=>'0'));
		echo $form->hiddenField($model, '[0]'.'value',array('value'=>$question->answerType));
	 	echo $form->hiddenField($model, '[0]'.'questionType',array('value'=>$question->answerType));
		echo $form->hiddenField($model, '[0]'.'studyId',array('value'=>$question->studyId));
		echo $form->hiddenField($model, '[0]'.'answerType',array('value'=>$question->answerType));
		echo $form->hiddenField($model, '[0]'.'interviewId',array('value'=>$interviewId));
		echo CHtml::hiddenField('minAlters', Study::model()->findByPk($studyId)->minAlters);
	}else{
		$skipList = array();
		if($question->dontKnowButton)
		    $skipList['DONT_KNOW'] = "Don't Know";
		if($question->refuseButton)
		    $skipList['REFUSE'] =  "Refuse";

		if(count($questions > 1) && $counter == 0 && $question->askingStyleList){
			$columns = 1;
			$maxwidth = 180;
			echo "<div class='multiRow' style='width:180px;margin: 0 0 20px 20px'>&nbsp;</div>";
			if($question->answerType == "MULTIPLE_SELECTION"){
				$options = QuestionOption::model()->findAllByAttributes(array('questionId'=>$question->id), $params=array('order'=>'ordering'));
				$columns = count($options) + count($skipList);
			}
			if($question->answerType == "TEXTUAL" || $question->answerType == "NUMERICAL"){
				$columns = 1 + count($skipList);
			}
			if($question->answerType == "TIME_SPAN"){
				$timeArray = Question::timeBits($question->timeUnits);
				$columns = count($timeArray)+ count($skipList);
			}
			if($columns != 0)
				$maxwidth = intval(620 / $columns);
			if($maxwidth > 180)
				$maxwidth = 180;
			if($question->answerType == "MULTIPLE_SELECTION"){
				foreach($options as $option){
					echo "<div class='multiRow' style='width:".$maxwidth."px'>".$option->name."</div>";
				}
			}else{
				if($question->answerType == "TIME_SPAN"){
					foreach($timeArray as $time)
						echo "<div class='multiRow' style='width:100px'></div>";
				}else{
					echo "<div class='multiRow' style='width:140px'>&nbsp;</div>";
				}
			}
			foreach($skipList as $k=>$value){
				echo "<div class='multiRow' style='width:".$maxwidth."px'>".$value."</div>";
			}
		}
		if($question->dontKnowButton)
		    $skipList['DONT_KNOW'] = ($question->askingStyleList) ? "" : "Don't Know";
		if($question->refuseButton)
		    $skipList['REFUSE'] = ($question->askingStyleList) ? "": "Refuse";

Yii::app()->clientScript->registerScript('floatingNav', "
$(function() {
var nav = $('.floatingNav');
if(nav.length != 0)
	floatTop();

function floatTop(){
	// Stick the #nav to the top of the window
	var navHomeY = nav.offset().top;
	var isFixed = false;
	var w = $(window);
	w.scroll(function() {
		var scrollTop = w.scrollTop();
		var shouldBeFixed = scrollTop > navHomeY;
		if (shouldBeFixed && !isFixed) {
			$('#navigation').hide();
			$('.question').css({marginTop:nav.height()+36});
			nav.css({
				position: 'fixed',
				top: 0,
				//left: nav.offset().left,
				width: nav.width()
			});
			isFixed = true;
		}
		else if (!shouldBeFixed && isFixed)
		{
			$('.question').css({marginTop:0});
			nav.css({
				position: 'static'
			});
			isFixed = false;
		}
	});
}
});

");
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
Yii::app()->clientScript->registerScript('skipReason-'.$array_id, "
$('.".$array_id."-skipReason').click(function(event){
	if($(this).val() != $('#Answer_".$array_id."_skipReason').val()){
		$('.".$array_id."-skipReason').prop('checked', false);
		$(this).prop('checked', true);
		$('#Answer_".$array_id."_otherSpecifyText').val('');
		$('#Answer_".$array_id."_otherSpecifyText').hide();
		$('#".$array_id."_other').prop('checked', false);
        $('.multiselect-".$array_id."').prop('checked', false);
		$('#Answer_".$array_id."_value').val('');
		$('#Answer_".$array_id."_skipReason').val($(this).val());
	}else{
		$('#Answer_".$array_id."_skipReason').val('NONE');
	}
});
");

		if(count($questions > 1) && $counter == 0 && $question->askingStyleList)
			echo "</div><br style='clear:both'>";
		if($counter == 0 )
			echo "<div class='question'>";
		if($model[$array_id]->getError('value')){
			$rowColor = "error";
		}

		$this->renderPartial('_form_'.$panel, array(/*'skipList'=>$skipList,*/'rowColor'=>$rowColor, 'question'=>$question, 'interviewId'=>$interviewId, 'form'=>$form, 'array_id'=>$array_id, 'model'=>$model, 'ajax'=>true), false, false);

		if(count($skipList) != 0){
			if($rowColor != "" && $question->askingStyleList){
			    echo "<div class='multiRow ".$rowColor."' style='width:".$maxwidth."px'>".CHtml::checkBoxList(
			    	$array_id."_skip",
			    	array($model[$array_id]->skipReason),
			    	$skipList,
			    	array('class'=>$array_id.'-skipReason', 'container'=>'', 'separator'=>"</div><div class='multiRow ".$rowColor."' style='width:".$maxwidth."px'>")
			    ) . "</div>";
			}else{
			    echo "<div clear=all>".
			    CHtml::checkBoxList($array_id."_skip", array($model[$array_id]->skipReason), $skipList, array('class'=>$array_id.'-skipReason'))
			    ."</div>";
			}
		}

		echo $form->hiddenField($model[$array_id], '['.$array_id.']'.'skipReason',array('value'=>$model[$array_id]->skipReason, 'class'=>"skipReasonValue"));

		echo $form->hiddenField($model[$array_id], '['.$array_id.']'. 'questionId',array('value'=>$question->id));
	 	echo $form->hiddenField($model[$array_id], '['.$array_id.']'. 'questionType',array('value'=>$question->subjectType));
		echo $form->hiddenField($model[$array_id], '['.$array_id.']'.'studyId',array('value'=>$question->studyId));
		echo $form->hiddenField($model[$array_id], '['.$array_id.']'.'answerType',array('value'=>$question->answerType));
		echo $form->hiddenField($model[$array_id], '['.$array_id.']'.'interviewId',array('value'=>$interviewId));
	}

	if($question->subjectType == 'ALTER' || $question->subjectType == 'ALTER_PAIR'){
		echo $form->hiddenField($model[$array_id], '['.$array_id.']'.'alterId1',array('value'=>$question->alterId1));
		if($question->subjectType == 'ALTER_PAIR')
			echo $form->hiddenField($model[$array_id], '['.$array_id.']'.'alterId2',array('value'=>$question->alterId2));
	}
	?>
	<?php $counter++; ?>
	<?php if(count($questions) == $counter && $question->answerType != "ALTER_PROMPT"): ?>

<?php
	if($question->allButton && ($question->subjectType == 'ALTER' || $question->subjectType == 'ALTER_PAIR')){
if($rowColor != "" && $question->askingStyleList){
	$columns = count($options) + count($skipList);
	$maxwidth = 180;
	if($columns != 0)
		$maxwidth = intval(620 / $columns);
	if($maxwidth > 180)
		$maxwidth = 180;

	echo "<br clear=all><div class='multiRow palette-sun-flower' style='width:180px; text-align:left'>Set All</div><div class='multiRow palette-sun-flower' style='width:".$maxwidth."px'>".CHtml::checkBoxList(
			'multiselect-pageLevel',
			$selected,
			CHtml::listData($options, 'id', ''),
			array('class'=>'multiselect pageLevel', 'container'=>'', 'separator'=>"</div><div class='multiRow palette-sun-flower'  style='width:".$maxwidth."px'>")
		) . "</div>";
}else{
	echo CHtml::checkBoxList(
	    'multiselect-pageLevel',
	    $selected,
	    CHtml::listData($options, 'id', 'name'),
	    array('class'=>'multiselect pageLevel')
	);
	echo "<br>";
}
		if(count($skipList) != 0){
			if($rowColor != "" && $question->askingStyleList){
			    echo "<div class='multiRow palette-sun-flower' style='width:".$maxwidth."px'>".CHtml::checkBoxList(
			    	"pageLevel_skip",
			    	array($model[$array_id]->skipReason),
			    	$skipList,
			    	array('class'=>'pageLevel-skipReason pageLevel', 'container'=>'', 'separator'=>"</div><div class='multiRow palette-sun-flower' style='width:".$maxwidth."px'>")
			    ) . "</div>";
			}else{
			    echo "<div clear=all>".
			    CHtml::checkBoxList("pageLevel_skip pageLevel", array($model[$array_id]->skipReason), $skipList, array('class'=>'pageLevel-skipReason'))
			    ."</div>";
			}
		}
	}
?>		</div>
		<br style="clear:left">
	<?php endif; ?>
<?php endforeach; ?>

	<div id="buttonRow" style="float:left;padding-bottom:20px;clear:left">
		<input name="page" type=hidden value=<?php echo $page ?> />
		<input name="studyId" type=hidden value=<?php echo $studyId ?> />
		<?php if($page != 0 ): ?>
			<a class="graybutton" href="/interviewing/<?php echo $studyId. "?interviewId=". $interviewId . "&page=". ($page - 1) . $key; ?>">Back</a>
		<?php endif; ?>
		<?php if($completed != -1): ?>
			<?php if($question->answerType != "CONCLUSION"): ?>
				<input class='orangebutton' type="submit" value="Next"/>
			<?php else: ?>
				<input class='orangebutton' type="submit" value="Finish"/>
			<?php endif; ?>
		<?php else: ?>
			<?php if($question->answerType != "CONCLUSION"): ?>
				<a class="orangebutton" href="/interviewing/<?php echo $studyId. "?interviewId=". $interviewId . "&page=". ($page + 1) . $key; ?>">Next</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>

<?php $this->endWidget(); ?>
<?php
if($networkQuestion)
	$this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$networkQuestion->networkRelationshipExprId, 'params'=>$networkQuestion->networkParams));

?>

<script>
$(function(){
	nav = <?php echo Study::nav($studyId, $page, $interviewId); ?>;
	console.log(nav);
	for(k in nav){
		$('#navbox ul').append("<li><a href='/interviewing/<?php echo $studyId. "?interviewId=". $interviewId . "&page="; ?>" + k + "'>" + k + ". " + nav[k] + "</a></li>");
	}
});
</script>