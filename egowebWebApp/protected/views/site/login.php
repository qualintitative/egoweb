<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle = 'Login';

?>

<h1>Login</h1>

<p>Please fill out the following form with your login credentials:</p>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'login-form',
        'enableClientValidation'=>false,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
        'htmlOptions' => array(
            'class'=>'form-horizontal',
        ),
    )); ?>

	<div class="form-group">
		<?php echo $form->label($model,'username', array("class" => "col-md-1 control-label")); ?>
		<div class="col-md-3"><?php echo $form->textField($model,'username', array("class"=>"form-control")); ?></div>
		<div class="col-md-8"><?php echo $form->error($model,'username', array("class"=>"help-block")); ?></div>
	</div>

	<div class="form-group">
		<?php echo $form->label($model,'password', array("class"=>"col-md-1 control-label")); ?>
		<div class="col-md-3"><?php echo $form->passwordField($model,'password', array("class"=>"form-control")); ?></div>
		<div class="col-md-8"><?php echo $form->error($model,'password'); ?></div>
	</div>

        <?php if($model->scenario == 'captchaRequired'): ?>
            <div class="form-group">
                <?php echo CHtml::activeLabel($model,'verifyCode', array("class" => "col-md-1 control-label")); ?><?php $this->widget('CCaptcha'); ?>
                <div class="col-md-3"><?php echo CHtml::activeTextField($model,'verifyCode', array("class"=>"form-control")); ?></div>
                <div class="col-md-offset-4 col-md-8">
                    Please enter the letters as they are shown in the image above.<br/>
                Letters are not case-sensitive.
                </div>
            </div>
        <?php endif; ?>

	<div class="form-group rememberMe">
		<div class="col-md-offset-1 col-md-3">
    		<?php echo $form->checkBox($model,'rememberMe'); ?>
            <?php echo $form->label($model,'rememberMe'); ?>
		</div>
		<div class="col-md-8"><?php echo $form->error($model,'rememberMe'); ?></div>
	</div>

	<div class="form-group">
    	<div class="col-md-offset-1 col-md-11">
		    <?php echo CHtml::submitButton('Login', array("class"=>"btn btn-primary")); ?>
    	</div>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
