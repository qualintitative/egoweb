<?php
/* @var $this InterviewingController */
/* @var $model Answer */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'answer-form',
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
		<?php echo $form->labelEx($model,'questionId'); ?>
		<?php echo $form->textField($model,'questionId'); ?>
		<?php echo $form->error($model,'questionId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'interviewId'); ?>
		<?php echo $form->textField($model,'interviewId'); ?>
		<?php echo $form->error($model,'interviewId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'alterId1'); ?>
		<?php echo $form->textField($model,'alterId1'); ?>
		<?php echo $form->error($model,'alterId1'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'alterId2'); ?>
		<?php echo $form->textField($model,'alterId2'); ?>
		<?php echo $form->error($model,'alterId2'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'valueText'); ?>
		<?php echo $form->textArea($model,'valueText',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'valueText'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'value'); ?>
		<?php echo $form->textArea($model,'value',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'value'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'otherSpecifyText'); ?>
		<?php echo $form->textArea($model,'otherSpecifyText',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'otherSpecifyText'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'skipReason'); ?>
		<?php echo $form->textField($model,'skipReason'); ?>
		<?php echo $form->error($model,'skipReason'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'studyId'); ?>
		<?php echo $form->textField($model,'studyId'); ?>
		<?php echo $form->error($model,'studyId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'questionType'); ?>
		<?php echo $form->textField($model,'questionType'); ?>
		<?php echo $form->error($model,'questionType'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'answerType'); ?>
		<?php echo $form->textField($model,'answerType'); ?>
		<?php echo $form->error($model,'answerType'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->