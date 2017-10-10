<?php
/* @var $this QuestionController */
/* @var $model Question */
/* @var $form CActiveForm */
?>

<?php

// create answertypes based on subjecttype
$answerTypes = array(
	'TEXTUAL'=>'TEXTUAL',
	'NUMERICAL'=>'NUMERICAL',
	'MULTIPLE_SELECTION'=>'MULTIPLE_SELECTION',
	'DATE'=>'DATE',
);

if($model->subjectType == "EGO_ID"){
	$answerTypes = array_merge($answerTypes, array('STORED_VALUE'=>'STORED_VALUE', 'RANDOM_NUMBER'=>'RANDOM_NUMBER'));
}else{
	$answerTypes = array_merge($answerTypes, array('TIME_SPAN'=>'TIME_SPAN', 'TEXTUAL_PP'=>'TEXTUAL_PP', 'NO_RESPONSE'=>'NO_RESPONSE'));
}
$subjectTypes = array(
	'EGO'=>'EGO',
	'NAME_GENERATOR'=>'NAME_GENERATOR',
	'ALTER'=>'ALTER',
	'ALTER_PAIR'=>'ALTER_PAIR',
    'NETWORK'=>'NETWORK',
);

?>

<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'question-form',
	'enableAjaxValidation'=>$ajax,
	"htmlOptions"=>array("class"=>"form-horizontal")
));
?>
<div class="form" style="height:315px; overflow-y:auto;">

<?php echo $form->errorSummary($model); ?>
<?php echo $form->hiddenField($model,'id',array('value'=>$model->id)); ?>
<?php echo $form->hiddenField($model,'subjectType',array('value'=>$model->subjectType)); ?>
<?php echo $form->hiddenField($model,'studyId',array('value'=>$model->studyId)); ?>

<?php
// set arbitrary number for model id, need to do this for dropdown list retrieval (list looks us values from previous questions)
if(!is_numeric($model->id))
	$model->id = 99999999999;
?>

