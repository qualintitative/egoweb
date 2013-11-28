<?php
/* @var $this QuestionController */
/* @var $data Question */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('random_key')); ?>:</b>
	<?php echo CHtml::encode($data->random_key); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('active')); ?>:</b>
	<?php echo CHtml::encode($data->active); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('title')); ?>:</b>
	<?php echo CHtml::encode($data->title); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('promptText')); ?>:</b>
	<?php echo CHtml::encode($data->promptText); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('prompt')); ?>:</b>
	<?php echo CHtml::encode($data->prompt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('prefaceText')); ?>:</b>
	<?php echo CHtml::encode($data->prefaceText); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('preface')); ?>:</b>
	<?php echo CHtml::encode($data->preface); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('citationText')); ?>:</b>
	<?php echo CHtml::encode($data->citationText); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('citation')); ?>:</b>
	<?php echo CHtml::encode($data->citation); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('subjectType')); ?>:</b>
	<?php echo CHtml::encode($data->subjectType); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('answerType')); ?>:</b>
	<?php echo CHtml::encode($data->answerType); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('askingStyleList')); ?>:</b>
	<?php echo CHtml::encode($data->askingStyleList); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('ordering')); ?>:</b>
	<?php echo CHtml::encode($data->ordering); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('otherSpecify')); ?>:</b>
	<?php echo CHtml::encode($data->otherSpecify); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('noneButton')); ?>:</b>
	<?php echo CHtml::encode($data->noneButton); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('allButton')); ?>:</b>
	<?php echo CHtml::encode($data->allButton); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('pageLevelDontKnowButton')); ?>:</b>
	<?php echo CHtml::encode($data->pageLevelDontKnowButton); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('pageLevelRefuseButton')); ?>:</b>
	<?php echo CHtml::encode($data->pageLevelRefuseButton); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('dontKnowButton')); ?>:</b>
	<?php echo CHtml::encode($data->dontKnowButton); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('refuseButton')); ?>:</b>
	<?php echo CHtml::encode($data->refuseButton); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('allOptionString')); ?>:</b>
	<?php echo CHtml::encode($data->allOptionString); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('uselfExpression')); ?>:</b>
	<?php echo CHtml::encode($data->uselfExpression); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('minLimitType')); ?>:</b>
	<?php echo CHtml::encode($data->minLimitType); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('minLiteral')); ?>:</b>
	<?php echo CHtml::encode($data->minLiteral); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('minPrevQues')); ?>:</b>
	<?php echo CHtml::encode($data->minPrevQues); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('maxLimitType')); ?>:</b>
	<?php echo CHtml::encode($data->maxLimitType); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('maxLiteral')); ?>:</b>
	<?php echo CHtml::encode($data->maxLiteral); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('maxPrevQues')); ?>:</b>
	<?php echo CHtml::encode($data->maxPrevQues); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('minCheckableBoxes')); ?>:</b>
	<?php echo CHtml::encode($data->minCheckableBoxes); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('maxCheckableBoxes')); ?>:</b>
	<?php echo CHtml::encode($data->maxCheckableBoxes); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('withListRange')); ?>:</b>
	<?php echo CHtml::encode($data->withListRange); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('listRangeString')); ?>:</b>
	<?php echo CHtml::encode($data->listRangeString); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('minListRange')); ?>:</b>
	<?php echo CHtml::encode($data->minListRange); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('maxListRange')); ?>:</b>
	<?php echo CHtml::encode($data->maxListRange); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('timeUnits')); ?>:</b>
	<?php echo CHtml::encode($data->timeUnits); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('symmetric')); ?>:</b>
	<?php echo CHtml::encode($data->symmetric); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('keepOnSamePage')); ?>:</b>
	<?php echo CHtml::encode($data->keepOnSamePage); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('studyId')); ?>:</b>
	<?php echo CHtml::encode($data->studyId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('answerReasonExpressionId')); ?>:</b>
	<?php echo CHtml::encode($data->answerReasonExpressionId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('networkRelationshipExprId')); ?>:</b>
	<?php echo CHtml::encode($data->networkRelationshipExprId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('networkNShapeQId')); ?>:</b>
	<?php echo CHtml::encode($data->networkNShapeQId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('networkNColorQId')); ?>:</b>
	<?php echo CHtml::encode($data->networkNColorQId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('networkNSizeQId')); ?>:</b>
	<?php echo CHtml::encode($data->networkNSizeQId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('networkEColorQId')); ?>:</b>
	<?php echo CHtml::encode($data->networkEColorQId); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('networkESizeQId')); ?>:</b>
	<?php echo CHtml::encode($data->networkESizeQId); ?>
	<br />

	*/ ?>

</div>