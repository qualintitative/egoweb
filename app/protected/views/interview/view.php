<script>
baseUrl = "/www/";
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
interview = <?php echo $interview; ?>;
answers = <?php echo $answers ?>;
alters = <?php echo $alters ?>;
prevAlters = <?php echo $prevAlters ?>;
graphs = <?php echo $graphs; ?>;
allNotes = <?php echo $allNotes; ?>;
alterPrompts = <?php echo $alterPrompts ?>;
questionList = <?php echo $questionList ?>;
participantList = <?php echo $participantList ?>;
audio = <?php echo $audio; ?>;
otherGraphs = <?php echo $otherGraphs; ?>;
csrf = '<?php echo Yii::app()->request->csrfToken; ?>';
redirect = '<?php echo Yii::app()->session['redirect']; ?>';
isGuest = <?php echo (Yii::app()->user->isGuest ? 1 : 0); ?>;
</script>
<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/angular.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/angular-route.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/autocomplete.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/interview.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/www/js/server.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/sigma.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/sigma.plugins.dragNodes.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/shape-library.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/sigma.renderers.customShapes.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/sigma.layout.forceAtlas2.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/plugins/sigma.notes.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/www/css/autocomplete.css');
?>
<div ng-view></div>