<script>
// loads panel depending on answer type
jQuery(document).ready(function(){
	if('<?php echo $model->subjectType; ?>' != '')
		jQuery('.panel-<?php echo $model->id; ?>#<?php echo $model->subjectType; ?>').show();
	if('<?php echo $model->answerType; ?>' != '')
		jQuery('.panel-<?php echo $model->id; ?>#<?php echo $model->answerType; ?>').show();
	if('<?php echo $model->answerType; ?>' == 'MULTIPLE_SELECTION')
		jQuery('.panel-<?php echo $model->id; ?>#SELECTION').show();
	if('<?php echo $model->subjectType; ?>' == 'NETWORK')
		jQuery('.panel-<?php echo $model->id; ?>#NETWORK').show();
    if('<?php echo $model->subjectType; ?>' == 'NAME_GENERATOR')
    	jQuery('.panel-<?php echo $model->id; ?>#NAME_GENERATOR').show();
	if('<?php echo $model->askingStyleList; ?>' == true)
		jQuery('.panel-<?php echo $model->id; ?>#ALTER_STYLE').show();
	if('<?php echo $model->answerType; ?>' == 'TIME_SPAN'){
		jQuery('.panel-<?php echo $model->id; ?>#TIME_SPAN').show();
		$(".weeks").show();
	}
	if('<?php echo $model->answerType; ?>' == 'DATE'){
		jQuery('.panel-<?php echo $model->id; ?>#TIME_SPAN').show();
		$(".weeks").hide();
	}
});
</script>
<?php
// converts time unit checkboxes into timeUnits bit flag
Yii::app()->clientScript->registerScript('timeChange', "
jQuery('input.time-".$model->id."').change(function() {
	$('.panel-".$model->id." > #Question_timeUnits').val(0);
	$('.time-".$model->id."').each(function() {
		if($(this).is(':checked'))
			$('.panel-".$model->id." > #Question_timeUnits').val($('.panel-".$model->id." > #Question_timeUnits').val() | $(this).val());
	});
	console.log($('.panel-".$model->id." > #Question_timeUnits').val());
});
");
?>

	<div  style="width:50%; float:left; padding:10px">
		<div class="form-group">
		    <?php echo $form->labelEx($model,'title', array('for'=>$model->id . "_" . "title", "class"=>"control-label col-sm-4")); ?>
            <div class="col-sm-8">
                <?php echo $form->textField($model,'title',array('id'=>$model->id . "_" . "title", "class"=>"form-control")); ?>
		    </div>
		</div>

<?php if($model->subjectType != "EGO_ID"): ?>
        <div class="form-group">
            <?php echo $form->labelEx($model,'subjectType', array('for'=>'s-'.$model->id, "class"=>"control-label col-sm-4 input-sm")); ?>
            <div class="col-sm-8">
                <?php
                    echo $form->dropDownList(
                        $model,
                        'subjectType',
                        $subjectTypes,
                        array('class'=>'subjectTypeSelect', 'id'=>'s-'.$model->id, 'onchange'=>'changeAType(this)', "class"=>"form-control")
                    );
                ?>
            </div>
        </div>
<?php endif; ?>

		<div class="form-group">
    		<?php echo $form->labelEx($model,'answerType', array('for'=>'a-'.$model->id, "class"=>"control-label col-sm-4 input-sm")); ?>
    		<div class="col-sm-8">
        		<?php
        			echo $form->dropDownList(
        				$model,
        				'answerType',
        				$answerTypes,
        				array('class'=>'answerTypeSelect', 'id'=>'a-'.$model->id, 'onchange'=>'changeAType(this)', "class"=>"form-control")
        			);
        		?>
    		</div>
		</div>

        <div class="form-group">
    		<?php echo $form->labelEx($model,'Skip Logic Expression', array('for'=>$model->id."_"."answerReasonExpressionId", "class"=>"control-label col-sm-4 input-sm")); ?>
    		<?php $criteria=new CDbCriteria;
    		$criteria=array(
    			'condition'=>"studyId = " . $model->studyId,
    		);
    		?>
    		<div class="col-sm-8">
    		<?php echo $form->dropdownlist(
    			$model,
    			'answerReasonExpressionId',
    			CHtml::listData(
    				Expression::model()->findAll($criteria),
    				'id',
    				function($post) {return CHtml::encode(substr($post->name,0,40));}
    			),
    			array('empty' => 'Choose One', 'id'=>$model->id."_"."answerReasonExpressionId", "class"=>"form-control")
    		); ?>
    		</div>
        </div>

		<?php if($model->subjectType != "EGO_ID"): ?>
        <div>
		    <label><?php echo $form->checkBox($model,'dontKnowButton', array('id'=>$model->id . "_" . "dontKnowButton")); ?> Don't Know</label>
        </div>
		<?php echo $form->checkBox($model,'refuseButton', array('id'=>$model->id . "_" . "refuseButton")); ?>
		<?php echo $form->labelEx($model,'refuseButton', array('for'=>$model->id . "_" . "refuseButton")); ?>
		<br style="clear:left">
		<?php echo $form->checkBox($model,'askingStyleList', array('id'=>$model->id . "_" . "askingStyleList", 'onchange'=>'changeStyle($(this), '.$model->id.', "' . $model->subjectType.'")')); ?>
            <?php if($model->subjectType == "EGO" || $model->subjectType == "NETWORK"): ?>
            <?php echo CHtml::label("Leaf and Stem Question", $model->id . "_" . "askingStyleList"); ?>
            <?php else: ?>
			<?php echo $form->labelEx($model,'askingStyleList', array('for'=>$model->id . "_" . "askingStyleList")); ?>
			<?php endif;?>
		<?php else: ?>
		<div class="panel-<?php echo $model->id; ?>" id="TEXTUAL" style="display:none">
            <?php echo $form->labelEx($model,'useAlterListField', array("class"=>"control-label col-sm-8")); ?>
    		<div class="col-sm-4">
        		<?php echo $form->dropDownList(
        			$model,
        			'useAlterListField',
        			array(
        				''=>'None',
        				'id'=>'ID',
        				'email'=>'Email',
        				'name'=>'Name',
        			),
        			array("class"=>"form-control")
        		); ?>
        		<?php echo $form->error($model,'useAlterListField'); ?>
    		</div>
    	</div>

        <div class="panel-<?php echo $model->id; ?>" id="RANDOM_NUMBER" style="display:none">
                <div class="form-group">
                    <label class="control-label col-sm-4">Min</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="minRandom" onchange="$('#<?php echo $model->id; ?>-minLiteral').val($(this).val())" value="<?php echo $model->minLiteral; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4">Max</label>
                    <div class="col-sm-8">
                        <input class="form-control" id="maxRandom" onchange="$('#<?php echo $model->id; ?>-maxLiteral').val($(this).val())" value="<?php echo $model->maxLiteral; ?>">
                    </div>
                </div>
        </div>
        <?php endif; ?>

        <div class="panel-<?php echo $model->id; ?>" id="MULTIPLE_SELECTION" style="display:none">
    		<?php echo $form->checkBox($model,'otherSpecify',array('id'=>$model->id . "_" . "otherSpecify")); ?>
    		<?php echo $form->labelEx($model,'otherSpecify',array('for'=>$model->id . "_" . "otherSpecify")); ?>
    		<table border="0" bgcolor="#dddddd" >
    			<tr><td colspan="2">Bounds for MULTIPLE_SELECTION Entry:</td></tr>
    			<tr>
    				<td>
    					<?php echo $form->labelEx($model,'minCheckableBoxes',array('for'=>$model->id . "_" . "minCheckableBoxes")); ?>
    				</td>
    				<td>
    					<?php echo $form->textField($model,'minCheckableBoxes',array('id'=>$model->id . "_" . "minCheckableBoxes")); ?>
    					<?php echo $form->error($model,'minCheckableBoxes'); ?>
    				</td>
    			</tr>
    			<tr>
    				<td><?php echo $form->labelEx($model,'maxCheckableBoxes',array('for'=>$model->id . "_" . "maxCheckableBoxes")); ?></td>
    				<td>
    					<?php echo $form->textField($model,'maxCheckableBoxes',array('id'=>$model->id . "_" . "maxCheckableBoxes")); ?>
    					<?php echo $form->error($model,'maxCheckableBoxes'); ?>
    				</td>
    			</tr>
    		</table>
		</div>

		<div class="panel-<?php echo $model->id; ?>" id="NUMERICAL" style="display:none">

			<?php
		$criteria=new CDbCriteria;
		if(!isset($model->ordering))
			$model->ordering = 999;
		$criteria=array(
			'condition'=>"studyId = " . $model->studyId . " AND ordering < " . $model->ordering . " AND answerType = 'NUMERICAL'",
			'order'=>'ordering',
		);
			?>
				<table border="0" bgcolor="#dddddd" >
			<tr><td colspan="4">Bounds for NUMERICAL Entry:</td></tr>
			<tr><td>Min:</td>
			<td width=100>
			<?php echo $form->radioButtonList(
				$model,
				'minLimitType',
				array(
					'NLT_LITERAL'=>'Literal',
					'NLT_PREVQUES'=>'Previous',
					'NLT_NONE'=>'None'
				),
				array(
					'template'=>'<div style="width:100px; height:30px; float:left">{input}<div style="float:left; padding-left:5px">{label}</div></div>',
					'baseID'=>$model->id.'_minLimitType',
				)
			); ?>
			</td><td>
				<div style="height:30px;">
					<?php echo $form->textField($model,'minLiteral', array('style'=>'width:60px; margin:0', "id"=>$model->id .'-minLiteral')); ?>
				</div>
				<div style="height:30px;">
			<?php echo $form->dropdownlist(
				$model,
				'minPrevQues',
				CHtml::listData(Question::model()->findAll($criteria), 'id', 'title'),
				array('style'=>'margin:0','empty' => 'Choose One')
			); ?>
			</div>
			</td>
			</tr>
			<tr><td>Max:</td>
			<td>
			<?php echo $form->radioButtonList(
				$model,
				'maxLimitType',
				array(
					'NLT_LITERAL'=>'Literal',
					'NLT_PREVQUES'=>'Previous',
					'NLT_NONE'=>'None'
				),
				array(
					'template'=>'<div style="width:100px; height:30px; float:left">{input}<div style="float:left; padding-left:5px">{label}</div></div>',
					'baseID'=>$model->id.'_maxLimitType',
				)
			); ?>
			</td><td>
				<div style="height:30px;">
					<?php echo $form->textField($model,'maxLiteral', array('style'=>'width:60px; margin:0', "id"=>$model->id .'-maxLiteral')); ?>
				</div>
				<div style="height:30px;">
			<?php echo $form->dropdownlist(
				$model,
				'maxPrevQues',
				CHtml::listData(Question::model()->findAll($criteria), 'id', 'title'),
				array('empty' => 'Choose One')
			); ?>
		</div>
			</td>
			</tr>
		</table>
		</div>

		<div class="panel-<?php echo $model->id; ?>" id="TIME_SPAN" style="display:none">
		<?php echo $form->labelEx($model,'timeUnits'); ?>
		<?php echo $form->hiddenField($model,'timeUnits'); ?>
		<?php echo $form->error($model,'timeUnits'); ?>

		<?php $timeArray = Question::timeBits($model->timeUnits); ?>
		<table>
			<tr>
				<td>Units:</td>
				<td style="padding-left:0; padding-right:0;"><input type="checkbox" class="time-<?php echo $model->id ?>" id="<?php echo $model->id; ?>_yrs" value=1 <?php if(in_array("BIT_YEAR", $timeArray)): ?> checked <?php endif; ?> /></td>
				<td style="padding-left:0; padding-right:0;" align="left"><label for="<?php echo $model->id; ?>_yrs">Years</label></td>
				<td style="padding-left:4px; padding-right:0;" ><input type="checkbox" class="time-<?php echo $model->id ?>" id="<?php echo $model->id; ?>_mons" value=2 <?php if(in_array("BIT_MONTH", $timeArray)): ?> checked <?php endif; ?> /></td>
				<td style="padding-left:0; padding-right:0;" align="left"><label for="<?php echo $model->id; ?>_mons">Months</label></td>
				<td class="weeks" style="padding-left:4px; padding-right:0;" ><input type="checkbox" class="time-<?php echo $model->id ?>" id="<?php echo $model->id; ?>_wks" value=4 <?php if(in_array("BIT_WEEK", $timeArray)): ?> checked <?php endif; ?> /></td>
				<td class="weeks" style="padding-left:0; padding-right:0;" align="left"><label for="<?php echo $model->id; ?>_wks">Weeks</label></td>
				<td style="padding-left:4px; padding-right:0;" ><input type="checkbox" class="time-<?php echo $model->id ?>" id="<?php echo $model->id; ?>_days" value=8 <?php if(in_array("BIT_DAY", $timeArray)): ?> checked <?php endif; ?> /></td>
				<td style="padding-left:0; padding-right:0;" align="left"><label for="<?php echo $model->id; ?>_days">Days</label></td>
				<td style="padding-left:4px; padding-right:0;"><input type="checkbox" class="time-<?php echo $model->id ?>" id="<?php echo $model->id; ?>_hrs" value=16 <?php if(in_array("BIT_HOUR", $timeArray)): ?> checked <?php endif; ?> /></td>
				<td style="padding-left:0; padding-right:0;" align="left"><label for="<?php echo $model->id; ?>_hrs">Hours</label></td>
				<td style="padding-left:4px; padding-right:0;"><input type="checkbox" class="time-<?php echo $model->id ?>" id="<?php echo $model->id; ?>_mins" value=32 <?php if(in_array("BIT_MINUTE", $timeArray)): ?> checked <?php endif; ?> /></td>
				<td style="padding-left:0; padding-right:0;"align="left"><label for="<?php echo $model->id; ?>_mins">Minutes</label></td>
			</tr>
		</table>
		</div>

        <div class="panel-<?php echo $model->id; ?>" id="NAME_GENERATOR" style="<?php if(!strstr($model->subjectType, "ALTER_PAIR")){ ?>display:none<?php } ?>">
            Minimum Alters: <?php echo $form->textField($model,'minLiteral', array('style'=>'width:60px; margin:0', "id"=>$model->id .'-minLiteral')); ?>
            Minimum Alters: <?php echo $form->textField($model,'maxLiteral', array('style'=>'width:60px; margin:0', "id"=>$model->id .'-maxLiteral')); ?>
        </div>

	<div id="ALTER" style="<?php if(!strstr($model->subjectType, "ALTER")){ ?>display:none<?php } ?>">

		<div id="ALTER_PAIR" style="<?php if(!strstr($model->subjectType, "ALTER_PAIR")){ ?>display:none<?php } ?>">
			<div class="row">
				<?php echo $form->labelEx($model,'symmetric'); ?>
				<?php echo $form->checkBox($model,'symmetric'); ?>
				<?php echo $form->error($model,'symmetric'); ?>
			</div>
		</div>



		<div class="panel-<?php echo $model->id; ?>" id="ALTER_STYLE" style="display:none">
				<table border="0" bgcolor="#dddddd" >
				<tr>
				<td style="padding-left:0; padding-right:0; white-space:nowrap;" align="right"><label for="<?php echo $model->id . "_" . "withListRange"; ?>">Use List Limit?</label>
					<?php echo $form->checkBox($model,'withListRange', array("id"=>$model->id . "_" . "withListRange")); ?></td>
				<td style="padding-left:4px; padding-right:0;"><label for="<?php echo $model->id . "_" . "minListRange"; ?>">Min:</label>
					<?php echo $form->textField($model,'minListRange', array("id"=>$model->id . "_" . "minListRange"), array('style'=>'width:30px')); ?><br style="clear:both">
				<label for="<?php echo $model->id . "_" . "maxListRange"; ?>">Max:</label>
					<?php echo $form->textField($model,'maxListRange', array("id"=>$model->id . "_" . "maxListRange"), array('style'=>'width:30px')); ?></td>
				</tr><tr>
				<td colspan="2" style="padding-left:5px; padding-right:0; white-space:nowrap;" align="right">Count Response:
			<?php
		$criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"questionId = " . $model->id,
			'order'=>'ordering',
		);
			?>

			<?php echo $form->dropdownlist(
				$model,
				'listRangeString',
				CHtml::listData(QuestionOption::model()->findAll($criteria), 'id', 'name'),
				array('empty' => 'Choose One')
			); ?></td>
				</tr>
				<tr>
				<td style="padding-left:0; padding-right:0; white-space:nowrap;">PAGE-LEVEL Buttons: </td>
				<td style="padding-left:4px; padding-right:0;"><label for="<?php echo $model->id . "_" . "pageLevelDontKnowButton"; ?>">DON'T KNOW</label>
					<?php echo $form->checkBox($model,'pageLevelDontKnowButton', array("id"=>$model->id . "_" . "pageLevelDontKnowButton")); ?><br style="clear:both">
				<label for="<?php echo $model->id . "_" . "pageLevelRefuseButton"; ?>">REFUSE</label>
					<?php echo $form->checkBox($model,'pageLevelRefuseButton', array("id"=>$model->id . "_" . "pageLevelRefuseButton")); ?><br style="clear:both">
				<label for="<?php echo $model->id . "_" . "noneButton"; ?>">None</label>
					<?php echo $form->checkBox($model,'noneButton', array("id"=>$model->id . "_" . "noneButton")); ?><br style="clear:both">
				<label for="<?php echo $model->id . "_" . "allButton"; ?>">Set Alls</label>
					<?php echo $form->checkBox($model,'allButton', array("id"=>$model->id . "_" . "allButton")); ?></td>
				</tr>
			</table>
		</div>
	</div>

	<br style="clear:both" />
	<?php echo $form->hiddenField($model,'networkParams',array('value'=>$model->networkParams)); ?>

	<?php if($model->subjectType == "NETWORK"): ?>


		<div class="row">
			Alters are adjacent when:
		<?php
            #OK FOR SQL INJECTION
			$questionIds = q("SELECT id FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = ".$model->studyId)->queryColumn();
			$questionIds = implode(",", $questionIds);
			if(!$questionIds)
				$questionIds = 0;
            #OK FOR SQL INJECTION
			$alter_pair_expression_ids = q("SELECT id FROM expression WHERE studyId = " . $model->studyId . " AND questionId in (" . $questionIds . ")")->queryColumn();
			$all_expression_ids = $alter_pair_expression_ids;
			foreach($alter_pair_expression_ids as $id){
                #OK FOR SQL INJECTION
				$all_expression_ids = array_merge(q("SELECT id FROM expression WHERE FIND_IN_SET($id, value)")->queryColumn(),$all_expression_ids);
			}
			if($all_expression_ids){
			$alter_pair_expressions = q("SELECT * FROM expression WHERE id in (" . implode(",",$all_expression_ids) . ")")->queryAll();
				$list = array();
				foreach($alter_pair_expressions as $expression){
					$list[$expression['id']] = substr($expression['name'], 0 , 30);
				}
			}else{
				$list = array();
			}
		echo $form->dropdownlist(
			$model,
			'networkRelationshipExprId',
			$list,
			array('empty' => 'Choose One')
		); ?>
		<?php echo $form->error($model,'networkRelationshipExprId'); ?>
		</div>

	<div id="visualize-bar" class="col-sm-8 pull-left">

	<?php
	$this->widget('plugins.visualize', array('method'=>'nodecolor', 'id'=>$model->studyId, 'params'=>$model->networkParams));
		$this->widget('plugins.visualize', array('method'=>'nodeshape', 'id'=>$model->studyId, 'params'=>$model->networkParams));
		$this->widget('plugins.visualize', array('method'=>'nodesize', 'id'=>$model->studyId, 'params'=>$model->networkParams));
		$this->widget('plugins.visualize', array('method'=>'edgecolor', 'id'=>$model->studyId, 'params'=>$model->networkParams));
		$this->widget('plugins.visualize', array('method'=>'edgesize', 'id'=>$model->studyId, 'params'=>$model->networkParams));

	?>
	</div>
	<script>
