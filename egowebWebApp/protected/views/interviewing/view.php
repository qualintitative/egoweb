<?php
/* @var $this InterviewingController */
/* @var $model[$array_id] Answer */
$this->pageTitle = $study->name;
$completed = 0;
if($interviewId)
	$completed = Interview::model()->findByPk($interviewId)->completed;
$prompts = array('INTRODUCTION', 'PREFACE', 'ALTER_PROMPT', 'CONCLUSION');
if(!isset($key) || !$key){
	if(isset($_GET['key']))
		$key = $_GET['key'];
	else
		$key = '';
}
?>
<script>
	getVariables = <?php echo (count($_GET) > 0 ? json_encode($_GET) : '{}'); ?>;

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
		if($(".answerInput").length > 0)
			$(".answerInput")[0].focus();
		$("input[id*='question_']").each(function(index){
			if($.inArray($(this).val(),Object.keys(getVariables)) != -1){
				$("[name='Answer[" + $(this).attr('questionId') + "][value]']").val(getVariables[$(this).val()]);
				$("[name='Answer[" + $(this).attr('questionId') + "][value]']").prop( "readonly", "readonly" );
			}
		});

		$(document).keydown(function(e) {
			if(e.keyCode == 13){
				if($("#alterFormBox").length != 0 && $("#alterFormBox").html() != "")
					$('.alterSubmit').submit();
				else
					$('#answer-form').submit();
			}
			if (e.keyCode == 39){
				e.preventDefault();
				$("input:focus").next().focus();
			}
			if (e.keyCode == 37){
				e.preventDefault();
				$("input:focus").prev().focus();
			}
			if (e.keyCode == 38){
				e.preventDefault();
				var counter = $("input:focus").parent().attr("counter");
				if(typeof counter != "undefined"){
					var index = $("input:focus").index();
					if(counter > 0)
						counter--;
					$("[counter='" + counter + "']").children()[index].focus();
				}else{
					$(".answerInput").each(function(i){
					if($(this).is(":focus"))
						index = i;
					});					console.log(index);
					if(index > 0)
						index--;
					$(".answerInput")[index].focus();
				}
			}
			if (e.keyCode == 40){
				e.preventDefault();
				var counter = $("input:focus").parent().attr("counter");
				if(typeof counter != "undefined"){
					var index = $("input:focus").index();
					counter++;
					if($("[counter='" + counter + "']").length == 0)
						counter--;
					$("[counter='" + counter + "']").children()[index].focus();
				}else{
					$(".answerInput").each(function(i){
					if($(this).is(":focus"))
						index = i;
					});
					console.log(index);
					if(typeof $(".answerInput")[index+1] != "undefined")
						index++;
					$(".answerInput")[index].focus();
				}
			}
		});

		if(<?php echo $completed; ?> == -1){
			$("input").prop('disabled', true);
			$("select").prop('disabled', true);
			$(".orangebutton").prop('disabled', false);
			$(".graybutton").prop('disabled', false);
			$("#graph-form").find("input").prop('disabled', false);
		}
		$('.pageLevel').change(function(){
			var selected = $(this);
			if($(this).is(":checked")){
				$( "input[class*='-skipReason']").prop("checked", false);
				$( "input[class*='multiselect-']").prop("checked", false);
				$( "input[value='" + selected.val() + "']").each(function(index){
					if(!$(this).hasClass("pageLevel") && $(this).attr('class') && (($(this).attr('class').match(/multiselect-(.*)/) && $(this).attr('class').match(/multiselect-(.*)/).length > 1) || ($(this).attr('class').match(/(.*)-skipReason/) && $(this).attr('class').match(/(.*)-skipReason/).length > 1))){
						if($(this).attr('class').match(/multiselect-(.*)/))
							var multi = $(this).attr('class').match(/multiselect-(.*)/)[1];
						else
							var multi = $(this).attr('class').match(/(.*)-skipReason/)[1];
						var realVal = $("#Answer_" + multi + "_value");
						var values = realVal.val().split(',');
						var skipVal = $("#Answer_" + multi + "_skipReason" ).val();
						if(realVal.val() == "" && $("#Answer_" + multi + "_skipReason" ).val() == "NONE"){
							$(this).prop("checked", true);
							realVal.val(selected.val());
							if(selected.val() == "DONT_KNOW" || selected.val() == "REFUSE"){
								$("#Answer_" + multi + "_skipReason" ).val(selected.val());
								realVal.val("");
							}
						}else{
							if(skipVal == "NONE"){
								for(var k in values){
									$(".multiselect-" +  multi + "[value='" + values[k] + "']").prop("checked", true);
								}
							}else{
								$("." +  multi + "-skipReason[value='" + skipVal + "']").prop("checked", true);
							}
						}
					}
				});
			}else{
				$( "input[value='" + selected.val() + "']").each(function(index){
					if($(this).attr('class') && ($(this).attr('class').match(/multiselect-(.*)/) && $(this).attr('class').match(/multiselect-(.*)/).length > 1) || ($(this).attr('class').match(/(.*)-skipReason/) && $(this).attr('class').match(/(.*)-skipReason/).length > 1)){
						if($(this).attr('class').match(/multiselect-(.*)/)){
							var multi = $(this).attr('class').match(/multiselect-(.*)/)[1];
							var realVal = $("#Answer_" + multi + "_value");
							var values = realVal.val().split(',');
							for(var k in values){
								if(values[k] == selected.val()){
									$(this).prop("checked", false);
									realVal.val('');
								}
							}
						}else{
							var multi = $(this).attr('class').match(/(.*)-skipReason/)[1];
							var skipVal = $("#Answer_" + multi + "_skipReason");
							if(selected.val() == skipVal.val()){
								$(this).prop("checked", false);
								$("#Answer_" + multi + "_skipReason" ).val("NONE");
							}
						}
					}
				});
			}
		})
	});
