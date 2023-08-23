<script>
baseUrl = document.location.protocol + "//" + document.location.hostname + "/www/";
study = <?php echo $study ?>;
egoIdString = "<?php echo $ego_id_string ?>";
ego_id_questions = <?php echo $ego_id_questions ?>;
questions = <?php echo $questions ?>;
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
questionTitles = <?php echo $questionTitles ?>;
questionList = <?php echo $questionList ?>;
participantList = <?php echo $participantList ?>;
audio = <?php echo $audio; ?>;
otherGraphs = <?php echo $otherGraphs; ?>;
csrf = '<?php echo Yii::$app->request->getCsrfToken(); ?>';
isGuest = <?php echo(Yii::$app->user->isGuest ? 1 : 0); ?>;
if(isGuest && '<?php echo Yii::$app->session->get('redirect'); ?>' != '')
    window.localStorage.setItem('redirect', '<?php echo Yii::$app->session->get('redirect'); ?>');
else 
    window.localStorage.setItem('redirect', '');
redirect = window.localStorage.getItem('redirect');
</script>
<?php
$this->registerAssetBundle(\yii\web\JqueryAsset::className(), \yii\web\View::POS_HEAD);
use app\assets\InterviewAsset;
InterviewAsset::register($this);
?>
<div id="ngView" ng-view></div>
