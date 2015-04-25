<h4>Comparison Expression 
<span>about 
<?php

$criteria=new CDbCriteria;
$criteria=array(
    'condition'=>"studyId = " . $studyId . " AND type='Counting'",
);

list($times,$expressionId) = explode(":", $model->value);


echo CHtml::dropdownlist(
    'expressionId',
    $expressionId,
    CHtml::listData(Expression::model()->findAll($criteria), 'id', 'name'),
    array('empty' => 'Choose One', "onChange"=>"setExpression(\$(this).val())")
);
if($model->value == "")
	$model->value = "1:" . $expression->id;

list($compare, $expression->id) = preg_split('/:/',$model->value);
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
    "htmlOptions"=>array("class"=>"form-horizontal"),

));
echo $form->hiddenField($model, 'id', array('value'=>$model->id));
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'value', array('value'=>$model->value));
echo $form->hiddenField($model, 'type', array('value'=>'Comparison'));

?></span>
</h4>
<script>
function setExpression(expressionId){
    $('#Expression_value').val($("#compare").val() + ':' + expressionId);
    console.log($('#Expression_value').val());
}
jQuery('#compare').change(function() {
	if($(this).val() == '')
		$(this).val(1);
    $('#Expression_value').val($(this).val() + ':' + $("#expressionId").val());
    console.log($('#Expression_value').val());
});
</script>

<div class="form-group">
    <?php echo $form->labelEx($model,'name', array('class'=>'control-label col-sm-2')); ?>
    <div class="col-sm-8">
        <?php echo $form->textField($model,'name', array('class'=>'form-control')); ?>
    </div>
</div>

<br clear=all>
<br clear=all>

<span>Expression is true for an answer that is</span>
<?php
echo $form->dropdownlist($model,
    'operator',
    array(
        'Greater'=>'Greater Than',
        'GreaterOrEqual'=>'Greater Or Equal To',
        'Equals'=>'Equals',
        'LessOrEqual'=>'Less Or Equal To',
        'Less'=>'Less Than'
    )
);
?>
<?php
	echo CHtml::textField("compare", $compare);
?>
<br clear=all />

<?php $this->endWidget(); ?>
<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button>
</div>