<?php

/* @var $this AdminController */
$this->pageTitle =  "Admin";

?>
<?php if($alert): ?>
<div class="alert alert-success">
  <strong>System Update</strong><br>
  <?php echo $alert; ?>
</div>
<?php endif; ?>
<?php if(Yii::app()->user->user->permissions >= 3): ?>
<div class="panel panel-default col-sm-6">
  <div class="panel-body">
		<h3><?=CHtml::link('Interviewing', $this->createUrl("/interview"))?></h3>
		<p>
			Start a new interview or continue a partially completed interview.
		</p>
	</div>
</div>
<?php endif; ?>

	<?php if(Yii::app()->user->isAdmin): ?>

    <div class="panel panel-default col-sm-6">
      <div class="panel-body">

		<h3><?=CHtml::link('Authoring', $this->createUrl("/authoring"))?></h3>
		<p>
			Create a new study, add or change questions for an existing study.
		</p>
  </div>
</div>
<div class="panel panel-default col-sm-6">
  <div class="panel-body">

		<h3><?=CHtml::link('Data Processing', $this->createUrl("/data"))?></h3>
		<p>
			Analyze the data from completed interviews.<br><br>
		</p>
  </div>
</div>
<div class="panel panel-default col-sm-6">
  <div class="panel-body">
		<h3><?=CHtml::link('Import &amp; Export Studies', $this->createUrl("/importExport"))?></h3>
		<p>
			Save study and respondent data as files or
			transfer to another server.
		</p>
  </div>
</div>
<?php endif; ?>

<div class="panel panel-default col-sm-6">
  <div class="panel-body">
		<h3><a href="/dyad">Alter Matching</a></h3>
		<p>
      Match alters from related interviews<br><br>
		</p>
  </div>
</div>

		<?php if(Yii::app()->user->isSuperAdmin): ?>
      <div class="panel panel-default col-sm-6">
        <div class="panel-body">
			<h3><?=CHtml::link('User Admin', $this->createUrl("/admin/user"))?></h3>
			<p>
				Add new users.<br><br>
			</p>
    </div>
  </div>
  <div class="panel panel-default col-sm-6">
    <div class="panel-body">
		<h3><?=CHtml::link('Mobile', $this->createUrl("/mobile"))?></h3>
		<p>
    Apps for iOS and Android.<br><br>
		</p>
</div>
</div>
		<?php endif; ?>

<div class="panel panel-default col-sm-6">
  <div class="panel-body">
		<h3><?=CHtml::link('Logout', $this->createUrl("/site/logout"))?></h3>
		<p>
			Logout of Admin Mode.<br><br>
		</p>
  </div>
</div>
