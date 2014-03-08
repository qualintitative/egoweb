function getStudyList(server){
	url = 'http://' + server.ADDRESS + '/mobile/ajaxstudies';
	$.get(url,function(data){
		div = $('#' + server.ID);
		div.html('');
		$('#status').html('');
		list = JSON.parse(data);
		console.log(list);
		for(k in list){
			var localStudyId = db.queryValue("SELECT id FROM serverstudy WHERE address = '" + server.ADDRESS + "' AND serverstudyid = " + k);
			var interviews = 0;
			if(localStudyId)
				var interviews = db.queryObjects("SELECT id FROM interview WHERE studyId = " + localStudyId).data.length;
			console.log(localStudyId + ":" + interviews);
			if(typeof color == 'undefined' || color == ' colorB')
			    color = ' colorA';
			else
			    color = ' colorB';
			div.append("<div class='multiRow" + color + "' style='width:60%; text-align:left'>"+ list[k] + "</div>");
			if(!localStudyId)
			    div.append("<div class='multiRow" + color + "' style='width:15%'><button onclick = 'importStudy(\"" + server.ADDRESS + "\"," + k + ")'>Import Study</button></div>");
			else
			    div.append("<div class='multiRow" + color + "' style='width:15%'><button onclick='deleteStudy(" + localStudyId + ")'>Delete</button></div>");
			if(localStudyId && interviews > 0)
				div.append("<div class='multiRow" + color + "' style='width:25%'><button id='uploader-" + localStudyId + "' onclick='upload(" + localStudyId + ")' style='margin-left:20px; padding:3px'>Upload Data (" +  interviews + ")</button></div>");
			else if (!localStudyId || interviews == 0)
				div.append("<div class='multiRow" + color + "' style='width:25%'>&nbsp;</div>");

			div.append("<br style='clear:both'>");
		}
	});
}

function importStudy(address, id){

	db.catalog.setPersistenceScope(db.SCOPE_LOCAL);
	var server = db.queryRowObject("SELECT * FROM server WHERE address = '" + address + "'");

	$('#status').html("Importing study...");
	var url = 'http://' + address + '/mobile/ajaxdata/' + id;

	tableNames = new Array();
	for(i=0; i<db.catalog.getAllTables().length; i++){
		tableNames.push(db.catalog.getAllTables()[i].tableName);
	}
	console.log(tableNames);

	$.get(url,function(data){
		data = JSON.parse(data);
		console.log(data);

		var study = {
			tableName: "study",
			columns: 	[
				"id",
				"active",
				"name",
				"introduction",
				"egoIdPrompt",
				"alterPrompt",
				"conclusion",
				"minAlters",
				"maxAlters",
				"adjacencyExpressionId",
				"valueRefusal",
				"valueDontKnow",
				"valueLogicalSkip",
				"valueNotYetAnswered",
				"modified",
				"multiSessionEgoId"
			],
			primaryKey: [ "id" ],
		};

		if($.inArray('STUDY', tableNames) == -1)
			db.catalog.createTable(study);

		data.study[0] = parseInt(data.study[0]);
		newId = db.queryValue("SELECT id FROM serverStudy ORDER BY id DESC");
		if(!newId)
		    newId = 0;
		newId = parseInt(newId) + 1;
		newstudy = [
		    newId,
		    address,
		    data.study[0]
		]
		db.catalog.getTable('serverstudy').insertRow(newstudy);
		data.study[0] = newId;
		db.catalog.getTable('study').insertRow(data.study);

		var question = {
			tableName: "question",
			columns: 	[
				"id",
				"active",
				"title",
				"prompt",
				"preface",
				"citation",
				"subjectType",
				"answerType",
				"askingStyleList",
				"ordering",
				"otherSpecify",
				"noneButton",
				"allButton",
				"pageLevelDontKnowButton",
				"pageLevelRefuseButton",
				"dontKnowButton",
				"refuseButton",
				"allOptionString",
				"uselfExpression",
				"minLimitType",
				"minLiteral",
				"minPrevQues",
				"maxLimitType",
				"maxLiteral",
				"maxPrevQues",
				"minCheckableBoxes",
				"maxCheckableBoxes",
				"withListRange",
				"listRangeString",
				"minListRange",
				"maxListRange",
				"timeUnits",
				"symmetric",
				"keepOnSamePage",
				"studyId",
				"answerReasonExpressionId",
				"networkRelationshipExprId",
				"networkNShapeQId",
				"networkNColorQId",
				"networkNSizeQId",
				"networkEColorQId",
				"networkESizeQId",
				"useAlterListField",
			],
		};

		if($.inArray('QUESTION', tableNames) == -1)
			db.catalog.createTable(question);

		for (k in data.questions){
		    data.questions[k][0] = parseInt(data.questions[k][0]);
			data.questions[k][34] = newId;
		    data.questions[k][9] = parseInt(data.questions[k][9]);
		    db.catalog.getTable('question').insertRow(data.questions[k]);
		}
		var questionOption = {
		    tableName: "questionOption",
		    columns: 	[
		    	"id",
		    	"active",
		    	"studyId",
		    	"questionId",
		    	"name",
		    	"value",
		    	"ordering"
		    ],
		};

		if($.inArray('QUESTIONOPTION', tableNames) == -1)
			db.catalog.createTable(questionOption);

		for (k in data.options){
			data.options[k][0] = parseInt(data.options[k][0]);
			data.options[k][2] = newId;
			db.catalog.getTable('questionOption').insertRow(data.options[k]);
		}
		var expression = {
			tableName: "expression",
			columns: 	[
				"id",
				"active",
				"name",
				"type",
				"operator",
				"value",
				"resultForUnanswered",
				"studyId",
				"questionId"
			],
		};

if($.inArray('EXPRESSION', tableNames) == -1)
	db.catalog.createTable(expression);
for (k in data.expressions){
		data.expressions[k][0] = parseInt(data.expressions[k][0]);
		data.expressions[k][7] = newId;
		db.catalog.getTable('expression').insertRow(data.expressions[k]);
}
var answer = {
	tableName: "answer",
	columns: 	[
				"id",
				"active",
				"questionId",
				"interviewId",
				"alterId1",
				"alterId2",
				"value",
				"otherSpecifyText",
				"skipReason",
				"studyId",
				"questionType",
				"answerType"
				],
	primaryKey: [ "id" ]
};
if($.inArray('ANSWER', tableNames) == -1)
	db.catalog.createTable(answer);

var alters = {
	tableName: "alters",
	columns: 	[
				"id",
				"active",
				"ordering",
				"name",
				"interviewId",
				"alterListId"
				],
	primaryKey: [ "id" ]
};
if($.inArray('ALTERS', tableNames) == -1)
	db.catalog.createTable(alters);

var interview = {
	tableName: "interview",
	columns: 	[
				"id",
				"active",
				"studyId",
				"completed"
				],
	primaryKey: [ "id" ]
};
if($.inArray('INTERVIEW',tableNames) == -1)
	db.catalog.createTable(interview);

db.commit();
	$('#status').html($('#status').html()+"DONE!");
	getStudyList(server);

});
}