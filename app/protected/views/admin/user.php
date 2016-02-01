<h3 class="margin-top-10">User Admin</h3>
<div id="userlist">
<?php $this->renderPartial('_view_user', array('dataProvider'=>$dataProvider, 'ajax'=>true), false, false); ?>
</div>
<div id="userform">
<?php
$user = new User;
$this->renderPartial('_form_user', array('user'=>$user, 'ajax'=>true), false, false);
 ?>
</div>
<div id="edit-user">
</div>