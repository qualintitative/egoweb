<?php $this->pageTitle=Yii::app()->name . ' - Forgot Password';
?>


<?php
$flashMessages = Yii::app()->user->getFlashes();
if ($flashMessages) {
    foreach($flashMessages as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
} else {?>


<div class="form halfsize">

<h1>Forgot Password</h1>
<p>Enter your email to retrieve your password</p>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'forgot-form',
	'enableClientValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
	<label>Email</label>
    <?php echo CHtml::textField('email', '', array('size'=>60,'maxlength'=>128)); ?>
	</div>

	<?php echo CHtml::submitButton('Submit'); ?>

	<?php $this->endWidget(); ?>



</div>

	<?php } ?>