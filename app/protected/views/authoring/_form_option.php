<div style="overflow-y:auto; height:320px">
<div class="col-sm-6">
<script>
function ajaxCheck(optionId, checked){
    $.post("/authoring/ajaxupdate",{optionId:optionId, otherSpecify:(checked == true ? 1:0),YII_CSRF_TOKEN:$("input[name='YII_CSRF_TOKEN']").val()}, function(data){
        $('#data-<?php echo $questionId ?>').html(data);
    });
}
</script>
<?php
#OK FOR SQL INJECTION
$studyId = q("SELECT studyId FROM question WHERE id = " . $questionId)->queryScalar();
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
			 $('#edit-option-".$questionId."').html(data);
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
            array(
				'name'=>'name',
				'value'=>'"<label>$data->name</label>" . "<div id=\"OPTION_" . $data->id  . "\" class=\"audioPlay\">" . (file_exists(Yii::app()->basePath."/../audio/".$data->studyId . "/OPTION/" . $data->id . ".mp3") ? ' .
				'"<a class=\"playSound\" onclick=\"playSound($(this).attr(\'file\'))\" href=\"#\" file=\"/audio/$data->studyId/OPTION/$data->id.mp3\"><span class=\"fui-volume play-sound\"></span></a></div>" : "")',
				'type'=>'raw',
				'htmlOptions'=>array(
					'style'=>'width:60%',
				),
            ),
		'value',
            array(
				'name'=>'Specify',
				'value'=>'CHtml::checkBox("otherSpecify", $data->otherSpecify, array("onchange"=>"ajaxCheck($data->id, $(this).prop(\'checked\'))"));',
				'type'=>'raw',
				'htmlOptions'=>array(
                ),
            ),
		array
		(
			'class'=>'CButtonColumn',
			'template'=>'{moveup}{update}{delete}{upload}',
			'buttons'=>array
			(
				'delete' => array
				(
					'url'=>'Yii::app()->createUrl("/authoring/ajaxdelete", array("QuestionOption[id]"=>$data->id, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'delete'),
				),
				'update' => array
				(
					'url'=>'Yii::app()->createUrl("/authoring/ajaxload", array("optionId"=>$data->id, "_"=>"'.uniqid().'", "form"=>"_form_option_edit"))',
					'options'=>array('class'=>'update'),
				),
				'moveup' => array
				(
					'imageUrl'=>'/images/arrow_up.png',
					'url'=>'Yii::app()->createUrl("/authoring/ajaxmoveup", array("optionId"=>$data->id, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'moveup'),
				),
				'upload' => array
				(
					'label'=>'<span class="fui-gear"></span>Audio',
					'url'=>'Yii::app()->createUrl("/authoring/uploadaudio", array("id"=>$data->id, "studyId"=>$data->studyId, "type"=>"OPTION", "_"=>"'.uniqid().'"))',
					'options'=>array(
						'data-toggle'=>"modal",
						'data-target'=>"#myModal",
					),
				),
			),

		),
	),
	'summaryText'=>'',
));

?>
<a class='delete' href="<?php echo Yii::app()->createUrl("/authoring/ajaxdelete", array("QuestionOption[id]"=>"all", "questionId"=>$questionId)); ?>">Delete all</a>
</div>
<div class="col-sm-6">
	<div style="margin-bottom:15px;">
		<span class="smallheader">Add new option</span>
		<?php
			$model = new QuestionOption;

			$form=$this->beginWidget('CActiveForm', array(
				'id'=>'add-option-form',
				'enableAjaxValidation'=>true,
			));

			echo $form->hiddenField($model,'id',array('value'=>$model->id));
			echo $form->hiddenField($model,'questionId',array('value'=>$questionId));
			echo $form->hiddenField($model,'studyId',array('value'=>$studyId));

			echo $form->labelEx($model,'name');
			echo $form->textField($model,'name', array('style'=>'width:100px'));
			echo $form->error($model,'name');
			echo $form->labelEx($model,'value');
			echo $form->textField($model,'value', array('style'=>'width:100px'));
			echo $form->error($model,'value');

			echo CHtml::ajaxSubmitButton ("Add Option",
				CController::createUrl('ajaxupdate'),
				array('update' => '#data-'.$questionId),
				array('id'=>uniqid(), 'live'=>false)
			);

			$this->endWidget();
		?>
	</div>

	<div id="edit-option-<?php echo $questionId; ?>" style="margin-bottom:15px;"></div>
	<div>

		<span class="smallheader">Replace options</span>
		<table>
			<tr>
				<td>

				<?php
					// Replace options with options from option list
					$model = Question::model()->findByPk($questionId);
					$form = $this->beginWidget('CActiveForm', array(
						'id'=>'replace-option-preset-form',
						'enableAjaxValidation'=>true,
					));

					echo CHtml::dropdownlist('answerListId', '', CHtml::listData(AnswerList::model()->findAllByAttributes(array('studyId'=>$model->studyId)), 'id', 'listName'));
					echo CHtml::hiddenField('QuestionOption[id]', 'replacePreset');
					echo CHtml::hiddenField('questionId', $questionId);
					echo CHtml::ajaxSubmitButton ("Replace with options from preset",

					CController::createUrl('ajaxupdate'),
						array('update' => '#data-'.$model->id),
						array('id'=>uniqid(), 'live'=>false)
					);

					$this->endWidget();
				?>

				</td>
			</tr>
			<tr>
				<td>
				<?php
					// replace options with options from another question
					$form=$this->beginWidget('CActiveForm', array(
						'id'=>'replace-option-other-form',
						'enableAjaxValidation'=>true,
					));

					$criteria=new CDbCriteria;
					$criteria=array(
						'condition'=>"studyId = " . $model->studyId . " AND id != " .$questionId. " AND (answerType = 'SELECTION' OR answerType = 'MULTIPLE_SELECTION')",
						'order'=>'ordering',
					);

					echo CHtml::dropdownlist('otherQuestionId', '', CHtml::listData(Question::model()->findAll($criteria), 'id', 'title'));
					echo CHtml::hiddenField('QuestionOption[id]', 'replaceOther');
					echo CHtml::hiddenField('questionId', $questionId);
					echo CHtml::ajaxSubmitButton ("Replace with options from other question",

					CController::createUrl('ajaxupdate'),
						array('update' => '#data-'.$model->id),
						array('id'=>uniqid(), 'live'=>false)
					);

					$this->endWidget();
				?>
				</td>
			</tr>
		</table>
	</div>
</div>
</div>
