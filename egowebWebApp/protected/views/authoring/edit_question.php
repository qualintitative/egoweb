<?php
/* @var $this QuestionController */
/* @var $model Question */
?>

<h1>Edit <?php echo $model->answerType; ?> Question</h1>

<?php echo $this->renderPartial('_form_question', array('model'=>$model, 'ajax'=>$ajax)); ?>
