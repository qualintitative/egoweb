<?php
/* @var $this StudyController */
/* @var $model Study */

$this->menu=array(
	array('label'=>'Study Settings', 'url'=>array('edit','id'=>$model->id)),
	array('label'=>'Ego ID Questions', 'url'=>array('create')),
);
?>

<h1>View Study #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'random_key',
		'active',
		'name',
		'introductionText',
		'introduction',
		'egoIdPromptText',
		'egoIdPrompt',
		'alterPromptText',
		'alterPrompt',
		'conclusionText',
		'conclusion',
		'minAlters',
		'maxAlters',
		'adjacencyExpressionId',
		'valueRefusal',
		'valueDontKnow',
		'valueLogicalSkip',
		'valueNotYetAnswered',
	),
)); ?>
