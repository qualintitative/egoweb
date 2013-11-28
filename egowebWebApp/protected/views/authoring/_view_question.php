<?php
/* @var $this QuestionController */
/* @var $data Question */
?>
<?php if(!isset($_POST['Question'])): ?>
<div id="<?php echo $data->id; ?>">
<?php endif; ?>

	<h3><?php echo $data->title; ?>
	<?php if($data->answerType == "MULTIPLE_SELECTION"): ?>
	    	<div class="optionLink" style="height:20px; width:60px;float:right">Options</div>
	<?php endif; ?>
	</h3>
	<div id="data-<?php echo $data->id; ?>" style="height:350px; overflow-y:scroll;">
	</div>

	<div style="float:left; width:500px; display:none">
		<a href='javascript:void(0)' onclick='loadData(<?php echo $data->id ?>, "_form_question")'>
		<?php echo CHtml::encode(($data->ordering+1) . ': ' . $data->title) ?></a>
		<br />
		<?php echo CHtml::encode($data->answerType); ?>
		<br /><br />
	<b><?php echo CHtml::encode($data->prompt); ?></b>
	</div>

	<br clear=all>

<?php if(!isset($_POST['Question'])): ?>
</div>
<?php endif; ?>
