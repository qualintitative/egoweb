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

<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('random_key')); ?>:</b>
	<?php echo CHtml::encode($data->random_key); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('active')); ?>:</b>
	<?php echo CHtml::encode($data->active); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('introduction')); ?>:</b>
	<?php echo CHtml::encode($data->introduction); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('egoIdPrompt')); ?>:</b>
	<?php echo CHtml::encode($data->egoIdPrompt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('alterPrompt')); ?>:</b>
	<?php echo CHtml::encode($data->alterPrompt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('conclusion')); ?>:</b>
	<?php echo CHtml::encode($data->conclusion); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('egoIdPrompt')); ?>:</b>
	<?php echo CHtml::encode($data->egoIdPrompt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('alterPromptText')); ?>:</b>
	<?php echo CHtml::encode($data->alterPromptText); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('alterPrompt')); ?>:</b>
	<?php echo CHtml::encode($data->alterPrompt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('conclusionText')); ?>:</b>
	<?php echo CHtml::encode($data->conclusionText); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('conclusion')); ?>:</b>
	<?php echo CHtml::encode($data->conclusion); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('minAlters')); ?>:</b>
	<?php echo CHtml::encode($data->minAlters); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('maxAlters')); ?>:</b>
	<?php echo CHtml::encode($data->maxAlters); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('adjacencyExpressionId')); ?>:</b>
	<?php echo CHtml::encode($data->adjacencyExpressionId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('valueRefusal')); ?>:</b>
	<?php echo CHtml::encode($data->valueRefusal); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('valueDontKnow')); ?>:</b>
	<?php echo CHtml::encode($data->valueDontKnow); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('valueLogicalSkip')); ?>:</b>
	<?php echo CHtml::encode($data->valueLogicalSkip); ?>
	<br />

	*/ ?>
