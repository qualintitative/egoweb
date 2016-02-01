<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'note-form',
	'enableAjaxValidation'=>true,
));?>

<div class="form-group">
	<label class="control-label">
	<?php
	if(is_numeric($model->alterId))
		echo Alters::getName($model->alterId);
	else
		echo str_replace("graphNote-", "", $model->alterId);
	?>
	</label>

	<?php echo $form->textArea($model,'notes',array('value'=>$model->notes, 'class'=>'form-control', 'placeholder'=>'notes')); ?>
</div>

<?php echo $form->hiddenField($model,'id',array('value'=>$model->id)); ?>
<?php echo $form->hiddenField($model,'interviewId',array('value'=>$model->interviewId)); ?>
<?php echo $form->hiddenField($model,'expressionId',array('value'=>$model->expressionId)); ?>
<?php echo $form->hiddenField($model,'alterId',array('value'=>$model->alterId)); ?>

<button class="btn btn-primary pull-right" onclick="saveNote();return false;">Save Note</button>
<?php if(!$model->isNewRecord): ?>
<button class="btn btn-danger pull-right" onclick="deleteNote();return false;" style="margin-right:10px;">Delete Note</button>
<?php endif; ?>

<?php $this->endWidget(); ?>