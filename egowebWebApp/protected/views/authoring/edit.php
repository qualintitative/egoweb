<?php
/* @var $this StudyController */
/* @var $model Study */

$this->menu=array(
	array('label'=>'List Study', 'url'=>array('index')),
	array('label'=>'Create Study', 'url'=>array('create')),
	array('label'=>'View Study', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Study', 'url'=>array('admin')),
);
?>

<h1>Study Settings</h1>

<?php echo $this->renderPartial('_form_study_settings', array('model'=>$model)); ?>