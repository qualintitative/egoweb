isGuest = 0;

app.config(function($routeProvider) {
    $routeProvider

    .when('/', {
        templateUrl: baseUrl + 'main.html',
        controller: 'mainController'
    })

    .when('/admin', {
        templateUrl: baseUrl + 'admin.html',
        controller: 'adminController'
    })

    .when('/studies', {
        templateUrl: baseUrl + 'studies.html',
        controller: 'studiesController'
    })

});

app.factory("getStudies", function($http, $q) {
    var result = function(id) {
        var server = db.queryRowObject("SELECT * FROM server WHERE id = " + id);
        var url = server.ADDRESS + '/mobile/getstudies';
        if (!url.match('http') && !url.match('https')) url = "http://" + url;
        return $.ajax({
            url: url,
            type: 'POST',
            data: $("#serverForm_" + server.ID).serialize(),
            crossDomain: true,
            success: function(data) {
                if (data != "error") {
                    return data;
                    $('#addServerButton').show();
                } else {
                    $('#status').html($('#status').html() + 'validation failed');
                }
            },
            error: function(data) {
                $('#status').html($('#status').html() + 'error');
            }
        });
    }
    return {
        result: result
    }
});

app.factory("uploadData", function($http, $q) {
    var result = function(address) {
        var url = address + '/mobile/getstudies';
        if (!url.match('http') && !url.match('https')) url = "http://" + url;
        return $.ajax({
            url: url,
            type: 'POST',
            data: $("#serverForm").serialize(),
            crossDomain: true,
            success: function(data) {
                if (data != "error") {
                    return data;
                    $('#addServerButton').show();
                } else {
                    $('#status').html($('#status').html() + 'validation failed');
                }
            },
            error: function(data) {
                $('#status').html($('#status').html() + 'error');
            }
        });
    }
    return {
        result: result
    }
});

app.factory("getServer", function($http, $q) {
    var result = function(address) {
        var url = address + '/mobile/check';
        if (!url.match('http') && !url.match('https')) url = "http://" + url;
        return $.ajax({
            url: url,
            type: 'GET',
            crossDomain: true,
            success: function(data) {
                if (data == "success") {
                    newId = db.queryValue("SELECT id FROM server ORDER BY id DESC");
                    if (!newId) newId = 0;
                    server = [parseInt(newId) + 1, address];
                    db.catalog.getTable('server').insertRow(server);
                    db.commit();
                    return db.queryObjects("SELECT * FROM server").data;
                    //$('#status').html('successfully added server');
                    //$("#page").html($("#serverList").html());
                    //listServers($("#list"));
                } else {
                    $('#status').html($('#status').html() + 'no response from server');
                }
            },
            error: function(data) {
                $('#status').html($('#status').html() + 'error connecting to server');
            }
        });
    }
    return {
        result: result
    }
});