</script>



<?php // Alter List Box for Alter Prompt Page ?>
<?php  if(isset($questions[0]) && $questions[0]->answerType == "ALTER_PROMPT"): ?>
	<div id="alterListBox">
		<?php
		// fetch variable alter prompt
		$alterPrompt = AlterPrompt::getPrompt($study->id, Interview::countAlters($interviewId));
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
		$this->renderPartial('_view_alter', array('dataProvider'=>$dataProvider, 'alterPrompt'=>$alterPrompt, 'model'=>$alter, 'studyId'=>$study->id, 'interviewId'=>$interviewId, 'ajax'=>true), false, false);
		?>
	</div>
<?php endif; ?>

<?php if(isset($questions[0])): ?>
	<?php if(in_array($questions[0]->answerType, $prompts)): ?>
		<div class="questionText <?php if($questions[0]->answerType == "ALTER_PROMPT"){ echo "col-sm-9"; } ?>">
			<?php echo Interview::interpretTags($questions[0]->prompt, $interviewId, $question->alterId1, $question->alterId2); ?>
			<?php if($questions[0]->answerType == "PREFACE" && file_exists(Yii::app()->basePath."/../audio/".$study->id . "/PREFACE/" . $questions[0]->id . ".mp3")):?>
				<script>
					var promptAudio_<?=$questions[0]->id;?> = loadAudio("/audio/<?= $study->id . "/PREFACE/" . $questions[0]->id . ".mp3"; ?>");
				</script>
				<a class="play-sound" onclick='playSound("/audio/<?= $study->id . "/PREFACE/" . $questions[0]->id . ".mp3" ?>")' href="#"><span class="fui-volume"></span></a>
			<?php endif; ?>
			<?php if($questions[0]->answerType == "ALTER_PROMPT" && file_exists(Yii::app()->basePath."/../audio/".$study->id . "/STUDY/ALTERPROMPT.mp3")):?>
				<script>
					var promptAudio_<?=$questions[0]->id;?> = loadAudio("/audio/<?= $study->id . "/STUDY/ALTERPROMPT.mp3"; ?>");
				</script>
				<a class="play-sound" onclick='playSound("/audio/<?= $study->id . "/STUDY/ALTERPROMPT.mp3" ?>")' href="#"><span class="fui-volume"></span></a>
			<?php endif; ?>

		</div>
		<div class="question">
	<?php endif; ?>
	<?php if($questions[0]->answerType == 'ALTER_PROMPT'): ?>
		<div id="alterPrompt" class="orangeText" style="width:500px"></div>
		<div id="alterFormBox"></div>
		</div>
	<?php endif;?>
