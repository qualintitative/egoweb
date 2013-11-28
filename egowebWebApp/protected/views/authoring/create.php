<?php
/* @var $this StudyController */
/* @var $model Study */

$this->breadcrumbs=array(
	'Studies'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Study', 'url'=>array('index')),
	array('label'=>'Manage Study', 'url'=>array('admin')),
);
?>

<h1>Create Study</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>