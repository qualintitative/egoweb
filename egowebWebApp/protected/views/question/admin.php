<?php
/* @var $this QuestionController */
/* @var $model Question */

$this->breadcrumbs=array(
	'Questions'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Question', 'url'=>array('index')),
	array('label'=>'Create Question', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#question-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Questions</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'question-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'random_key',
		'active',
		'title',
		'promptText',
		'prompt',
		/*
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
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