function refresh(container){
	var params = new Object;
	if(typeof container == "undefined")
		container = $('body');
	if($('#nodeColorSelect option:selected', container).val()){
		var nodeColor = new Object;
		var question = $('#nodeColorSelect option:selected', container).val();
		nodeColor['questionId'] = question.replace('_nodeColor','');
		nodeColor['options'] = [];
		$("#" + question + " select", container).each(function(index){
			nodeColor['options'].push({"id":$(this).attr('id'),"color":$("option:selected", this).val()});
		});
		params['nodeColor'] = nodeColor;
	}
	if($('#nodeShapeSelect option:selected', container).val()){
		var nodeShape = new Object;
		var question = $('#nodeShapeSelect option:selected', container).val();
		nodeShape['questionId'] = question.replace('_nodeShape','');
		nodeShape['options'] = [];
		$("#" + question + " select", container).each(function(index){
			nodeShape['options'].push({"id":$(this).attr('id'),"shape":$("option:selected", this).val()});
		});
		params['nodeShape'] = nodeShape;
	}
	if($('#nodeSizeSelect option:selected', container).val()){
		var nodeSize = new Object;
		var question = $('#nodeSizeSelect option:selected', container).val();
		nodeSize['questionId'] = question.replace('_nodeSize','');
		nodeSize['options'] = [];
		$( "#" + question + " select", container).each(function(index){
			nodeSize['options'].push({"id":$(this).attr('id'),"size":$("option:selected", this).val()});
		});
		params['nodeSize'] = nodeSize;
	}
	if($('#edgeColorSelect option:selected', container).val()){
		var edgeColor = new Object;
		var question = $('#edgeColorSelect option:selected', container).val();
		edgeColor['questionId'] = question.replace('_edgeColor','');
		edgeColor['options'] = [];
		$("#" + question + " select", container).each(function(index){
			edgeColor['options'].push({"id":$(this).attr('id'),"color":$("option:selected", this).val()});
		});
		params['edgeColor'] = edgeColor;
	}
	if($('#edgeSizeSelect option:selected', container).val()){
		var edgeSize = new Object;
		var question = $('#edgeSizeSelect option:selected', container).val();
		edgeSize['questionId'] = question.replace('_edgeSize','');
		edgeSize['options'] = [];
		$("#" + question + " select", container).each(function(index){
			edgeSize['options'].push({"id":$(this).attr('id'),"size":$("option:selected", this).val()});
		});
		params['edgeSize'] = edgeSize;
	}
	console.log(JSON.stringify(params));

	$("#Graph_params").val(JSON.stringify(params));
	return JSON.stringify(params);
}
	$('#<?= $model->id; ?> #visualize-bar select').change(function(){
		$('#<?= $model->id; ?> #Question_networkParams').val(refresh($('#<?= $model->id; ?> #visualize-bar')));
	});
	</script>
	<?php endif;?>

