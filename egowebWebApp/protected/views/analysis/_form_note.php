<div class="col-sm-9 pull-left row">
<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'note-form',
	'enableAjaxValidation'=>true,
));?>
<?php echo $form->hiddenField($model,'id',array('value'=>$model->id)); ?>
<div class="form-group">
<label class="control-label"><?= Alters::getName($model->alterId); ?></label>

<?php echo $form->textArea($model,'notes',array('value'=>$model->notes, 'class'=>'form-control', 'placeholder'=>'notes')); ?>

</div>
<?php echo $form->hiddenField($model,'interviewId',array('value'=>$model->interviewId)); ?>
<?php echo $form->hiddenField($model,'expressionId',array('value'=>$model->expressionId)); ?>
<?php echo $form->hiddenField($model,'alterId',array('value'=>$model->alterId)); ?>

<button class="btn btn-primary pull-right" onclick="saveNote();return false;">Save Note</button>
<?php $this->endWidget(); ?>
</div>