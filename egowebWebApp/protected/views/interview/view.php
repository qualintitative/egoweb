<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/angular.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/angular-route.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/autocomplete.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/app.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/egoweb.css');
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
alterPrompts = <?php echo $alterPrompts ?>;
questionList = <?php echo $questionList ?>;
participantList = <?php echo $participantList ?>;
csrf = '<?php echo Yii::app()->request->csrfToken; ?>';

$(document).keydown(function(e) {
	if($("textarea").length == 0 &&  e.keyCode == 13){
		if($("#alterFormBox").length != 0 && $("#alterFormBox").html() != "")
			$('.alterSubmit').submit();
		else
			$('#answer-form').submit();
	}
	if (e.keyCode == 39){
		e.preventDefault();
		$("input:focus").next().focus();
	}
	if (e.keyCode == 37){
		e.preventDefault();
		$("input:focus").prev().focus();
	}
	if (e.keyCode == 38){
		e.preventDefault();
		var counter = $("input:focus").parent().attr("counter");
		if(typeof counter != "undefined"){
			var index = $("input:focus").index();
			if(counter > 0)
				counter--;
			$("[counter='" + counter + "']").children()[index].focus();
		}else{
			$(".answerInput").each(function(i){
			if($(this).is(":focus"))
				index = i;
			});					console.log(index);
			if(index > 0)
				index--;
			$(".answerInput")[index].focus();
			console.log("focus:" + index);
		}
	}
	if (e.keyCode == 40){
		e.preventDefault();
		var counter = $("input:focus").parent().attr("counter");
		if(typeof counter != "undefined"){
			var index = $("input:focus").index();
			counter++;
			if($("[counter='" + counter + "']").length == 0)
				counter--;
			$("[counter='" + counter + "']").children()[index].focus();
		}else{
			$(".answerInput").each(function(i){
			if($(this).is(":focus"))
				index = i;
			});
			console.log(index);
			if(typeof $(".answerInput")[index+1] != "undefined")
				index++;
			$(".answerInput")[index].focus();
			console.log("focus:" + index);
		}
	}
});
</script>
<div ng-view></div>
