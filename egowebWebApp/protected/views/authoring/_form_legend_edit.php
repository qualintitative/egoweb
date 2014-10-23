
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'add-legend-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('class'=>'form-horizontal'),
));

echo $form->hiddenField($model,'id',array('value'=>$model->id));
echo $form->hiddenField($model,'questionId',array('value'=>$questionId));
echo $form->hiddenField($model,'studyId',array('value'=>$studyId));

echo '<div class="form-group">';
echo $form->labelEx($model,'label', array('class'=>'control-label col-sm-4'));
echo '<div class="col-sm-8">';
echo $form->textField($model,'label', array('class'=>'form-control'));
echo $form->error($model,'label');
echo "</div>";
echo "</div>";

echo '<div class="form-group">';
echo $form->labelEx($model,'shape', array('class'=>'control-label col-sm-4'));
echo '<div class="col-sm-8">';
echo $form->dropdownlist(
	$model,
	'shape',
	array(
		'line'=>'line',
		'circle'=>'circle',
		'star'=>'star',
		'diamond'=>'diamond',
		'cross'=>'cross',
		'equilateral'=>'triangle',
		'square'=>'square',
	),
	array('class'=>'form-control')
);
echo $form->error($model,'shape');
echo "</div>";
echo "</div>";

echo '<div class="form-group">';
echo $form->labelEx($model,'color', array('class'=>'control-label col-sm-4'));
echo '<div class="col-sm-8">';
echo $form->dropdownlist(
	$model,
	'color',
	array(
		'#000'=>'black',
		'#ccc'=>'gray',
		'#07f'=>'blue',
		'#0c0'=>'green',
		'#F80'=>'orange',
		'#fa0'=>'yellow',
		'#f00'=>'red',
		'#c0f'=>'purple',
	), array('class'=>'form-control')
);
echo $form->error($model,'color');
echo "</div>";
echo "</div>";

echo '<div class="form-group">';
echo $form->labelEx($model,'size', array('class'=>'control-label col-sm-4'));
echo '<div class="col-sm-8">';
echo $form->dropdownlist(
	$model,
	'size',
	array(
		'1'=>'1',
		'2'=>'2',
		'3'=>'3',
		'4'=>'4',
		'5'=>'5',
		'6'=>'6',
		'7'=>'7',
		'8'=>'8',
	), array('class'=>'form-control')
);echo $form->error($model,'size');
echo "</div>";
echo "</div>";

echo CHtml::ajaxSubmitButton (

	$model->isNewRecord ? "Add" : "Save",
	CController::createUrl('ajaxupdate?_='.uniqid()),
	array('update' => '#data-'.$questionId),
	array('id'=>uniqid(), 'live'=>true, 'class'=>'btn btn-primary col-sm-offset-4 btn-sm')
);

$this->endWidget();

?>