</div>

	<div class="row" style="width:50%; float:left; padding:10px 20px">
		<?php echo $form->labelEx($model,'prompt', array('onclick'=>'$(".nicEdit-main", this.parentNode)[0].focus()')); ?>
		<div class="audioPlay" id="<?= $model->subjectType; ?>_<?= $model->id; ?>"><?php if(file_exists(Yii::app()->basePath."/../audio/".$model->studyId . "/" . $model->subjectType . "/" . $model->id . ".mp3")): ?><a class="play-sound" onclick="playSound($(this).attr('file'))" href="#" file="/audio/<?= $model->studyId . "/" . $model->subjectType . "/" . $model->id . ".mp3"; ?>"><span class="fui-volume"></span></a><?php endif; ?></div>
		<?php if(!$model->isNewRecord):?>
		<a class="btn btn-primary pull-right btn-xs" data-toggle="modal" data-target="#myModal" href="/authoring/uploadaudio?type=<?= $model->subjectType; ?>&id=<?= $model->id; ?>&studyId=<?= $model->studyId; ?>">Upload Audio</a>
		<?php endif;?>
		<?php echo $form->textArea($model,'prompt',array('rows'=>6, 'cols'=>50, 'id'=>'prompt'.$model->id)); ?>
		<?php echo $form->error($model,'prompt'); ?>
		<br>
		<?php echo $form->labelEx($model,'preface', array('onclick'=>'$(".nicEdit-main", this.parentNode)[1].focus()')); ?>
		<div class="audioPlay" id="preface_<?= $model->id; ?>"><?php if(file_exists(Yii::app()->basePath."/../audio/".$model->studyId . "/PREFACE/" . $model->id . ".mp3")): ?><a class="play-sound" onclick="playSound($(this).attr('file'))" href="#" file="/audio/<?= $model->studyId . "/PREFACE/" . $model->id . ".mp3"; ?>"><span class="fui-volume"></span></a><?php endif; ?></div>
		<?php if(!$model->isNewRecord):?>
		<a class="btn btn-primary pull-right btn-xs" data-toggle="modal" data-target="#myModal" href="/authoring/uploadaudio?type=PREFACE&id=<?= $model->id; ?>&studyId=<?= $model->studyId; ?>">Upload Audio</a>
		<?php endif;?>
		<?php echo $form->textArea($model,'preface',array('rows'=>6, 'cols'=>50, 'id'=>'preface'.$model->id)); ?>
		<?php echo $form->error($model,'preface'); ?>
		<br>
		<?php echo $form->labelEx($model,'Leaf and Stem'); ?>
		<?php echo $form->textArea($model,'citation',array('rows'=>6, 'cols'=>50, 'id'=>'citation'.$model->id)); ?>
		<?php echo $form->error($model,'citation'); ?>
		<br>
		<?php echo $form->labelEx($model,'Javascript'); ?>
		<?php echo $form->textArea($model,'javascript',array('rows'=>6, 'cols'=>50, 'id'=>'javascript'.$model->id)); ?>
		<?php echo $form->error($model,'javascript'); ?>
	</div>

