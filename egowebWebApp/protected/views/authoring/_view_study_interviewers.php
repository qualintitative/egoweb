<?php
Yii::app()->clientScript->registerScript('delete-prompt', "
jQuery('a.delete-interviewer').click(function() {

		var url = $(this).attr('href');
		document.location.href = url;
		return false;
});
");
?>
<div style="width:100%; float:left">
<h3>Users</h3>

<?php
if(isset($model))
	echo $model->getError('name');
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'study-interview-grid',
	'dataProvider'=>$dataProvider,
	'cssFile'=>false,
		'columns'=>array(
		array(
			'name' =>'interviewerId',
			'value' => 'User::getName($data->interviewerId)',
			'type' => 'raw',
			'htmlOptions' => array('style'=>'width:60px;'),
		),
		array(
			'name'=>'permissions',
			'header'=>'Role',
			'type'=>'raw',
			'value'=>'$data->permission',
		),
		array
		(
			'class'=>'CButtonColumn',
			'template'=>'{delete}',
			'buttons'=>array
			(
				'delete' => array
				(
					'url'=>'Yii::app()->createUrl("/authoring/deleteinterviewer", array("interviewerId"=>$data->interviewerId, "studyId"=>$data->studyId, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'delete-interviewer'),
				),

			),

		),
	),
	'summaryText'=>'',
));
?>
</div>
