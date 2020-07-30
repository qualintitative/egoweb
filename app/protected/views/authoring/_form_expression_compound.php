<h4>Compound Expression</h4>
<?php
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-form',
	'enableAjaxValidation'=>false,
	'action'=>'/authoring/expression/'.$studyId,
    "htmlOptions"=>array("class"=>"form-horizontal"),

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

<div class="form-group">
    <?php echo $form->labelEx($model,'name', array('class'=>'control-label col-sm-2')); ?>
    <div class="col-sm-8">
        <?php echo $form->textField($model,'name', array('class'=>'form-control')); ?>
    </div>
</div>

<br clear=all>

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
			'template' => '<div>{input} {label}</div>',
			'separator'=>'',
			'class'=>'expressionList',
		)
	);

?>

<br clear=all />
<?php $this->endWidget(); ?>
<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<?php if($model->id): ?><button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button><?php endif;?>
</div>