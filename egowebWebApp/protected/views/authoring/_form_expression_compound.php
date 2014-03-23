<?php
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-compound-form',
	'enableAjaxValidation'=>false,
	'action'=>'/authoring/expression/'.$studyId,

));
echo $form->hiddenField($model, 'id', array('value'=>$model->id));
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'questionId', array('value'=>$question->id));
echo $form->hiddenField($model, 'value', array('value'=>$model->value));
echo $form->hiddenField($model, 'type', array('value'=>'Compound'));

echo "
<script>
jQuery('.expressionList').change(function() {
	$('#Expression_value').val('');
	$('.expressionList').each(function() {
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

<?php echo $form->labelEx($model,'name'); ?>
<?php echo $form->textField($model,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'name'); ?>
<br />

Expression is true if
<?php
echo $form->dropdownlist($model,
	'operator',
	array(
		'Some'=>'Some',
		'All'=>'All',
		'None'=>'None',
	)
);
?>
of the selected expressions below are true:
<br />

<div id="Expressions">
<?php
	$selected = explode(',', $model->value);
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"type != 'Counting' AND id != " . (($model->id) ? $model->id : 0 ). " AND studyId = " . $studyId ." AND id != '" . $model->id ."'",
			);
	echo CHtml::CheckboxList(
		'expressionList',
		$selected,
		CHtml::listData(Expression::model()->findAll($criteria), 'id', 'name'),
		array(
			'separator'=>'<br>',
			'class'=>'expressionList',
		)
	);

?>
</div>

<br clear=all />
<input type="submit" value="Save"/>
<?php $this->endWidget(); ?>
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})">delete</button>