<?php endif;?>

<?php
foreach($questions as $first) {
	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'answer-form',
		'htmlOptions'=>array('class'=>$first->subjectType == "NETWORK" ? 'col-sm-6' : 'col-sm-12'),
		'enableAjaxValidation'=>true,
		'action'=>'/interviewing/save/'.$study->id.($key ? "&key=" . $key : ""),
	));
	break;
}
?>

<?php
// preload error message if there is one
$error_id = "";
$maxwidth = 180;

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
		<?php if($question->subjectType == "EGO_ID"): ?>
			<?php echo "EGO_ID <br style='clear:left'><br style='clear:left'>"; ?>
		<?php else: ?>
			<?php echo $question->title . "<br style='clear:left'><br style='clear:left'>"; ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	if($counter == 0 && $question->askingStyleList)
		echo "<div class='floatingNav' style='background:#fff'>";
	?>

	<?php
	// display error
	if($counter == 0 && $error_id !== ""){
		echo $form->errorSummary($model[$error_id]);
	}
	?>

	<?php if($counter == 0 && !in_array($question->answerType, $prompts)): ?>
		<div class="questionText">
			<?php
			if ($question->subjectType == "EGO_ID")
				echo Study::model()->findByPk($study->id)->egoIdPrompt;
			else
				echo Interview::interpretTags($question->prompt, $interviewId , $question->alterId1, $question->alterId2);
			?>
			<?php if(file_exists(Yii::app()->basePath."/../audio/".$question->studyId . "/" . $question->subjectType . "/" . $question->id . ".mp3")):?>
				<script>
					var promptAudio_<?=$question->id;?> = loadAudio("/audio/<?= $question->studyId . "/" . $question->subjectType . "/" . $question->id . ".mp3"; ?>");
				</script>
				<a class="play-sound" onclick='playSound("/audio/<?= $question->studyId . "/" . $question->subjectType . "/" . $question->id . ".mp3" ?>")' href="#"><span class="fui-volume"></span></a>
			<?php endif; ?>
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

	if($counter == 0)
		echo "<input type='hidden' id='question_$question->id' value='$question->title' questionId='$question->id'>";

	// either set empty values for prompt / preface page, or display the question
	if(in_array($question->answerType, $prompts)){
		$model = new Answer;
		echo $form->hiddenField($model, '[0]'. 'questionId',array('value'=>'0'));
		echo $form->hiddenField($model, '[0]'.'value',array('value'=>$question->answerType));
		echo $form->hiddenField($model, '[0]'.'questionType',array('value'=>$question->answerType));
		echo $form->hiddenField($model, '[0]'.'studyId',array('value'=>$question->studyId));
		echo $form->hiddenField($model, '[0]'.'answerType',array('value'=>$question->answerType));
		echo $form->hiddenField($model, '[0]'.'interviewId',array('value'=>$interviewId));
		echo CHtml::hiddenField('minAlters', Study::model()->findByPk($study->id)->minAlters);
	}else{
		$skipList = array();
		if($question->dontKnowButton)
			$skipList['DONT_KNOW'] = "Don't Know";
		if($question->refuseButton)
			$skipList['REFUSE'] =  "Refuse";

		$options = QuestionOption::model()->findAllByAttributes(array('questionId'=>$question->id), $params=array('order'=>'ordering'));
		if($counter == 0 && $question->askingStyleList){
			$columns = 1;
			$maxwidth = 180;

			echo "<div class='multiRow' style='width:180px;margin: 0 0 20px 20px'>&nbsp;</div>";
			if($question->answerType == "MULTIPLE_SELECTION"){
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

		if($counter == 0 && $question->askingStyleList)
			echo "</div><br style='clear:both'>";
		if($counter == 0 )
			echo "<div class='question ". ($question->askingStyleList ? $question->subjectType : "") ."'>";
		if($model[$array_id]->getError('value')){
			$rowColor = "error";
		}

		$this->renderPartial('_form_'.$panel, array('counter'=>$counter, 'skipList'=>$skipList,'rowColor'=>$rowColor, 'question'=>$question, 'interviewId'=>$interviewId, 'form'=>$form, 'array_id'=>$array_id, 'model'=>$model, 'ajax'=>true), false, false);

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
				echo "<br clear=all><div counter=$counter class='multiRow palette-sun-flower' style='text-align:left'><label style='width:180px'>Set All</label>".CHtml::checkBoxList(
						'multiselect-pageLevel',
						'',
						CHtml::listData($options, 'id', ''),
						array('class'=>'multiselect pageLevel',
						'container'=>'',
						'separator'=>"",
						'template'=>'{input}',
						'style'=>"margin-left:" . intval($maxwidth * .4) ."px; width:" . intval($maxwidth * .6) ."px",
						)
					);
					if(count($skipList) != 0){
							echo CHtml::checkBoxList(
							"pageLevel_skip",
							array($model[$array_id]->skipReason),
							$skipList,
							array('class'=>'skipReason pageLevel',
							'container'=>'',
							'separator'=>"",
							'template'=>'{input}',
							'style'=>"margin-left:" . intval($maxwidth * .4) ."px; width:" . intval($maxwidth * .6) ."px",
						)
						);
					}
				echo "</div>";
			}

		}
		?>		</div>
		<br style="clear:left">
	<?php endif; ?>
	<?php
	if($question->subjectType == "NETWORK")
		$networkQuestion = $question;
	else
		$networkQuestion = "";
	?>
<?php endforeach; ?>

<input name="page" type=hidden value=<?php echo $page ?> />
<input name="studyId" type=hidden value=<?php echo $study->id ?> />

<?php $this->endWidget(); ?>

<?php
if($networkQuestion  && is_numeric($networkQuestion->networkRelationshipExprId)){
	echo "<div id='visualizePlugin' class='col-sm-6 pull-right'>";
	$this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$networkQuestion->networkRelationshipExprId, 'params'=>$networkQuestion->networkParams, 'networkTitle'=>$networkQuestion->title));
	echo "</div>";
}
?>

