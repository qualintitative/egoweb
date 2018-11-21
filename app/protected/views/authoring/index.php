<?php
/* @var $this StudyController */
/* @var $dataProvider CActiveDataProvider */
$this->pageTitle = "Authoring";
?>

<div class="col-sm-12">
<div class="panel panel-default">
	<div class="panel-heading">Create New Study</div>
  <div class="panel-body">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'study-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="form-inline">
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100, 'class'=>"form-control", "placeholder"=>"Name")); ?>
		<?php echo $form->error($model,'name'); ?>
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>
</div>
</div>
</div>

<div class="col-sm-6">
<div class="panel panel-default">
	<div class="panel-heading">Single Session Studies</div>
  <div class="panel-body">
	<?php foreach($single as $data): ?>
	<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
	<?php endforeach; ?>
  </div>
</div>
</div>

<div class="col-sm-6">
<div class="panel panel-default">
	<div class="panel-heading">Multi Session Studies</div>
  <div class="panel-body">
	<?php foreach($multi as $data): ?>
	<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
	<?php endforeach; ?>
  </div>
</div>
</div>
