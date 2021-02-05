<?php
$study = Study::model()->findByPk($studyId);
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
    "htmlOptions"=>array("class"=>"form-horizontal"),

));
echo $form->hiddenField($model, 'id', array('value'=>$model->id));
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'value', array('value'=>$model->value));
echo $form->hiddenField($model, 'type', array('value'=>'Name Generator'));

?>
<div class="form-group">
    <?php echo $form->labelEx($model,'name', array('class'=>'control-label col-sm-2')); ?>
    <div class="col-sm-8">
        <?php echo $form->textField($model,'name', array('value'=>$model->name, 'class'=>'form-control')); ?>
    </div>
</div>

Expression is true for an answer that contains any of the selected options below:
<br>
<?php
if($study->multiSessionEgoId){
    $criteria = array(
        "condition"=>"title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")",
    );
    $questions = Question::model()->findAll($criteria);
    $multiIds = array();
    foreach($questions as $q){
        $multiIds[] = $q->studyId;
    }
	$criteria=array(
		'condition'=>"studyId in (" . implode(",", $multiIds) . ")  AND subjectType = 'NAME_GENERATOR'",
        'order'=>'ordering',
	);
} else {
	$criteria=array(
		'condition'=>"studyId = " . $studyId . " AND subjectType = 'NAME_GENERATOR'",
		'order'=>'ordering',
	);
}
echo CHtml::activeHiddenField($model, 'questionId', array('value'=>$question->id));
$qList = array();
$questions = Question::model()->findAll($criteria);
foreach($questions as $q){
    $m_study = Study::model()->findByPK($q->studyId);
	$qList[$q->id] = $m_study->name . ":" . $q->title;
}


    $selected = explode(',', $model->value);
    echo CHtml::CheckboxList(
        'valueList',
        $selected,
        $qList,
        array(
            'separator'=>'',
            'class'=>'valueList',
            'template' => '<div>{input} {label}</div>',
        )
    );


// converts multiple selection checkboxes into answer value
echo "
<script>
jQuery('.valueList').change(function() {
    $('#Expression_value').val('');
    $('.valueList').each(function() {
        if($(this).is(':checked')){
            if($('#Expression_value').val() != '')
                $('#Expression_value').val($('#Expression_value').val() + ',' + $(this).val());
            else
                $('#Expression_value').val($(this).val());
        }
    });
    console.log($('#Expression_value').val());
});
</script>";
?>
<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<?php if($model->id): ?><button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button><?php endif; ?>
</div>
<?php
$this->endWidget();
?>
