<?php
/* @var $this InterviewingController */
/* @var $model Answer */
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
		<?php echo $form->label($model,'questionId'); ?>
		<?php echo $form->textField($model,'questionId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'interviewId'); ?>
		<?php echo $form->textField($model,'interviewId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'alterId1'); ?>
		<?php echo $form->textField($model,'alterId1'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'alterId2'); ?>
		<?php echo $form->textField($model,'alterId2'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'valueText'); ?>
		<?php echo $form->textArea($model,'valueText',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'value'); ?>
		<?php echo $form->textArea($model,'value',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'otherSpecifyText'); ?>
		<?php echo $form->textArea($model,'otherSpecifyText',array('rows'=>6, 'cols'=>50)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'skipReason'); ?>
		<?php echo $form->textField($model,'skipReason'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'studyId'); ?>
		<?php echo $form->textField($model,'studyId'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'questionType'); ?>
		<?php echo $form->textField($model,'questionType'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'answerType'); ?>
		<?php echo $form->textField($model,'answerType'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->