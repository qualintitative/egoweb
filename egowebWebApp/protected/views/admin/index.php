<?php
/* @var $this AdminController */
?>
	<table cellspacing=0 cellpadding=0 class="admin">
	<tr>
	<td width=50%>
		<h3><a href="/interviewing">Interviewing</a></h3>
		<p>
			Start a new interview or continue a partially completed interview.
		</p>
	</td>
	</tr>
	<?php if(Yii::app()->user->isSuperAdmin): ?>
	<tr>
	<td>
		<h3><a href="/authoring">Authoring</a></h3>
		<p>
			Create a new interview, add or change questions for an existing interview.
		</p>
	</td>
	<td>
		<h3><a href="/analysis">Analysis</a></h3>
		<p>
			Analyze the data from completed interviews.
		</p>
	</td>
	</tr>
	<tr>
	<td>
		<h3><a href="/importExport">Import &amp; Export</a></h3>
		<p>
			Save study and respondent data as files for archiving or
			transferring between computers.
		</p>
	</td>
	<td>
		<h3><a href="/admin/user">User Admin</a></h3>
		<p>
			Add new users
		</p>
	</td>
	</tr>
	<?php endif; ?>
	<tr>
	<td>
		<h3><a href="/mobile">Mobile</a></h3>
		<p>
			Egoweb Mobile development
		</p>
	</td>
	<td>
		<h3><a href="/site/logout">Logout</a></h3>
		<p>
			Logout of Admin Mode
		</p>
	</td>
	</tr>
	</table>