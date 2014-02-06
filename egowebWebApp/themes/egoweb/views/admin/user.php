<div id="userlist">
<?php $this->renderPartial('_view_user', array('dataProvider'=>$dataProvider, 'ajax'=>true), false, true); ?>
</div>
<div id="userform">
<?php
$user = new User;
$this->renderPartial('_form_user', array('user'=>$user, 'ajax'=>true), false, true);
 ?>
</div>
<div id="edit-user">
</div>