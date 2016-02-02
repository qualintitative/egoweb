<?php
/* @var $this InterviewingController */
/* @var $dataProvider CActiveDataProvider */
$this->pageTitle = "Interviewing";

?>

<div class="view" style="width:360px;float:left;margin-right:30px">
<?php if(isset($_GET['studyId'])): ?>
<script>
$(function(){
	$.get("/interviewing/study/<?php echo $_GET['studyId']; ?>", function(data){
		$("#interviewList").html(data);
	});
});
</script>
<?php endif; ?>

	<h2>Single Session Studies</h2>
	<?php foreach($single as $data): ?>
	<?php echo CHtml::ajaxLink(CHtml::encode($data->name), Yii::app()->createUrl('interviewing/study/'.$data->id),
		array('update'=>'#interviewList')
		)."<br>"; ?>
	<?php endforeach; ?>

	<h2>Multi Session Studies</h2>
	<?php foreach($multi as $data): ?>
	<?php echo CHtml::ajaxLink(CHtml::encode($data->name),Yii::app()->createUrl('interviewing/study/'.$data->id),
		array('update'=>'#interviewList')
		)."<br>"; ?>
	<?php endforeach; ?>

</div>

<div id="interviewList">
</div>