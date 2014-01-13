<?php
Yii::app()->clientScript->registerScript('delete', "
jQuery('a.delete').click(function() {

        var url = $(this).attr('href');
        //  do your post request here


        $.get(url,function(data){
             $('#alterList').html(data);
         });
        return false;
});
");
Yii::app()->clientScript->registerScript('update', "
jQuery('a.update').click(function() {

        var url = $(this).attr('href');
        //  do your post request here


        $.get(url,function(data){
             $('#edit-alterList').html(data);
         });
        return false;
});
");

Yii::app()->clientScript->registerScript('moveup-2', "
jQuery('a.moveup').click(function() {
        var url = $(this).attr('href');
        //  do your post request here


        $.get(url,function(data){
             $('#showLink').html(data);
         });
        return false;
});
");
Yii::app()->clientScript->registerScript('moveup-Alters', "
function clickHandler(evt) {

        var url = $(this).attr('href');
        //  do your post request here


        $.get(url,function(data){
             $('#showLink').html(data);
         });
        return false;
}
");
?>
<div style="width:100%; float:left">
<label>Pre-defined Alter List</label>

<?php
$afterAjaxUpdate = 'function(id, data) { $("a.moveup").bind("click", clickHandler); }';
if(isset($model))
    echo $model->getError('name');
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'alters-grid',
	'dataProvider'=>$dataProvider,
	'cssFile'=>false,
	'columns'=>array(
        'id',
		'name',
		'email',
		array(
			'name'=>'interviwerId',
			'header'=>'Interviewer',
			'type'=>'raw',
			'value'=>'User::getName($data->interviewerId)',
		),
		array
		(
    		'class'=>'CButtonColumn',
    		'template'=>'{moveup}{update}{delete}',
    		'buttons'=>array
    		(
        		'delete' => array
        		(
            		'url'=>'Yii::app()->createUrl("/authoring/ajaxdelete", array("AlterList[id]"=>$data->id, "_"=>"'.uniqid().'"))',
            		'options'=>array('class'=>'delete'),
        		),
        		'update' => array
        		(
            		'url'=>'Yii::app()->createUrl("/authoring/ajaxload", array("alterListId"=>$data->id, "form"=>"_form_alter_list_edit", "_"=>"'.uniqid().'"))',
            		'options'=>array('class'=>'update'),
        		),
        		'moveup' => array
        		(
        			'imageUrl'=>'/images/link.png',
            		'url'=>'Yii::app()->createUrl("/authoring/ajaxshowlink", array("alterListId"=>$data->id, "_"=>"'.uniqid().'"))',
            		'options'=>array('class'=>'moveup'),
        		),
    		),

		),
	),
	'summaryText'=>'',
    'afterAjaxUpdate'=>$afterAjaxUpdate,
));
?>
</div>