<?php /*

	<div class="row">
		<?php echo $form->labelEx($model,'allOptionString'); ?>
		<?php echo $form->textField($model,'allOptionString'); ?>
		<?php echo $form->error($model,'allOptionString'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uselfExpression'); ?>
		<?php echo $form->textField($model,'uselfExpression'); ?>
		<?php echo $form->error($model,'uselfExpression'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'keepOnSamePage'); ?>
		<?php echo $form->textField($model,'keepOnSamePage'); ?>
		<?php echo $form->error($model,'keepOnSamePage'); ?>
	</div>

*/
?>
</div>
<div class="btn-group" style="padding:5px">
<?php if($ajax == true): ?>
	<?php echo CHtml::ajaxSubmitButton (
		$model->isNewRecord ? 'Create' : 'Save',
		CController::createUrl('ajaxupdate?_'.uniqid()),
		array(
			'success' => 'js:function(data){data=data.split(";;;");console.log(data);$("#' . $model->id .' > h3").html($("#' . $model->id .' > h3").html().replace(data[0], data[1]));$(".optionLink").click(function(e){clickOption[$(this).parent().parent().attr("id")] = true;});$("#' . $model->id .' > h3").click();}',
		),
		array('id'=>uniqid(), 'live'=>false, 'class'=>"btn btn-success btn-xs"));
	?>