<div id="buttonRow" style="float:left;padding-bottom:20px;clear:left">
	<?php if($page != 0 ): ?>
		<a class="graybutton" href="/interviewing/<?php echo $study->id. "?interviewId=". $interviewId . "&page=". ($page - 1) . $key; ?>">Back</a>
	<?php endif; ?>
	<?php if($completed != -1): ?>
		<?php if($question->answerType != "CONCLUSION"): ?>
			<input class='orangebutton' type="submit" onclick="$('#answer-form').submit()" value="Next"/>
		<?php else: ?>
			<input class='orangebutton' type="submit" onclick="$('#answer-form').submit()" value="Finish"/>
		<?php endif; ?>
	<?php else: ?>
		<?php if($question->answerType != "CONCLUSION"): ?>
			<a class="orangebutton" href="/interviewing/<?php echo $study->id. "?interviewId=". $interviewId . "&page=". ($page + 1) . $key; ?>">Next</a>
		<?php endif; ?>
	<?php endif; ?>
</div>

<script>
	$(function(){
		nav = <?= $qNav ?>;
		for(k in nav){
			$('#navbox ul').append("<li><a href='/interviewing/<?php echo $study->id. "?interviewId=". $interviewId . "&page="; ?>" + k + "'>" + k + ". " + nav[k] + "</a></li>");
		}
	});
</script>
