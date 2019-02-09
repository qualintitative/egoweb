<?php
/* @var $this DataController */
$this->pageTitle = "Alter Matching";
?>
<script>
function getInterviews(dropdown, container){
	$.get('/dyad/ajaxinterviews/' + dropdown.val(), function(data){
    $("#sendError").hide();
    $("#sendNotice").hide();
    $(container).html(data);
  });
}
</script>
<div class="panel panel-default">
<div class="panel-heading">Single Study Match</div>
<div class="panel-body">
<?php foreach($studies as $data): ?>
	<?php echo CHtml::link(
		CHtml::encode(Study::getName($data->id)),
		Yii::app()->createUrl('dyad/study/'.$data->id)
		); ?><br>
<?php endforeach; ?>
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading">Match Different Studies</div>
<div class="panel-body">
<?php
    echo CHtml::form('dyad/matching', 'post', array('id'=>'analysis'));
    echo CHtml::hiddenField('studyId', $study->id);
    echo CHtml::hiddenField('expressionId', "");
?>
<?php
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist(
	'studyId',
	'',
<<<<<<< HEAD
	CHtml::listData(Study::model()->findAll($criteria),'id', 'name'),
=======
	CHtml::listData($studies,'id', 'name'),
>>>>>>> dev
	                array(
                        'empty' => 'Select',
                        'onchange'=>"js:getInterviews(\$(this), '#interview-1')",
                        'class'=>'form-control'
                    )

);
?>
<div id="interview-1"></div>
<?php
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist(
	'studyId',
	'',
<<<<<<< HEAD
	CHtml::listData(Study::model()->findAll($criteria),'id', 'name'),
=======
	CHtml::listData($studies,'id', 'name'),
>>>>>>> dev
	                array(
                        'empty' => 'Select',
                        'onchange'=>"js:getInterviews(\$(this), '#interview-2')",
                        'class'=>'form-control'
                    )

);
?>
<div id="interview-2"></div>
<button class='authorButton'>Dyad Match</button><br style='clear:both'>
</form>
</div>
</div>