app.factory("saveAlter", function($http, $q) {
    var getAlters = function() {
        var name = $("#Alters_name").val();
    	if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
    		var interviewIds = getInterviewIds(interviewId);
    		for(k in interviewIds){
    			var oldAlter = db.queryRow("SELECT * FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewIds[k] + ",%' AND name = '" + name + "'");
    			if(oldAlter)
    				break;
    		}
    	}
    	if(typeof oldAlter != "undefined" && oldAlter){
    		alter = [
    			oldAlter[0],
    			1,
    			parseInt(db.queryValue("SELECT ordering FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewId + ",%' ORDER BY ordering DESC")) + 1,
    			name,
    			oldAlter[4] + "," + interviewId,
    			''
    		];
    		db.catalog.getTable('alters').updateRow(alter);
    	}else{
    		var newId = db.queryValue("SELECT id FROM alters ORDER BY id DESC");
    		if(!newId)
    			newId = 0;
    		newId = parseInt(newId) + 1;
    		alters[newId] = {
    			ID: newId,
    			ACTIVE:1,
    			ORDERING: parseInt(db.queryValue("SELECT ordering FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewId + ",%' ORDER BY ordering DESC")) + 1,
    			NAME: name,
    			INTERVIEWID: interviewId,
    			ALTERLISTID: ''
    		};
    		db.catalog.getTable('alters').insertRow(objToArray(alters[newId]));
    	}
    	var notifyObj = {
            onsuccess: function() {
		        return alters;
	        }
	   }
    	db.commit();
    	defer = $.Deferred();
    	return defer.resolve(JSON.stringify(alters));
    }
    return {
        getAlters : getAlters
    }
});

app.factory("deleteAlter", function($http, $q) {
    var getAlters = function() {
        var id = $("#deleteAlterId").val();
    	var alter = db.queryRow("SELECT * FROM alters WHERE id = " + id);
    	if(alter && typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
    		var interviewIds = alter[4].toString().split(",");
    		$(interviewIds).each(function(index){
    			if(interviewIds[index] == interviewId)
    				interviewIds.splice(index,1);
    		});
    		alter[4] = interviewIds.join(",");
    		alters[id].INTERVIEWID = alter[4];
    		db.catalog.getTable('alters').updateRow(alter);
    	}else{
        	delete alters[id];
    		db.catalog.getTable('alters').deleteRow(alter);
    	}
    	db.commit();
    	defer = $.Deferred();
    	return defer.resolve(JSON.stringify(alters));
    }
    return {
        getAlters : getAlters
    }
});


app.factory("importStudy", function($http, $q) {
    var result = function(address, studyId) {
        loadedAudioFiles = 0;
        totalAudioFiles = 0;

        var server = db.queryRowObject("SELECT * FROM server WHERE address = '" + address + "'");
        $('#status').html("Importing study...");
        var url = address + '/mobile/ajaxdata/' + studyId;
        if (!url.match('http') && !url.match('https')) url = "http://" + url;

        tableNames = new Array();
        for (i = 0; i < db.catalog.getAllTables().length; i++) {
            tableNames.push(db.catalog.getAllTables()[i].tableName);
        }
        console.log(tableNames);
        return $.ajax({
            url: url,
            type: "GET",
            timeout: 30000,
            crossDomain: true,
            success: function(data) {
                data = JSON.parse(data);
                console.log(data);
                var study = {
                    tableName: "study",
                    columns: data['columns']['study'],
                    primaryKey: ["id"],
                };
                if ($.inArray('STUDY', tableNames) == -1) db.catalog.createTable(study);
                data.study[0] = parseInt(data.study[0]);
                newId = db.queryValue("SELECT id FROM serverStudy ORDER BY id DESC");
                if (!newId) newId = 0;
                newId = parseInt(newId) + 1;
                newstudy = [
                newId, address, data.study[0]]
                db.catalog.getTable('serverstudy').insertRow(newstudy);
                data.study[0] = newId;
                db.catalog.getTable('study').insertRow(data.study);
                totalAudioFiles = data.audioFiles.length;
                displayAudioLoad();
                if (totalAudioFiles > 0) {
                    var a = new DirManager();
                    console.log(a);
                    a.create_r('egowebaudio/' + data.study[0] + "/EGO", console.log('created successfully'));
                    a.create_r('egowebaudio/' + data.study[0] + "/ALTER", console.log('created successfully'));
                    a.create_r('egowebaudio/' + data.study[0] + "/ALTER_PAIR", console.log('created successfully'));
                    a.create_r('egowebaudio/' + data.study[0] + "/NETWORK", console.log('created successfully'));
                    a.create_r('egowebaudio/' + data.study[0] + "/OPTION", console.log('created successfully'));
                    a.create_r('egowebaudio/' + data.study[0] + "/PREFACE", console.log('created successfully'));
                    var b = new FileManager();
                    for (var j in data.audioFiles) {
                        console.log(j);
                        b.download_file(data.audioFiles[j].url, 'egowebaudio/' + data.study[0] + '/' + data.audioFiles[j].type + '/', data.audioFiles[j].id + ".mp3", function() {
                            loadedAudioFiles++;
                            displayAudioLoad()
                        });
                    }
                }
                var question = {
                    tableName: "question",
                    columns: data['columns']['question'],
                    primaryKey: ["id"]
                };
                if ($.inArray('QUESTION', tableNames) == -1) db.catalog.createTable(question);

                var questionOption = {
                    tableName: "questionOption",
                    columns: data['columns']['questionOption'],
                    primaryKey: ["id"]
                };
                if ($.inArray('QUESTIONOPTION', tableNames) == -1) db.catalog.createTable(questionOption);
                var expression = {
                    tableName: "expression",
                    columns: data['columns']['expression'],
                    primaryKey: ["id"]
                };
                if ($.inArray('EXPRESSION', tableNames) == -1) db.catalog.createTable(expression);

                var answer = {
                    tableName: "answer",
                    columns: data['columns']['answer'],
                    primaryKey: ["id"]
                };
                if ($.inArray('ANSWER', tableNames) == -1) db.catalog.createTable(answer);
                var alters = {
                    tableName: "alters",
                    columns: data['columns']['alters'],
                    primaryKey: ["id"]
                };
                if ($.inArray('ALTERS', tableNames) == -1) db.catalog.createTable(alters);
                var interview = {
                    tableName: "interview",
                    columns: data['columns']['interview'],
                    primaryKey: ["id"]
                };
                if ($.inArray('INTERVIEW', tableNames) == -1) db.catalog.createTable(interview);
                var alterList = {
                    tableName: "alterList",
                    columns: data['columns']['alterList'],
                    primaryKey: ["id"]
                };
                if ($.inArray('ALTERLIST', tableNames) == -1) db.catalog.createTable(alterList);
                var alterPrompt = {
                    tableName: "alterPrompt",
                    columns: data['columns']['alterPrompt'],
                    primaryKey: ["id"]
                };
                if ($.inArray('ALTERPROMPT', tableNames) == -1) db.catalog.createTable(alterPrompt);
                var graphs = {
                    tableName: "graphs",
                    columns: data['columns']['graphs'],
                    primaryKey: ["id"]
                };
                if ($.inArray('GRAPHS', tableNames) == -1) db.catalog.createTable(graphs);
                var notes = {
                    tableName: "notes",
                    columns: data['columns']['notes'],
                    primaryKey: ["id"]
                };
                if ($.inArray('NOTES', tableNames) == -1) db.catalog.createTable(notes);
                console.log("tables created...");
                for (k in data.questions) {
                    data.questions[k][0] = parseInt(data.questions[k][0]);
                    data.questions[k][34] = newId;
                    data.questions[k][9] = parseInt(data.questions[k][9]);
                    db.catalog.getTable('question').insertRow(data.questions[k]);
                }
                console.log("questions imported...");
                for (k in data.options) {
                    data.options[k][0] = parseInt(data.options[k][0]);
                    data.options[k][6] = parseInt(data.options[k][6]);
                    data.options[k][2] = newId;
                    try {
                        db.catalog.getTable('questionOption').insertRow(data.options[k]);
                    } catch (err) {
                        console.log(data.options[k]);
                    }
                }
                console.log("options imported...");
                for (k in data.expressions) {
                    data.expressions[k][0] = parseInt(data.expressions[k][0]);
                    data.expressions[k][7] = newId;
                    db.catalog.getTable('expression').insertRow(data.expressions[k]);
                }
                console.log("expressions imported...");
                for (k in data.alterList) {
                    data.alterList[k][0] = parseInt(data.alterList[k][0]);
                    data.alterList[k][1] = newId;
                    db.catalog.getTable('alterList').insertRow(data.alterList[k]);
                }
                console.log("alterList imported...");
                for (k in data.alterPrompts) {
                    data.alterPrompts[k][0] = parseInt(data.alterPrompts[k][0]);
                    data.alterPrompts[k][1] = newId;
                    db.catalog.getTable('alterPrompt').insertRow(data.alterPrompts[k]);
                }
                console.log("alter prompts imported...");
                db.commit();
                //$('#status').html($('#status').html()+"DONE!");
            }
        });
    }
    return {
        result: result
    }
});

app.controller('mainController', ['$scope', '$log', '$routeParams', '$sce', '$location', '$route', function($scope, $log, $routeParams, $sce, $location, $route) {
    studyList = {};
    $("#questionMenu").addClass("hidden");
    $("#studyTitle").html("");
    $("#questionTitle").html("");
}]);

app.controller('studiesController', ['$scope', '$log', '$routeParams', '$sce', '$location', '$route', function($scope, $log, $routeParams, $sce, $location, $route) {
    $("#questionMenu").addClass("hidden");
    $("#studyTitle").html("Studies");
    $("#questionTitle").html("");
    $scope.studies = db.queryObjects("SELECT * FROM study").data;
    $scope.interviews = [];
    $scope.done = [];
    justUploaded = [];
    studyList = {};

    for(k in $scope.studies){
        var interviews = db.queryObjects("SELECT * FROM interview WHERE studyId = " + $scope.studies[k].ID).data;
        if(typeof $scope.done[$scope.studies[k].ID] == "undefined")
            $scope.done[$scope.studies[k].ID] = [];
        for(i in interviews){
            interviews[i].egoValue = getEgoIdValue(interviews[i].ID);
            if(interviews[i].COMPLETED == "-1")
                $scope.done[$scope.studies[k].ID].push(interviews[i].ID);
        }
        $scope.interviews[$scope.studies[k].ID] = interviews;
    }
    console.log($scope.interviews);
    $scope.startSurvey = function(studyId, intId) {
	    study = db.queryRowObject("SELECT * FROM study WHERE id = " + studyId);
        if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
            var qTitle = db.queryValue("SELECT title FROM question WHERE ID = " + study.MULTISESSIONEGOID);
        	var column = db.queryObjects("SELECT STUDYID FROM question WHERE title = '" + qTitle + "'").data;
        	var multiIds = [];
        	for (var k in column){
        		multiIds.push(column[k].STUDYID)
        	}
    	}else{
            var multiIds = [studyId];
    	}
        var studies = db.queryObjects("SELECT * FROM study WHERE id in (" + multiIds.join(",") + ")").data;
        var studyNames = [];
        for(k in studies){
            studyNames[studies[k].ID] = studies[k].NAME;
        }
    	results = db.queryObjects("SELECT * FROM question WHERE studyId in (" + multiIds.join(",") + ") ORDER BY ORDERING").data;
    	questions = [];
    	questionList = [];
        for(k in results){
            questions[results[k].ID] = results[k];
            if(typeof questionList[studyNames[results[k].STUDYID]] == "undefined")
                questionList[studyNames[results[k].STUDYID]] = {};
            questionList[studyNames[results[k].STUDYID]][results[k].TITLE] = results[k].ID;
        }
        console.log(questionList);
    	ego_id_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'EGO_ID' AND studyId = " + studyId + " ORDER BY ORDERING").data;
    	ego_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'EGO' AND studyId = " + studyId + " ORDER BY ORDERING").data;
    	alter_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'ALTER' AND studyId = " + studyId + " ORDER BY ORDERING").data;
    	alter_pair_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = " + studyId + " ORDER BY ORDERING").data;
    	network_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'NETWORK' AND studyId = " + studyId + " ORDER BY ORDERING").data;
    	results = db.queryObjects("SELECT * FROM questionOption WHERE studyId = " + studyId + " ORDER BY ORDERING").data;
    	options = [];
    	for(k in results){
        	if(typeof options[results[k].QUESTIONID] == "undefined")
            	options[results[k].QUESTIONID] = [];
        	options[results[k].QUESTIONID][results[k].ORDERING] = results[k];
    	}
        alterPrompts = [];
    	results =  db.queryObjects("SELECT * FROM alterPrompt WHERE studyId = " + studyId).data;
        for(k in results){
            alterPrompts[results[k].AFTERALTERSENTERED] = results[k].DISPLAY;
        }
    	results =  db.queryObjects("SELECT * FROM expression WHERE studyId = " + studyId).data;
    	expressions = [];
        for(k in results){
            expressions[results[k].ID] = results[k];
    	}
    	results =  db.queryObjects("SELECT * FROM alterList WHERE studyId = " + studyId).data;
    	participantList = [];
    	participantList['email'] = [];
    	participantList['name'] = [];
        for(k in results){
            if(results[k].EMAIL)
                participantList['email'].push(results[k].EMAIL);
            if(results[k].NAME)
                participantList['name'].push(results[k].NAME);
    	}
    	csrf = "";
        answers = {};
        audio = [];
		alters = {};
        prevAlters = {};
        graphs = {};
        allNotes = {};
        otherGraphs = {};
    	if(typeof intId == "undefined"){
        	interviewId = undefined;
        	interview = false;
            var page = 0;
    	}else{
    		interviewId = intId;
    		interview = db.queryRowObject("SELECT * FROM interview WHERE id = " + intId);
    		results = db.queryObjects("SELECT * FROM graphs WHERE interviewId = " + interviewId).data;
            for (k in results){
                graphs[results[k].EXPRESSIONID] = results[k];
            }
    		results = db.queryObjects("SELECT * FROM notes WHERE interviewId = " + interviewId).data;
            for (k in results){
                if(typeof allNotes[results[k].EXPRESSIONID] == "undefined")
                    allNotes[results[k].EXPRESSIONID] = {};
                allNotes[results[k].EXPRESSIONID][results[k].ALTERID] = results[k];
            }
        	if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
        		var interviewIds = getInterviewIds(interviewId);
        		interviewIds.splice(interviewIds.indexOf(interviewId),1);
        		for(var k in interviewIds){
        			prevAlters = db.queryObjects("SELECT * FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewIds[k] + ",%' AND CONCAT(',', interviewId, ',') NOT LIKE '%," + interviewId + ",%'").data;
        		}
        	}
    		results = db.queryObjects("SELECT * FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewId + ",%'").data;
            for (k in results){
                alters[results[k].ID] = results[k];
            }
    		results = db.queryObjects("SELECT * FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewId + ",%'").data;
            for (k in results){
                alters[results[k].ID] = results[k];
            }
    		var page = db.queryValue("SELECT completed FROM interview WHERE id = " + intId);
    		if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
    			var interviewIds = getInterviewIds(intId);
    			results = db.queryObjects("SELECT * FROM answer WHERE interviewId in (" + interviewIds.join(",") + ")").data;
    		}else{
    			results = db.queryObjects("SELECT * FROM answer WHERE interviewId = " + intId).data;
    		}
    		for (k in results){
    			if(results[k].QUESTIONTYPE == "ALTER")
    				array_id = results[k].QUESTIONID + "-" + results[k].ALTERID1;
    			else if(results[k].QUESTIONTYPE == "ALTER_PAIR")
    				array_id = results[k].QUESTIONID + "-" + results[k].ALTERID1 + "and" + results[k].ALTERID2;
    			else
    				array_id = results[k].QUESTIONID;
    			results[k].ID = parseInt(results[k].ID);
    			answers[array_id] = results[k];
    		}
    		if(page == -1)
    			page = 0;
    	}
        var url = $location.absUrl().replace($location.url(),'');
        $("#studyTitle").html(study.NAME);
        document.location = url + "/page/" + parseInt(page);
    }

    $scope.upload = function(studyId){
        $('#status').html('Uploading...');
    	$("#uploader-" + studyId).prop('disabled', true);

    	var serverAddress = db.queryValue("SELECT address FROM serverstudy WHERE id = " + studyId);
    	var url = serverAddress + "/mobile/uploadData";
        if (!url.match('http') && !url.match('https')) url = "http://" + url;
    	$('#data').val(createSurveyJSON(studyId));
    	console.log($('#data').val());
    	$.ajax({
    		type:'POST',
    		url:url,
            crossDomain: true,
    		data:$('#hiddenForm').serialize(),
    		success:function(data){
    			if(data.match("Upload completed.  No Errors Found")){
    				justUploaded.push(studyId);
    				deleteInterviews(studyId);
    				$route.reload();
    			}
    			$('#status').html(data);
    		},
    		error:function(xhr, ajaxOptions, thrownError){
    			$('#status').html('Error: ' + xhr.status);
    			$("#uploader-" + studyId).prop('disabled', false);
    		}
    	});
	}
}]);

app.controller('adminController', ['$scope', '$log', '$routeParams', '$sce', '$location', '$route', 'getServer', 'getStudies', 'importStudy', function($scope, $log, $routeParams, $sce, $location, $route, getServer, getStudies, importStudy) {
    $scope.address = "";
    $("#studyTitle").html("Admin");
    $("#questionTitle").html("");
    $("#questionMenu").addClass("hidden");
    console.log(studyList);
    $scope.studyList = studyList;
    $scope.connect = function(id) {
        getStudies.result(id).then(function(data) {
            var server = db.queryRowObject("SELECT * FROM server WHERE id = " + id);
            studyList[server.ADDRESS] = JSON.parse(data);
            for(k in studyList[server.ADDRESS]){
                studyList[server.ADDRESS][k].localStudyId = db.queryValue("SELECT id FROM serverstudy WHERE address = '" + server.ADDRESS + "' AND serverstudyid = " + studyList[server.ADDRESS][k].id);
            }
            console.log(studyList);
            $route.reload();
        });
    }
    $scope.importStudy = function(address, studyId) {
        importStudy.result(address, studyId).then(function(data) {
            console.log("done");
            for(k in studyList[address]){
                studyList[address][k].localStudyId = db.queryValue("SELECT id FROM serverstudy WHERE address = '" + address + "' AND serverstudyid = " + studyList[address][k].id);
            }
            $route.reload();
        });
    }
    $scope.addServer = function() {
        var check = db.queryValue("SELECT address FROM server WHERE address = '" + $scope.address + "'");
        if (check != $scope.address) {
            // check to make sure the form is completely valid
            getServer.result($scope.address).then(function(data) {
                $scope.servers = data;
                $route.reload();
            });
        } else {
            $('#status').html('server already exists');
        }
    };
    $scope.deleteStudy = function(id){
    	var interviews = db.queryObjects("SELECT id FROM interview WHERE completed = -1 AND studyId = " + id).data.length;
    	if(interviews == 0){
        	if(confirm("Are you sure?  This will remove all incomplete interviews.")){
        		console.log("Deleting study " + id);
        		var serverStudy = db.queryRow("SELECT * FROM serverStudy where id = " + id);
        		console.log(serverStudy);
        		var server = db.queryRowObject("SELECT * FROM server WHERE address = '" + serverStudy[1] + "'");
        		var rowdel = db.queryRow("SELECT * FROM study WHERE id = " + id);
        		db.catalog.getTable("serverstudy").deleteRow(serverStudy);
        		db.catalog.getTable("study").deleteRow(rowdel);
                for(k in studyList[serverStudy[1]]){
                    console.log(studyList[serverStudy[1]][k].id +":"+ serverStudy[2]);
                    if(studyList[serverStudy[1]][k].id == serverStudy[2])
                        studyList[serverStudy[1]][k].localStudyId = null;
                }
                console.log(studyList);
        		var questions = db.query("SELECT * FROM question WHERE studyId = " + id).data;
        		for(q in questions){
        			db.catalog.getTable("question").deleteRow(questions[q]);
        		}
        		var options = db.query("SELECT * FROM questionOption WHERE studyId = " + id).data;
        		for(r in options){
        			db.catalog.getTable("questionOption").deleteRow(options[r]);
        		}
        		var expressions = db.query("SELECT * FROM expression WHERE studyId = " + id).data;
        		for(t in expressions){
        			db.catalog.getTable("expression").deleteRow(expressions[t]);
        		}
        		var alterList = db.query("SELECT * FROM alterList WHERE studyId = " + id).data;
        		for(t in alterList){
        			db.catalog.getTable("alterList").deleteRow(alterList[t]);
        		}
        		var alterPrompt = db.query("SELECT * FROM alterPrompt WHERE studyId = " + id).data;
        		for(t in alterPrompt){
        			db.catalog.getTable("alterPrompt").deleteRow(alterPrompt[t]);
        		}
        		db.commit();
        		deleteInterviews(id, true);
                $route.reload();
    		}
    	}else{
    		alert("you must upload completed survey data before you can delete");
    	}
    }
    $scope.servers = db.queryObjects("SELECT * FROM server").data;
    console.log($scope.servers);
}]);

baseUrl = "";
document.addEventListener("deviceready", onDeviceReady, false);
// PhoneGap is loaded and it is now safe to make calls PhoneGap methods

function onDeviceReady() {
}

$(function(){
	setTimeout(function(){
        db.catalog.setPersistenceScope(db.SCOPE_LOCAL);
		tableNames = new Array();
		for(i=0; i<db.catalog.getAllTables().length; i++){
			tableNames.push(db.catalog.getAllTables()[i].tableName);
		}

		console.log(tableNames);

		var server = {
			tableName: "SERVER",
			columns:[
				"id",
				"address",
			],
			primaryKey: [ "address" ],
		};

		if($.inArray('SERVER', tableNames) == -1)
			db.catalog.createTable(server);

		var serverstudy = {
			tableName: "SERVERSTUDY",
			columns:[
				"id",
				"address",
				"serverStudyId",
			],
			primaryKey: [ "id" ],
		};

		if($.inArray('SERVERSTUDY', tableNames) == -1)
			db.catalog.createTable(serverstudy);

		db.commit();

	}, 500);
});

function save(questions, page, url, scope){
    var post = node.objectify($('#answerForm'));
    if(typeof questions[0] == "undefined"){
        for(k in post.ANSWER){
            answer = post.ANSWER[k];

            console.log(post);
    		if(answer.QUESTIONTYPE == "ALTER")
    			var array_id = answer.QUESTIONID + "-" + answer.ALTERID1;
    		else if(answer.QUESTIONTYPE == "ALTER_PAIR")
    			var array_id = answer.QUESTIONID + "-" + answer.ALTERID1 + "and" + answer.ALTERID2;
    		else
    			var array_id = answer.QUESTIONID;
            console.log($("#Answer_" + array_id + "_VALUE").val());

            answer.VALUE = $("#Answer_" + array_id + "_VALUE").val();
            if(typeof interviewId == "undefined" && answer.QUESTIONTYPE == "EGO_ID" && answer.VALUE != ""){
                console.log("new interview");
                interviewId = db.queryValue("SELECT id FROM interview ORDER BY id DESC");
    			if(!interviewId)
    				interviewId = 0;
    			interviewId = parseInt(interviewId) + 1;
    			interview = [
    				interviewId,
    				1,
    				study.ID,
    				0,
    				Math.round(Date.now()/1000),
    				''
    			]
                db.catalog.getTable('interview').insertRow(interview);
            }
            answer.INTERVIEWID = interviewId;
    		if(!answer.ID){
        		answers[array_id] = {
                    ID : '',
                	ACTIVE : '',
                	QUESTIONID : answer.QUESTIONID,
                	INTERVIEWID : answer.INTERVIEWID,
                	ALTERID1 : answer.ALTERID1,
                	ALTERID2 : answer.ALTERID2,
                	VALUE : answer.VALUE,
                	OTHERSPECIFYTEXT : answer.OTHERSPECIFYTEXT,
                	SKIPREASON : answer.SKIPREASON,
                	STUDYID : answer.STUDYID,
                	QUESTIONTYPE : answer.QUESTIONTYPE,
                	ANSWERTYPE : answer.ANSWERTYPE
                };
    	    	var newId = parseInt(db.queryValue("SELECT id FROM answer ORDER BY id DESC"));
    	    	console.log(newId);
    	    	if(!newId)
    	    	    newId = 0;
    	    	answers[array_id].ID = newId + 1;
    	    	answers[array_id].ACTIVE = "";
    	    	console.log(answers[array_id]);
    			db.catalog.getTable('answer').insertRow(objToArray(answers[array_id]));
    		}else{
    			db.catalog.getTable('answer').updateRow(objToArray(answers[array_id]));
    		}
        }
        var completed = parseInt(page) + 1;
    	if(parseInt(db.queryValue("SELECT completed FROM interview WHERE id = " + interviewId)) != -1){
    		interview = db.queryRow("SELECT * FROM interview WHERE id = " + interviewId);
    		interview = [
    			interviewId,
    			1,
    			study.ID,
    			completed,
    			interview[4],
    			interview[5]
    		]
    		db.catalog.getTable('interview').updateRow(interview);
    	}
    	db.commit();
	}
	if(typeof post.CONCLUSION != "undefined" && post.CONCLUSION == 1){
		interview = db.queryRow("SELECT * FROM interview WHERE id = " + interviewId);
		interview = [
			interviewId,
			1,
			study.ID,
			-1,
			interview[4],
			Math.round(Date.now()/1000)
		]
        db.catalog.getTable('interview').updateRow(interview);
		db.commit();

	}
	if(typeof questions[0] != "undefined" && questions[0].ANSWERTYPE == "CONCLUSION"){
		document.location = url + "/";
	}else{
        document.location = url + "/page/" + (parseInt(page) + 1);
    }
}

function saveSkip(interviewId, questionId, alterId1, alterId2, arrayId)
{
    if(typeof answers[arrayId] != "undefined" && answers[arrayId].VALUE == study.VALUELOGICALSKIP)
        return;
	answers[arrayId] = {
        ID : '',
    	ACTIVE : '',
    	QUESTIONID : questionId,
    	INTERVIEWID : interviewId,
    	ALTERID1 : alterId1,
    	ALTERID2 : alterId2,
    	VALUE : study.VALUELOGICALSKIP,
    	OTHERSPECIFYTEXT : "",
    	SKIPREASON : "NONE",
    	STUDYID : study.ID,
    	QUESTIONTYPE : questions[questionId].SUBJECTTYPE,
    	ANSWERTYPE : questions[questionId].ANSWERTYPE
    };
	if(typeof answers[arrayId] == "undefined"){
    	var newId = parseInt(db.queryValue("SELECT id FROM answer ORDER BY id DESC"));
    	if(!newId)
    	    newId = 0;
    	answers[arrayId].ID = newId + 1;
    	answers[arrayId].ACTIVE = "";
		db.catalog.getTable('answer').insertRow(objToArray(answers[arrayId]));
	}else{
		db.catalog.getTable('answer').updateRow(objToArray(answers[arrayId]));
	}
}

function saveNodes()
{
	var nodes = {};
	for(var k in s.graph.nodes()){
		nodes[s.graph.nodes()[k].id] = s.graph.nodes()[k];
	}
	$("#Graph_nodes").val(JSON.stringify(nodes));
    var post = node.objectify($('#graph-form'));
    if(typeof graphs[expressionId] == "undefined"){
        graphs[expressionId] = post.GRAPH;
    	var newId = parseInt(db.queryValue("SELECT id FROM graphs ORDER BY id DESC"));
    	if(!newId)
    	    newId = 0;
    	graphs[expressionId].ID = newId + 1;
        console.log(graphs);

        db.catalog.getTable('graphs').insertRow(objToArray(graphs[expressionId]));
    }else{
        graphs[expressionId] = post.GRAPH;
        db.catalog.getTable('graphs').updateRow(objToArray(graphs[expressionId]));
    }
    db.commit();
}

function getEgoIdValue(interviewId){
	var studyId = db.queryValue("SELECT studyID FROM interview WHERE id = " + interviewId);
	var egoIdQs = db.queryObjects("SELECT * FROM question WHERE studyId = " + studyId + " AND subjectType = 'EGO_ID' ORDER BY ORDERING").data;
    console.log(studyId);
    console.log(egoIdQs);
	var egoId = "";
	for(var k in egoIdQs){
		if(egoId)
			egoId = egoId + "_";
        if(egoIdQs[k].ANSWERTYPE == "MULTIPLE_SELECTION")
            egoId = egoId + db.queryValue("SELECT name FROM questionOption where id = (SELECT a.value FROM answer a WHERE a.questionId = " + egoIdQs[k].ID + " AND a.interviewId = " + interviewId + ")");
        else
            egoId = egoId + db.queryValue("SELECT value FROM answer WHERE questionId = " + egoIdQs[k].ID + " AND interviewId = " + interviewId);
	}
	return egoId;
}

function getInterviewIds(intId){
	var egoValue = db.queryValue("SELECT VALUE FROM answer WHERE CONCAT(',', interviewId, ',') LIKE '%," + intId + ",%' AND questionID = " + study.MULTISESSIONEGOID);
	var column = db.queryObjects("SELECT ID FROM question WHERE title = (SELECT q.title FROM question q WHERE q.ID = " + study.MULTISESSIONEGOID + ")").data;
	var multiIds = [];
	for (var k in column){
		multiIds.push(column[k].ID)
	}
	var column = db.queryObjects("SELECT INTERVIEWID FROM answer WHERE questionId in (" + multiIds.join(",") + ") AND value = '"  + egoValue + "'" ).data;
	var interviewIds = [];
	for (var k in column){
		interviewIds.push(column[k].INTERVIEWID)
	}
	return interviewIds;
}

function displayAudioLoad() {
    $('#status').html("Importing audio files: " + loadedAudioFiles + " / " +totalAudioFiles);
    if (loadedAudioFiles ==totalAudioFiles) {
        $('#status').html("Done!");
        setTimeout(function() {
            //$('#status').html("");
        }, 1000);
    }
}


string = {};
string.repeat = function(string, count)
{
	return new Array(count+1).join(string);
}

string.count = function(string)
{
	var count = 0;

	for (var i=1; i<arguments.length; i++)
	{
		var results = string.match(new RegExp(arguments[i], 'g'));
		count += results ? results.length : 0;
	}

	return count;
}

array = {};
array.merge = function(arr1, arr2)
{
	for (var i in arr2)
	{
		if (arr1[i] && typeof arr1[i] == 'object' && typeof arr2[i] == 'object')
			arr1[i] = array.merge(arr1[i], arr2[i]);
		else
			arr1[i] = arr2[i]
	}

	return arr1;
}

array.print = function(obj)
{
	var arr = [];
	$.each(obj, function(key, val) {
		var next = key + ": ";
		next += $.isPlainObject(val) ? array.print(val) : val;
		arr.push( next );
	  });

	return "{ " +  arr.join(", ") + " }";
}

node = {};

node.objectify = function(node, params)
{
	if (!params)
		params = {};

	if (!params.selector)
		params.selector = "*";

	if (!params.key)
		params.key = "name";

	if (!params.value)
		params.value = "value";

	var o = {};
	var indexes = {};

	$(node).find(params.selector+"["+params.key+"]").each(function()
	{
		var name = $(this).attr(params.key).toUpperCase(),
			value = $(this).attr(params.value);
        if(typeof $(this).attr(params.value) == "undefined")
            return;
		var obj = $.parseJSON("{"+name.replace(/([^\[]*)/, function()
		{
			return '"'+arguments[1]+'"';
		}).replace(/\[(.*?)\]/gi, function()
		{
			if (arguments[1].length == 0)
			{
				var index = arguments[3].substring(0, arguments[2]);
				indexes[index] = indexes[index] !== undefined ? indexes[index]+1 : 0;

				return ':{"'+indexes[index]+'"';
			}
			else
				return ':{"'+escape(arguments[1])+'"';
		})+':"'+value.replace(/[\\"]/gi, function()
		{
			return "\\"+arguments[0];
		})+'"'+string.repeat('}', string.count(name, ']'))+"}");

		o = array.merge(o, obj);
	});

	return o;
}

function objToArray(obj){
	var arr = [];
	for(k in obj){
		arr.push(obj[k]);
	}
	return arr;
}

justUploaded = [];

function createSurveyJSON(studyId){
	data = new Object;
	serverStudyId = db.queryValue("SELECT serverstudyid FROM serverstudy WHERE id = " + studyId);
	data['study'] = db.queryRowObject("SELECT * FROM study WHERE id = " + studyId);
	if(data['study']){
		data['study'].ID = serverStudyId;
		var questions = db.queryObjects("SELECT * FROM question WHERE studyId = " + studyId).data;
		for(a in questions){
			questions[a].STUDYID = serverStudyId;
		}
		data['questions'] = questions;
		var options = db.queryObjects("SELECT * FROM questionOption WHERE studyId = " + studyId).data;
		for(b in options){
			options[b].STUDYID = serverStudyId;
		}
		data['questionOptions'] = options;
		var expressions = db.queryObjects("SELECT * FROM expression WHERE studyId = " + studyId).data;
		for(c in expressions){
			expressions[c].STUDYID = serverStudyId;
		}
		data['expressions'] = expressions;
		console.log(data['expressions']);
		data['alters'] = [];
		data['answers'] = [];
		var interviews = db.queryObjects("SELECT * FROM interview WHERE completed = -1 AND studyId = " + studyId).data;
		console.log(interviews);
		for(k in interviews){
			var alters = db.queryObjects("SELECT * FROM alters WHERE interviewId = " + interviews[k].ID).data;
			for (g in alters){
				data['alters'].push(alters[g]);
			}
			var answers = db.queryObjects("SELECT * FROM answer WHERE interviewId = " + interviews[k].ID).data;
			for(f in answers){
				answers[f].STUDYID = serverStudyId;
				data['answers'].push(answers[f]);
			}
			interviews[k].STUDYID = serverStudyId;
		}
		console.log(data['alters']);
		data['interviews'] = interviews;
		console.log(data['interviews']);

	}
	return JSON.stringify(data);
}

function deleteInterviews(id, all){
	if(typeof all != "undefined")
		all = "";
	else
		all = "completed = -1 AND";
	var interviews = db.query("SELECT * FROM interview WHERE " + all + " studyId = " + id).data;
	console.log(interviews);
	for(u in interviews){
	    var alters = db.query("SELECT * FROM alters WHERE interviewId = " + interviews[u][0]).data;
	    for(v in alters){
	    	db.catalog.getTable("alters").deleteRow(alters[v]);
	    }
		var answers = db.query("SELECT * FROM answer WHERE interviewId = " + interviews[u][0]).data;
		for(w in answers){
	    	db.catalog.getTable("answer").deleteRow(answers[w]);
		}
	    db.catalog.getTable("interview").deleteRow(interviews[u]);
	}
	db.commit();
}
