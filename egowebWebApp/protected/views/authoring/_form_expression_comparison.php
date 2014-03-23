<span>Expression about <?php echo $expression->name; ?></span>
<?php
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-text-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,

));
if($model->value == "")
	$model->value = "1:" . $expression->id;

list($compare, $expressiond->id) = preg_split('/:/',$model->value);
echo $form->hiddenField($model, 'id', array('value'=>$model->id));
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'value', array('value'=>$model->value));
echo $form->hiddenField($model, 'type', array('value'=>'Comparison'));


echo "<script>
jQuery('#compare').change(function() {
	if($(this).val() == '')
		$(this).val(1);
    $('#Expression_value').val($(this).val() + ':' + ". $expression->id . ");
    console.log($('#Expression_value').val());
});
</script>";
?>
<?php echo $form->labelEx($model,'name'); ?>
<?php echo $form->textField($model,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'name'); ?>

<br />
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

<input type="submit" value="Save"/>


<?php $this->endWidget(); ?>
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})">delete</button>