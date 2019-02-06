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
		<h3><a href="/interview">Interviewing</a></h3>
		<p>
			Start a new interview or continue a partially completed interview.
		</p>
	</div>
</div>
<?php endif; ?>

	<?php if(Yii::app()->user->isAdmin): ?>

    <div class="panel panel-default col-sm-6">
      <div class="panel-body">

		<h3><a href="/authoring">Authoring</a></h3>
		<p>
			Create a new interview, add or change questions for an existing interview.
		</p>
  </div>
</div>
<div class="panel panel-default col-sm-6">
  <div class="panel-body">

		<h3><a href="/data">Data Processing</a></h3>
		<p>
			Analyze the data from completed interviews.<br><br>
		</p>
  </div>
</div>
<div class="panel panel-default col-sm-6">
  <div class="panel-body">
		<h3><a href="/importExport">Import &amp; Export Studies</a></h3>
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
			<h3><a href="/admin/user">User Admin</a></h3>
			<p>
				Add new users.<br><br>
			</p>
    </div>
  </div>
  <div class="panel panel-default col-sm-6">
    <div class="panel-body">
  <h3><a href="/mobile">Mobile</a></h3>
  <p>
    Apps for iOS and Android.<br><br>
  </p>
</div>
</div>
		<?php endif; ?>

<div class="panel panel-default col-sm-6">
  <div class="panel-body">
		<h3><a href="/site/logout">Logout</a></h3>
		<p>
			Logout of Admin Mode.<br><br>
		</p>
  </div>
</div>
