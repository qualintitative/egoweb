<div style="overflow-y:auto; height:320px">
<div style="width:300px; float:left; margin-left:20px">
<?php
#OK FOR SQL INJECTION
Yii::app()->clientScript->registerScript('delete', "
jQuery('a.delete').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		$.get(url,function(data){
			 $('#data-".$questionId."').html(data);
		 });
		return false;
});
");


Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/modal.js');
Yii::app()->clientScript->registerScript('update', "
jQuery('a.update').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		$.get(url,function(data){
			 $('#edit-legend-".$questionId."').html(data);
		 });
		return false;
});
");
Yii::app()->clientScript->registerScript('moveup', "
jQuery('a.moveup').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		$.get(url,function(data){
			 $('#data-".$questionId."').html(data);
		 });
		return false;
});
");

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'option-grid-'.$questionId,
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'label',
		'shape',
		'color',
		'size',
		array
		(
			'class'=>'CButtonColumn',
			'template'=>'{moveup}{update}{delete}',
			'buttons'=>array
			(
				'delete' => array
				(
					'url'=>'Yii::app()->createUrl("/authoring/ajaxdelete", array("Legend[id]"=>$data->id,"questionId"=>$data->questionId, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'delete'),
				),
				'update' => array
				(
					'url'=>'Yii::app()->createUrl("/authoring/ajaxload", array("legendId"=>$data->id, "_"=>"'.uniqid().'", "form"=>"_form_legend_edit"))',
					'options'=>array('class'=>'update'),
				),
				'moveup' => array
				(
					'imageUrl'=>'/images/arrow_up.png',
					'url'=>'Yii::app()->createUrl("/authoring/ajaxmoveup", array("legendId"=>$data->id, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'moveup'),
				),
			),

		),
	),
	'summaryText'=>'',
));

?>
	<?php
		echo CHtml::ajaxButton (CHtml::encode('Add new legend'),
		CController::createUrl('ajaxload', array('form'=>"_form_legend_edit", 'questionId'=>$questionId, 'studyId'=>$studyId)),
		array('update' => '#edit-legend-'.$questionId),
		array('id' => uniqid(), 'live'=>false)
	);
	?>
</div>
<div style="float:left; width:400px; margin:15px 0 0 30px;">

	<div id="edit-legend-<?php echo $questionId; ?>" style="margin-bottom:15px;"></div>

</div>
</div>
<button onclick='loadData("<?= $questionId ?>", "_form_question"); return false'>Back</button>

