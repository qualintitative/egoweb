<br style="clear:left">
<?php
		$form=$this->beginWidget('CActiveForm', array(
			'id'=>'alter-form',
			'enableAjaxValidation'=>true,
		));

if($study->useAsAlters){
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
}else{
	echo $form->textField($model, 'name', array("style"=>"float:left"));
}
echo $form->hiddenField($model, 'interviewId',array('value'=>$interviewId));

?>
		<?php
		echo CHtml::ajaxSubmitButton ("+ Add",
			CController::createUrl('ajaxupdate'),
			array('success'=>'js:function(data){$("#alterListBox").html(data);$("#Alters_name").val("");$(".flash-error").hide()}'),
			array('id'=>uniqid(), 'live'=>false, 'class'=>"orangebutton"));

		$this->endWidget();
		?>

		<?php if(isset($model[0])): ?>
			<div class="flash-error" style="width:200px;">
				<?php echo $model[0]->getError('value'); ?>
			</div>
		<?php endif; ?>
		<?php if($study->multiSessionEgoId): ?>
		<div id="previous_alters">
		<?php
        #OK FOR SQL INJECTION
		$egoValue = q("SELECT value FROM answer WHERE interviewId = " . $interviewId . " AND questionId = " . $study->multiSessionEgoId)->queryScalar();
        #OK FOR SQL INJECTION
        $multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
        #OK FOR SQL INJECTION
        $interviewIds = q("SELECT interviewId FROM answer WHERE questionId in (" . implode(",", $multiIds) . ") AND value = '" .$egoValue . "'" )->queryColumn();
		$interviewIds = array_diff($interviewIds, array($interviewId));
		$alters = [];
		foreach($interviewIds as $i_id){
			$aList =  q("SELECT * FROM alters WHERE FIND_IN_SET($i_id, interviewId) AND NOT FIND_IN_SET($interviewId, interviewId)")->queryAll();
			foreach($aList as $a){
				$alters[$a['id']] = $a;
			}
		}

		array_unique($alters);
			if($alters){
				echo "<b>Previous Alters</b><br><br>";
				foreach($alters as $oldAlter){
					echo $oldAlter['name'] . "<br>";
				}
			}
		?>
		</div>
		<?php endif; ?>
