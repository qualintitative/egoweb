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
<span>Expression about <?php
				if(isset($_GET['questionId']) && is_numeric($_GET['questionId']) && $_GET['questionId'] != 0)
					$question = Question::model()->findByPk((int)$_GET['questionId']);
				else
					$question = new Question;
$criteria=new CDbCriteria;
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
 ?></span>
<?php
// text expression form
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-text-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
));

?>

<?php echo $form->labelEx($model,'name'); ?>
<?php echo $form->textField($model,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'name'); ?>

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

<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs"/>
<?php $this->endWidget(); ?>
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button>
</div>