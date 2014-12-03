function getStudyList(server){
	url = 'http://' + server.ADDRESS + '/mobile/ajaxstudies';
	//if(typeof userId != "undefined")
	//	url = url + "?userId=" + userId;
	$.post(url, { userId:  userId } ,function(data){
		div = $('#' + server.ID);
		div.html('');
		$('#status').html('');
		list = JSON.parse(data);
		console.log(list);
		for(k in list){
			var localStudyId = db.queryValue("SELECT id FROM serverstudy WHERE address = '" + server.ADDRESS + "' AND serverstudyid = " + k);
			var interviews = 0;
			if(localStudyId)
				var interviews = db.queryObjects("SELECT id FROM interview WHERE studyId = " + localStudyId + " AND completed = -1").data.length;
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
	loadedAudioFiles = 0;
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
			columns: data['columns']['study'],
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

		totalAudioFiles = data.audioFiles.length;
		displayAudioLoad();
		if(totalAudioFiles > 0){
			var a = new DirManager();
			console.log(a);
			a.create_r('egowebaudio/' + data.study[0] + "/EGO", console.log('created successfully'));
			a.create_r('egowebaudio/' + data.study[0] + "/ALTER", console.log('created successfully'));
			a.create_r('egowebaudio/' + data.study[0] + "/ALTER_PAIR", console.log('created successfully'));
			a.create_r('egowebaudio/' + data.study[0] + "/NETWORK", console.log('created successfully'));
			a.create_r('egowebaudio/' + data.study[0] + "/OPTION", console.log('created successfully'));
			a.create_r('egowebaudio/' + data.study[0] + "/PREFACE", console.log('created successfully'));
			var b = new FileManager();
			for(var j in data.audioFiles){
				console.log(j);
				b.download_file(data.audioFiles[j].url,'egowebaudio/' + data.study[0] + '/' + data.audioFiles[j].type + '/', data.audioFiles[j].id + ".mp3", function(){loadedAudioFiles++;displayAudioLoad()});
			}
		}

		var question = {
			tableName: "question",
			columns: data['columns']['question'],
			primaryKey: [ "id" ]
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
			columns: data['columns']['questionOption'],
			primaryKey: [ "id" ]
		};

		if($.inArray('QUESTIONOPTION', tableNames) == -1)
			db.catalog.createTable(questionOption);

		for (k in data.options){
			data.options[k][0] = parseInt(data.options[k][0]);
			data.options[k][6] = parseInt(data.options[k][6]);
			data.options[k][2] = newId;
			try{
				db.catalog.getTable('questionOption').insertRow(data.options[k]);
			}catch(err){
				console.log(data.options[k]);
			}
		}
		var expression = {
			tableName: "expression",
			columns: data['columns']['expression'],
			primaryKey: [ "id" ]
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
	columns: data['columns']['answer'],
	primaryKey: [ "id" ]
};
if($.inArray('ANSWER', tableNames) == -1)
	db.catalog.createTable(answer);

var alters = {
	tableName: "alters",
	columns: data['columns']['alters'],
	primaryKey: [ "id" ]
};
if($.inArray('ALTERS', tableNames) == -1)
	db.catalog.createTable(alters);

var interview = {
	tableName: "interview",
	columns: data['columns']['interview'],
	primaryKey: [ "id" ]
};
if($.inArray('INTERVIEW',tableNames) == -1)
	db.catalog.createTable(interview);

db.commit();
	//$('#status').html($('#status').html()+"DONE!");
	getStudyList(server);

});
}

function displayAudioLoad() {
		$('#status').html("Importing audio files: " + loadedAudioFiles + " / " + totalAudioFiles);
		if(loadedAudioFiles == totalAudioFiles){
			$('#status').html("Done!");
			setTimeout(function(){
				//$('#status').html("");
   		  	}, 1000);
		}
}