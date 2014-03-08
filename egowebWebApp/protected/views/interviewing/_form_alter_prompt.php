<br style="clear:left">

<?php
$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
	'model' => $model,
	'name' => 'Alters[name]',
	'value' => '',
	'source'=>'js: function(request, response) {
		$.ajax({
			url: "'.$this->createUrl('/interviewing/autocomplete').'",
			dataType: "json",
			data: {
				term: request.term,
				field: "name",
				studyId: "'.$question->studyId.'",
				self: "'.Interview::getRespondant($interviewId).'",
				interviewId:'.$interviewId.'
			},
			success: function (data) {
				response(data);
			}
		})
	}',
		'options' => array(
			'minLength' => 1,
			'select' => "js:function(event, ui) {
			$('#Alter_name').val(ui.item.name);
			}",
		),
'htmlOptions'=> array(
	"style"=>"float:left;"
)

));
echo $form->hiddenField($model, 'interviewId',array('value'=>$interviewId));

?>