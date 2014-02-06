<?php
/* @var $this AdminController */
unset(Yii::app()->session['qList']);
unset(Yii::app()->session['pageList']);
?>
<div class="container">
	<div class="row">
		<div class="col-sm-6">
				<div class="h6"><a href="<?php echo  Yii::app()->createUrl('authoring'); ?>">Authoring</a></div>
				<p>Create a new interview, add or change questions for an existing interview.</p>
		</div>
		<div class="col-sm-6">
				<div class="h6"><a href="<?php echo  Yii::app()->createUrl('interviewing'); ?>">Interviewing</a></div>
				<p>Start a new interview or continue a partially completed interview.</p>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
				<div class="h6"><a href="<?php echo  Yii::app()->createUrl('analysis'); ?>">Analysis</a></div>
				<p>Analyze the data from completed interviews.</p>
		</div>
		<div class="col-sm-6">
				<div class="h6"><a href="<?php echo  Yii::app()->createUrl('importExport'); ?>">Import &amp; Export</a></div>
				<p>Save study and respondent data as files for archiving or transferring between computers.</p>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
				<div class="h6"><a href="<?php echo  Yii::app()->createUrl('mobile'); ?>">Mobile</a></div>
				<p>Egoweb Mobile development</p>
		</div>
		<div class="col-sm-6">
				<div class="h6"><a href="<?php echo  Yii::app()->createUrl('logout'); ?>">Logout</a></div>
				<p>Logout of Admin Mode</p>
		</div>
	</div>
</div>