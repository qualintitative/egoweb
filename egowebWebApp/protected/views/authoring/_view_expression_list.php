<?php
/* @var $this StudyController */
/* @var $data Study */
?>
	<b><?php
	if($data->type == "Text" || $data->type == "Number" || $data->type == "Selection")
		$form = "_form_expression_text";
	else
		$form = "_form_expression_". strtolower($data->type);
	if($data->type == "Comparison"){
		list($value, $expressionId) = preg_split('/:/', $data->value);
		$expressionId = "&expressionId=". $expressionId;
	}else{
		$expressionId = "";
	}
	echo
    CHtml::ajaxLink (CHtml::encode($data->name),
        CController::createUrl('ajaxload'),
        array('update' => '#Expression', 'data'=>'form='.$form.'&studyId='.$data->studyId.'&id='.$data->id.'&questionId='.$data->questionId.$expressionId),
        array('id'=>uniqid(), 'live'=>false)

    );
?>
</b>
<br />
<br />
