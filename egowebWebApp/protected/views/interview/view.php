<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/angular.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/angular-route.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/app.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/egoweb.css'); ?>

<script>
study = <?php echo $study ?>;
questions = <?php echo $questions ?>;
ego_id_questions = <?php echo $ego_id_questions ?>;
ego_questions = <?php echo $ego_questions ?>;
alter_questions = <?php echo $alter_questions ?>;
alter_pair_questions = <?php echo $alter_pair_questions ?>;
network_questions = <?php echo $network_questions ?>;
expressions = <?php echo $expressions ?>;
options = <?php echo $options ?>;
interviewId = <?php echo $interviewId ? $interviewId : "undefined" ?>;
answers = <?php echo $answers ?>;
alters = <?php echo $alters ?>;
csrf = '<?php echo Yii::app()->request->csrfToken; ?>';
</script>
<div ng-view></div>
