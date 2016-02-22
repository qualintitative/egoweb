<?php
/* @var $this InterviewingController */
/* @var $data Answer */
?>


	<?php echo CHtml::ajaxLink(
		CHtml::encode(Study::getName($data->id)),
		Yii::app()->createUrl('interviewing/study/'.$data->id),
		array('update'=>'#interviewList')
		); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('valueText')); ?>:</b>
	<?php echo CHtml::encode($data->valueText); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('value')); ?>:</b>
	<?php echo CHtml::encode($data->value); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('otherSpecifyText')); ?>:</b>
	<?php echo CHtml::encode($data->otherSpecifyText); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('skipReason')); ?>:</b>
	<?php echo CHtml::encode($data->skipReason); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('studyId')); ?>:</b>
	<?php echo CHtml::encode($data->studyId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('questionType')); ?>:</b>
	<?php echo CHtml::encode($data->questionType); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('answerType')); ?>:</b>
	<?php echo CHtml::encode($data->answerType); ?>
	<br />

	*/ ?>