<?php else: ?>
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>"btn btn-success btn-xs")); ?>
<?php endif; ?>
<?php if(!$model->isNewRecord): ?>
<?php

	echo CHtml::ajaxButton ("Delete",
		CController::createUrl('ajaxdelete', array('form'=>'_form_question', 'Question[id]'=>$model->id)),
		array('success' => 'js:function(data){$("#question-list").html(data);initList();}'),
		array('id' => 'delete-'.$model->id, 'live'=>false, 'class'=>"btn btn-danger btn-xs")
	);

	echo CHtml::ajaxButton (CHtml::encode('Preview'),
		CController::createUrl('preview', array('questionId'=>$model->id)),
		array('update' => '#data-'.$model->id),
		array('id' => uniqid(), 'live'=>false, 'class'=>"btn btn-info btn-xs")
	);

    if($model->subjectType == "NAME_GENERATOR"){
        echo CHtml::ajaxButton (CHtml::encode('Alter Prompts'),
    		CController::createUrl('alterprompt', array('questionId'=>$model->id, 'studyId'=>$model->studyId)),
    		array('update' => '#data-'.$model->id),
    		array('id' => uniqid(), 'live'=>false, 'class'=>"btn btn-default btn-xs")
    	);
    }

	echo CHtml::button(
		CHtml::encode('Duplicate'),
		array("submit"=>CController::createUrl('duplicate', array('questionId'=>$model->id)), 'class'=>"btn btn-warning btn-xs")
	);

	if($model->subjectType == "NETWORK"){
		echo CHtml::ajaxButton (CHtml::encode('Legend'),
			CController::createUrl('ajaxload', array('questionId'=>$model->id, 'form'=>'_form_legend', 'studyId'=>$model->studyId)),
			array('update' => '#data-'.$model->id),
			array('id' => uniqid(), 'live'=>false, 'class'=>"btn btn-default btn-xs")
		);
	}
?>
<?php endif; ?>
</div>
<?php $this->endWidget(); ?>

<script>
$(function(){
	$('#prompt<?php echo $model->id;?>').summernote({
		toolbar:noteBar,
		height:200,
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
		onChange: function(contents, $editable) {
			$('#prompt<?php echo $model->id;?>').val(rebuildEgowebTags(contents));
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#preface<?php echo $model->id;?>').summernote({
		toolbar:noteBar,
		height:200,
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
		onChange: function(contents, $editable) {
			$('#preface<?php echo $model->id;?>').val(rebuildEgowebTags(contents));
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#citation<?php echo $model->id;?>').summernote({
		toolbar:noteBar,
		height:200,
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
		onChange: function(contents, $editable) {
			$('#citation<?php echo $model->id;?>').val(rebuildEgowebTags(contents));
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
});
</script>
