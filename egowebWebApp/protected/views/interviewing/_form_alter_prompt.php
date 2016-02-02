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
			array('id'=>uniqid(), 'live'=>false, 'class'=>"orangebutton alterSubmit"));

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
			$criteria = array(
				'condition'=>"interviewId = $interviewId AND questionId = $study->multiSessionEgoId",
			);
        $egoValue = Answer::model()->find($criteria)->value;
        $multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
        #OK FOR SQL INJECTION

        $oldAnswers = Answer::model()->findAllByAttributes(array("questionId"=>$multiIds));
        foreach($oldAnswers as $oldA){
            if($oldA->value == $egoValue)
                $interviewIds[] = $oldA->interviewId;
        }

		$interviewIds = array_diff($interviewIds, array($interviewId));
		$alters = array();
		foreach($interviewIds as $i_id){
			$criteria = array(
				'condition'=>"FIND_IN_SET($i_id, interviewId) AND NOT FIND_IN_SET($interviewId, interviewId)",
			);
			$aList =  Alters::model()->findAll($criteria);
			foreach($aList as $a){
				$alters[$a->id] = $a->name;
			}
		}

		if($alters){
			echo "<b>Previous Alters</b><br><br>";
			foreach($alters as $oldAlter){
				echo $oldAlter. "<br>";
			}
		}
		?>
		</div>
		<?php endif; ?>
