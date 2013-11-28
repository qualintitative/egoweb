<?php
/* @var $this QuestionController */
/* @var $model Question */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'random_key'); ?>
		<?php echo $form->textField($model,'random_key'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'active'); ?>
		<?php echo $form->textField($model,'active'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'title'); ?>
		<?php echo $form->textArea($model,'title',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'promptText'); ?>
		<?php echo $form->textArea($model,'promptText',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'prompt'); ?>
		<?php echo $form->textArea($model,'prompt',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'prefaceText'); ?>
		<?php echo $form->textArea($model,'prefaceText',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'preface'); ?>
		<?php echo $form->textArea($model,'preface',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'citationText'); ?>
		<?php echo $form->textArea($model,'citationText',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'citation'); ?>
		<?php echo $form->textArea($model,'citation',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'subjectType'); ?>
		<?php echo $form->textField($model,'subjectType'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'answerType'); ?>
		<?php echo $form->textField($model,'answerType'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'askingStyleList'); ?>
		<?php echo $form->textField($model,'askingStyleList'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'ordering'); ?>
		<?php echo $form->textField($model,'ordering'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'otherSpecify'); ?>
		<?php echo $form->textField($model,'otherSpecify'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'noneButton'); ?>
		<?php echo $form->textField($model,'noneButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'allButton'); ?>
		<?php echo $form->textField($model,'allButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'pageLevelDontKnowButton'); ?>
		<?php echo $form->textField($model,'pageLevelDontKnowButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'pageLevelRefuseButton'); ?>
		<?php echo $form->textField($model,'pageLevelRefuseButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'dontKnowButton'); ?>
		<?php echo $form->textField($model,'dontKnowButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'refuseButton'); ?>
		<?php echo $form->textField($model,'refuseButton'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'allOptionString'); ?>
		<?php echo $form->textField($model,'allOptionString'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'uselfExpression'); ?>
		<?php echo $form->textField($model,'uselfExpression'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'minLimitType'); ?>
		<?php echo $form->textField($model,'minLimitType'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'minLiteral'); ?>
		<?php echo $form->textField($model,'minLiteral'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'minPrevQues'); ?>
		<?php echo $form->textField($model,'minPrevQues'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'maxLimitType'); ?>
		<?php echo $form->textField($model,'maxLimitType'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'maxLiteral'); ?>
		<?php echo $form->textField($model,'maxLiteral'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'maxPrevQues'); ?>
		<?php echo $form->textField($model,'maxPrevQues'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'minCheckableBoxes'); ?>
		<?php echo $form->textField($model,'minCheckableBoxes'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'maxCheckableBoxes'); ?>
		<?php echo $form->textField($model,'maxCheckableBoxes'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'withListRange'); ?>
		<?php echo $form->textField($model,'withListRange'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'listRangeString'); ?>
		<?php echo $form->textField($model,'listRangeString'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'minListRange'); ?>
		<?php echo $form->textField($model,'minListRange'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'maxListRange'); ?>
		<?php echo $form->textField($model,'maxListRange'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'timeUnits'); ?>
		<?php echo $form->textField($model,'timeUnits'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'symmetric'); ?>
		<?php echo $form->textField($model,'symmetric'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'keepOnSamePage'); ?>
		<?php echo $form->textField($model,'keepOnSamePage'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'studyId'); ?>
		<?php echo $form->textField($model,'studyId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'answerReasonExpressionId'); ?>
		<?php echo $form->textField($model,'answerReasonExpressionId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'networkRelationshipExprId'); ?>
		<?php echo $form->textField($model,'networkRelationshipExprId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'networkNShapeQId'); ?>
		<?php echo $form->textField($model,'networkNShapeQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'networkNColorQId'); ?>
		<?php echo $form->textField($model,'networkNColorQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'networkNSizeQId'); ?>
		<?php echo $form->textField($model,'networkNSizeQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'networkEColorQId'); ?>
		<?php echo $form->textField($model,'networkEColorQId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'networkESizeQId'); ?>
		<?php echo $form->textField($model,'networkESizeQId'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->