<?php
/* @var $this QuestionController */
/* @var $model Question */

$this->breadcrumbs=array(
	'Questions'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List Question', 'url'=>array('index')),
	array('label'=>'Create Question', 'url'=>array('create')),
	array('label'=>'Update Question', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Question', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Question', 'url'=>array('admin')),
);
?>

<h1>View Question #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'random_key',
		'active',
		'title',
		'promptText',
		'prompt',
		'prefaceText',
		'preface',
		'citationText',
		'citation',
		'subjectType',
		'answerType',
		'askingStyleList',
		'ordering',
		'otherSpecify',
		'noneButton',
		'allButton',
		'pageLevelDontKnowButton',
		'pageLevelRefuseButton',
		'dontKnowButton',
		'refuseButton',
		'allOptionString',
		'uselfExpression',
		'minLimitType',
		'minLiteral',
		'minPrevQues',
		'maxLimitType',
		'maxLiteral',
		'maxPrevQues',
		'minCheckableBoxes',
		'maxCheckableBoxes',
		'withListRange',
		'listRangeString',
		'minListRange',
		'maxListRange',
		'timeUnits',
		'symmetric',
		'keepOnSamePage',
		'studyId',
		'answerReasonExpressionId',
		'networkRelationshipExprId',
		'networkNShapeQId',
		'networkNColorQId',
		'networkNSizeQId',
		'networkEColorQId',
		'networkESizeQId',
	),
)); ?>
