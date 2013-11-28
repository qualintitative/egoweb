<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'add-option-form',
	'enableAjaxValidation'=>true,
)); ?>

<span class="smallheader">Edit an option</span>
<?php echo $form->hiddenField($model,'id',array('value'=>$model->id)); ?>
<?php echo $form->hiddenField($model,'questionId',array('value'=>$questionId)); ?>
<?php echo $form->hiddenField($model,'studyId',array('value'=>$model->studyId)); ?>

<?php echo $form->labelEx($model,'name'); ?>
<?php echo $form->textField($model,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'name'); ?>
<?php echo $form->labelEx($model,'value'); ?>
<?php echo $form->textField($model,'value', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'value'); ?>

<?php if($ajax == true): ?>
    <?php echo CHtml::ajaxSubmitButton ("Save Option",
    	CController::createUrl('ajaxupdate'), 
    	array('update' => '#data-'.$questionId),
    	array('id'=>uniqid(), 'live'=>false));
    ?>
<?php else: ?>
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
<?php endif; ?>
<?php $this->endWidget(); ?>