<script>
<?php $study = Study::model()->findByPk($studyId); ?>
<?php  if(Interview::countAlters($interviewId) >= $study->maxAlters): ?>
jQuery(document).ready(function(){
	jQuery('#alter-form').hide();
});
<?php  else: ?>
jQuery(document).ready(function(){
	jQuery('#alter-form').show();
	jQuery('#Alters_name').focus();

});
<?php endif; ?>

<?php if($alterPrompt): ?>
jQuery(document).ready(function(){
	jQuery('#alterPrompt').html('<?php echo $alterPrompt; ?>');
});
<?php endif; ?>

jQuery(document).ready(function(){
	var objDiv = document.getElementById("alterListBox");
	objDiv.scrollTop = objDiv.scrollHeight;
});
</script>
<?php

Yii::app()->clientScript->registerScript('alter-delete', "
jQuery('a.alter-delete').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		$.get(url,function(data){
			 $('#alterListBox').html(data);
		 });
		return false;
});
");

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'alters-grid',
	'dataProvider'=>$dataProvider,
	'hideHeader'=>'true',
	'columns'=>array(
		array(
			'type'=>'raw',
			'name'=>'number',
			'value'=>'$data->isRepeat',
			'htmlOptions'=>array('style'=>"width:20px;",'id'=>'ordering'),
		),
		array(
			'type'=>'raw',
			'name'=>'name',
			'value'=>'$data->name',
			'htmlOptions'=>array('style'=>"width:120px;"),
		),
		array
		(
			'class'=>'CButtonColumn',
			'template'=>'{delete}',
			'buttons'=>array
			(
				'delete' => array
				(
					'url'=>'Yii::app()->createUrl("/interviewing/ajaxdelete", array("interviewId"=>'.$interviewId.', "studyId"=>'.$studyId.', "Alters[id]"=>$data->id, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'alter-delete'),
				),
			),

		),
	),
	'summaryText'=>'',
));
?>

<div>
<?php
echo $model->getError('name');
?>
</div>