<?php
/* @var $this StudyController */
/* @var $model Study */
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
		<?php echo $form->textField($model,'random_key',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'active'); ?>
		<?php echo $form->textField($model,'active'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'introductionText'); ?>
		<?php echo $form->textField($model,'introductionText',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'introduction'); ?>
		<?php echo $form->textField($model,'introduction',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'egoIdPromptText'); ?>
		<?php echo $form->textField($model,'egoIdPromptText',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'egoIdPrompt'); ?>
		<?php echo $form->textField($model,'egoIdPrompt',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'alterPromptText'); ?>
		<?php echo $form->textField($model,'alterPromptText',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'alterPrompt'); ?>
		<?php echo $form->textField($model,'alterPrompt',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'conclusionText'); ?>
		<?php echo $form->textField($model,'conclusionText',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'conclusion'); ?>
		<?php echo $form->textField($model,'conclusion',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'minAlters'); ?>
		<?php echo $form->textField($model,'minAlters',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'maxAlters'); ?>
		<?php echo $form->textField($model,'maxAlters',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'adjacencyExpressionId'); ?>
		<?php echo $form->textField($model,'adjacencyExpressionId',array('size'=>60,'maxlength'=>100)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'valueRefusal'); ?>
		<?php echo $form->textField($model,'valueRefusal'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'valueDontKnow'); ?>
		<?php echo $form->textField($model,'valueDontKnow'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'valueLogicalSkip'); ?>
		<?php echo $form->textField($model,'valueLogicalSkip'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->