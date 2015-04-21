<?php
/* @var $this StudyController */
/* @var $model Study */
$this->pageTitle = $model->name;
?>

<h1>Study Settings</h1>

<?php echo $this->renderPartial('_form_study_settings', array('model'=>$model)); ?>