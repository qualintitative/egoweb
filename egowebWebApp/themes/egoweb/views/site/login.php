<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);
?>

<div class="container">
	<h1 class="mbl">Login</h1>
	<p>Please fill out the following form with your login credentials:</p>
	<div class="form">
		<?php $form=$this->beginWidget('CActiveForm', array(
			'id'=>'login-form',
			'enableClientValidation'=>true,
			'clientOptions'=>array(
				'validateOnSubmit'=>true,
			),
			'htmlOptions'=>array('class'=>'form-group')
		)); ?>
		<div class="row">
			<div class="col-sm-4">
			
				<div class="form-group">
					<?php echo $form->labelEx($model,'username',array('class'=>'control-label')); ?>
					<?php echo $form->textField($model,'username',array('size'=>60,'maxlength'=>100,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'username'); ?>
				</div>
			
				<div class="form-group">
					<?php echo $form->labelEx($model,'password',array('class'=>'control-label')); ?>
					<?php echo $form->passwordField($model,'password',array('size'=>60,'maxlength'=>100,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'password'); ?>
				</div>
				<!--
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input type="checkbox" value="">
							Option one is this and that&mdash;be sure to include why it's great
						</label>
					</div>
				</div>
				//-->
				<div class="row rememberMe">
					<?php echo $form->checkBox($model,'rememberMe'); ?>
					<?php echo $form->label($model,'rememberMe'); ?>
					<?php echo $form->error($model,'rememberMe'); ?>
				</div>
				<?php echo CHtml::submitButton('Login', array('class'=>'btn btn-primary btn-lg')); ?>
			</div>
		</div>
		
		<?php $this->endWidget(); ?>
	</div><!-- form -->
</div>
