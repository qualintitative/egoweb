<?php
/* @var $this QuestionController */
/* @var $model Question */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'question-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'random_key'); ?>
		<?php echo $form->textField($model,'random_key'); ?>
		<?php echo $form->error($model,'random_key'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'active'); ?>
		<?php echo $form->textField($model,'active'); ?>
		<?php echo $form->error($model,'active'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textArea($model,'title',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'promptText'); ?>
		<?php echo $form->textArea($model,'promptText',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'promptText'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'prompt'); ?>
		<?php echo $form->textArea($model,'prompt',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'prompt'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'prefaceText'); ?>
		<?php echo $form->textArea($model,'prefaceText',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'prefaceText'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'preface'); ?>
		<?php echo $form->textArea($model,'preface',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'preface'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'citationText'); ?>
		<?php echo $form->textArea($model,'citationText',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'citationText'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'citation'); ?>
		<?php echo $form->textArea($model,'citation',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'citation'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'subjectType'); ?>
		<?php echo $form->textField($model,'subjectType'); ?>
		<?php echo $form->error($model,'subjectType'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'answerType'); ?>
		<?php echo $form->textField($model,'answerType'); ?>
		<?php echo $form->error($model,'answerType'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'askingStyleList'); ?>
		<?php echo $form->textField($model,'askingStyleList'); ?>
		<?php echo $form->error($model,'askingStyleList'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ordering'); ?>
		<?php echo $form->textField($model,'ordering'); ?>
		<?php echo $form->error($model,'ordering'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'otherSpecify'); ?>
		<?php echo $form->textField($model,'otherSpecify'); ?>
		<?php echo $form->error($model,'otherSpecify'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'noneButton'); ?>
		<?php echo $form->textField($model,'noneButton'); ?>
		<?php echo $form->error($model,'noneButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'allButton'); ?>
		<?php echo $form->textField($model,'allButton'); ?>
		<?php echo $form->error($model,'allButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'pageLevelDontKnowButton'); ?>
		<?php echo $form->textField($model,'pageLevelDontKnowButton'); ?>
		<?php echo $form->error($model,'pageLevelDontKnowButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'pageLevelRefuseButton'); ?>
		<?php echo $form->textField($model,'pageLevelRefuseButton'); ?>
		<?php echo $form->error($model,'pageLevelRefuseButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'dontKnowButton'); ?>
		<?php echo $form->textField($model,'dontKnowButton'); ?>
		<?php echo $form->error($model,'dontKnowButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'refuseButton'); ?>
		<?php echo $form->textField($model,'refuseButton'); ?>
		<?php echo $form->error($model,'refuseButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'allOptionString'); ?>
		<?php echo $form->textField($model,'allOptionString'); ?>
		<?php echo $form->error($model,'allOptionString'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uselfExpression'); ?>
		<?php echo $form->textField($model,'uselfExpression'); ?>
		<?php echo $form->error($model,'uselfExpression'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'minLimitType'); ?>
		<?php echo $form->textField($model,'minLimitType'); ?>
		<?php echo $form->error($model,'minLimitType'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'minLiteral'); ?>
		<?php echo $form->textField($model,'minLiteral'); ?>
		<?php echo $form->error($model,'minLiteral'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'minPrevQues'); ?>
		<?php echo $form->textField($model,'minPrevQues'); ?>
		<?php echo $form->error($model,'minPrevQues'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'maxLimitType'); ?>
		<?php echo $form->textField($model,'maxLimitType'); ?>
		<?php echo $form->error($model,'maxLimitType'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'maxLiteral'); ?>
		<?php echo $form->textField($model,'maxLiteral'); ?>
		<?php echo $form->error($model,'maxLiteral'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'maxPrevQues'); ?>
		<?php echo $form->textField($model,'maxPrevQues'); ?>
		<?php echo $form->error($model,'maxPrevQues'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'minCheckableBoxes'); ?>
		<?php echo $form->textField($model,'minCheckableBoxes'); ?>
		<?php echo $form->error($model,'minCheckableBoxes'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'maxCheckableBoxes'); ?>
		<?php echo $form->textField($model,'maxCheckableBoxes'); ?>
		<?php echo $form->error($model,'maxCheckableBoxes'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'withListRange'); ?>
		<?php echo $form->textField($model,'withListRange'); ?>
		<?php echo $form->error($model,'withListRange'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'listRangeString'); ?>
		<?php echo $form->textField($model,'listRangeString'); ?>
		<?php echo $form->error($model,'listRangeString'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'minListRange'); ?>
		<?php echo $form->textField($model,'minListRange'); ?>
		<?php echo $form->error($model,'minListRange'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'maxListRange'); ?>
		<?php echo $form->textField($model,'maxListRange'); ?>
		<?php echo $form->error($model,'maxListRange'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'timeUnits'); ?>
		<?php echo $form->textField($model,'timeUnits'); ?>
		<?php echo $form->error($model,'timeUnits'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'symmetric'); ?>
		<?php echo $form->textField($model,'symmetric'); ?>
		<?php echo $form->error($model,'symmetric'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'keepOnSamePage'); ?>
		<?php echo $form->textField($model,'keepOnSamePage'); ?>
		<?php echo $form->error($model,'keepOnSamePage'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'studyId'); ?>
		<?php echo $form->textField($model,'studyId'); ?>
		<?php echo $form->error($model,'studyId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'answerReasonExpressionId'); ?>
		<?php echo $form->textField($model,'answerReasonExpressionId'); ?>
		<?php echo $form->error($model,'answerReasonExpressionId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'networkRelationshipExprId'); ?>
		<?php echo $form->textField($model,'networkRelationshipExprId'); ?>
		<?php echo $form->error($model,'networkRelationshipExprId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'networkNShapeQId'); ?>
		<?php echo $form->textField($model,'networkNShapeQId'); ?>
		<?php echo $form->error($model,'networkNShapeQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'networkNColorQId'); ?>
		<?php echo $form->textField($model,'networkNColorQId'); ?>
		<?php echo $form->error($model,'networkNColorQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'networkNSizeQId'); ?>
		<?php echo $form->textField($model,'networkNSizeQId'); ?>
		<?php echo $form->error($model,'networkNSizeQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'networkEColorQId'); ?>
		<?php echo $form->textField($model,'networkEColorQId'); ?>
		<?php echo $form->error($model,'networkEColorQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'networkESizeQId'); ?>
		<?php echo $form->textField($model,'networkESizeQId'); ?>
		<?php echo $form->error($model,'networkESizeQId'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->