<script>
function changeEQ(questionId){
    $.get("/authoring/ajaxload?form=_form_expression_question&questionId="
        + questionId + "&expressionId=<?php echo $model->id; ?>",
        function(data){
            $("#expressionQ").html(data);
        }
    )
}
</script>
<h4>Simple Expression 
<span>about <?php
				if(isset($_GET['questionId']) && is_numeric($_GET['questionId']) && $_GET['questionId'] != 0)
					$question = Question::model()->findByPk((int)$_GET['questionId']);
				else
					$question = new Question;
$criteria=new CDbCriteria;
$multi = q("SELECT multiSessionEgoId FROM study WHERE id = " . $studyId)->queryScalar();
if($multi){
    #OK FOR SQL INJECTION
	$multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " .$multi . ")")->queryColumn();
    #OK FOR SQL INJECTION
    $studyIds = q("SELECT id FROM study WHERE multiSessionEgoId in (" . implode(",", $multiIds) . ")")->queryColumn();
	$criteria=array(
		'condition'=>"studyId in (" . implode(",", $studyIds) . ")",
	);
} else {
	$criteria=array(
		'condition'=>"studyId = " . $studyId,
		'order'=>'FIELD(subjectType, "EGO_ID", "EGO","ALTER", "ALTER_PAIR", "NETWORK"), ordering',
	);
}
$questions = Question::model()->findAll($criteria);
$qList = array();
foreach($questions as $q){
	$studyName = q("SELECT name FROM study WHERE id = " . $q->studyId)->queryScalar();
	$qList[$q->id] = $studyName . ":" . $q->title;
}
echo CHtml::dropdownlist(
	'questionId',
	$model->questionId,
	$qList,
	array('empty' => 'Choose One', 'onChange'=>"changeEQ(\$(this).val());")
);
 ?></span></h4>
<?php
// text expression form
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
    "htmlOptions"=>array("class"=>"form-horizontal")
));

?>

<?php echo $form->hiddenField($model,'studyId', array('value'=>$studyId)); ?>

<div class="form-group">
    <?php echo $form->labelEx($model,'name', array('class'=>'control-label col-sm-2')); ?>
    <div class="col-sm-8">
        <?php echo $form->textField($model,'name', array('class'=>'form-control')); ?>
    </div>
</div>

<br clear=all>

<div id="expressionQ">
<?php
    $this->renderPartial("_form_expression_question", array('model'=>$model, 'question'=>$question), false, true);
?>
</div>

<br />

Expression is
<?php
echo $form->dropdownlist($model,
    'resultForUnanswered',
    array(
        '0'=>"False",
        "1"=>"True"
    )
);
?>
if the question is unanswered.

<br clear=all />
<br clear=all />
<?php $this->endWidget(); ?>

<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button>
</div>