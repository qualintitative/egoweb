<h4>Comparison Expression 
<span>about <?php echo $expression->name; ?></span>
<?php

$criteria=new CDbCriteria;
$criteria=array(
    'condition'=>"studyId = " . $studyId . " AND type='Counting'",
);

echo CHtml::dropdownlist(
    'expressionId',
    '',
    CHtml::listData(Expression::model()->findAll($criteria), 'id', 'name'),
    array('empty' => 'Choose One')
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

?>
</h4>
<script>
jQuery('#compare').change(function() {
	if($(this).val() == '')
		$(this).val(1);
    $('#Expression_value').val($(this).val() + ':' + <?php echo $expression->id; ?>);
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

<span>Expression is true for an answer that is</span>
<?php
echo $form->dropdownlist($model,
    'operator',
    array(
        'Greater'=>'Greater',
        'GreaterOrEqual'=>'Greater Or Equal',
        'Equals'=>'Equals',
        'LessOrEqual'=>'Less Or Equal',
        'Less'=>'Less'
    )
);
?>
<?php
	echo CHtml::textField("compare", $compare);
?>
<br clear=all />

<?php $this->endWidget(); ?>
<div class="btn-group">
<input type="submit" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button>
</div>