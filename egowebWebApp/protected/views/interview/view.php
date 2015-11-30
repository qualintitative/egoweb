<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/angular.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/angular-route.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/autocomplete.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/app.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/autocomplete.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/sigma.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/sigma.notes.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.plugins.dragNodes.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.plugins.dragEvents.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.renderers.customEdgeShapes/shape-library.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.renderers.customEdgeShapes/sigma.renderers.customEdgeShapes.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.renderers.customShapes/shape-library.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.renderers.customShapes/sigma.renderers.customShapes.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/plugins/sigma.layout.forceAtlas2.min.js');
?>
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
prevAlters = <?php echo $prevAlters ?>;
graphs = <?php echo $graphs; ?>;
allNotes = <?php echo $allNotes; ?>;
alterPrompts = <?php echo $alterPrompts ?>;
questionList = <?php echo $questionList ?>;
participantList = <?php echo $participantList ?>;
csrf = '<?php echo Yii::app()->request->csrfToken; ?>';

$(function(){
    setTimeout(function(){
    $(".answerInput")[0].focus();
    }, 0);
})
$(document).keydown(function(e) {
	if($("textarea").length == 0 &&  e.keyCode == 13){
    		e.preventDefault();
		if($("#alterFormBox").length != 0 && $("#alterFormBox").html() != "")
			$('.alterSubmit')[0].click();
		else
			$('.orangebutton')[0].click();
	}
	if (e.keyCode == 37){
		e.preventDefault();
		$(".answerInput:focus").parent().prev().find(".answerInput").focus();
	}
	if (e.keyCode == 39){
		e.preventDefault();
		$(".answerInput:focus").parent().next().find(".answerInput").focus();
	}
	if (e.keyCode == 38){
		e.preventDefault();
        $(".answerInput").each(function(index){
            if($(this).is(":focus")){
                if(typeof $(".answerInput")[index-columns] != "undefined")
                    $(".answerInput")[index-columns].focus();
                else
                    $(".answerInput:focus").parent().prev().find(".answerInput").focus();
                return false;
            }
        });
	}
	if (e.keyCode == 40){
		e.preventDefault();
        $(".answerInput").each(function(index){
            if($(this).is(":focus")){
                if(typeof $(".answerInput")[index+columns] != "undefined")
                    $(".answerInput")[index+columns].focus();
                else
                    $(".answerInput:focus").parent().next().find(".answerInput").focus();
                return false;
            }
        });
	}
});
</script>
<div ng-view></div>
