<?php

/* @var $this AdminController */
$this->pageTitle =  "Admin";

echo  decrypt("G7dAopA6940OzyQ4bgd2ug==");
echo "<br>";
echo decrypt("wXetk+J7/bsaLxW99xNXLsz863HKvAc9bD4pabhjhTjpzvHjMustZZYUr6nzpLti");
?>
	<table cellspacing=0 cellpadding=0 class="admin">
	<tr>
	<td width=50%>
		<h3><a href="/interview">Interviewing</a></h3>
		<p>
			Start a new interview or continue a partially completed interview.  On older browser, you may need to use the <a href="/interviewing">Legacy Interview</a> instead.
		</p>
	</td>
	<?php if(Yii::app()->user->isAdmin): ?>
	<td>
		<h3><a href="/authoring">Authoring</a></h3>
		<p>
			Create a new interview, add or change questions for an existing interview.
		</p>
	</td>
	</tr>
	<tr>
	<td>
		<h3><a href="/data">Data Processing</a></h3>
		<p>
			Analyze the data from completed interviews.
		</p>
	</td>
	<td>
		<h3><a href="/archive">Archive</a></h3>
		<p>
			Archive studies that are no longer active.
		</p>
	</td>
	</tr>
	<tr>
	<td>
		<h3><a href="/importExport">Import &amp; Export Studies</a></h3>
		<p>
			Save study and respondent data as files for archiving or
			transferring between computers.
		</p>
	</td>
		<?php if(Yii::app()->user->isSuperAdmin): ?>
		<td>
			<h3><a href="/admin/user">User Admin</a></h3>
			<p>
				Add new users.
			</p>
		</td>
		<?php endif; ?>
	</tr>
	<?php endif; ?>
	</tr>
	<tr>
	<td>
		<h3><a href="/mobile">Mobile</a></h3>
		<p>
			Apps for iOS and Android.
		</p>
	</td>
	<td>
		<h3><a href="/site/logout">Logout</a></h3>
		<p>
			Logout of Admin Mode.
		</p>
	</td>
	</tr>
	</table>
	<span style="color: #fff"><?php echo Yii::app()->getBaseUrl(true);?></span>
