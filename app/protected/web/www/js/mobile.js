/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
var mobileApp = {
    // Application Constructor
    initialize: function() {
        this.bindEvents();
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicitly call 'app.receivedEvent(...);'
    onDeviceReady: function() {
        mobileApp.receivedEvent('deviceready');
    },
    // Update DOM on a Received Event
    receivedEvent: function(id) {
        var parentElement = document.getElementById(id);
        var listeningElement = parentElement.querySelector('.listening');
        var receivedElement = parentElement.querySelector('.received');

        listeningElement.setAttribute('style', 'display:none;');
        receivedElement.setAttribute('style', 'display:block;');

        console.log('Received Event: ' + id);
    }
};

mobileApp.initialize();

db = false;
baseUrl = "";
isGuest = 0;
studyMaxId = 0;
loadedAudioFiles = 0;
totalAudioFiles = 0;
servers = {};
studies = {};
tables = ["study", "question", "questionOption", "expression", "answer", "alters", "interview", "alterList", "alterPrompt", "notes", "graphs"];
questions = {};
ego_id_questions = {};
name_gen_questions = {};
questionTitles = {};
egoIdQs = [];
multiIdQs = [];
egoAnswers = [];
egoOptions = [];
interviews = [];
multiIds = {};
isMobile = true;

var checkPlugin = setInterval(function(){ loadPlugin() }, 100);

function loadPlugin() {
    if(typeof window.cordova != "undefined"){
        if(typeof sqlitePlugin != "undefined"){
          if(window.cordova.platformId == "android"){
            db = openDatabase('egoweb', '1.0', 'egoweb database', 5 * 1024 * 1024);
          }else{
            db = sqlitePlugin.openDatabase({name: 'egoweb.db', location:"default"});
          }
          clearInterval(checkPlugin);
          loadDb();
        }else{
            console.log("plugin still needs to load");
        }
    }else{
        db = openDatabase('egoweb', '1.0', 'egoweb database', 5 * 1024 * 1024);
        clearInterval(checkPlugin);
        loadDb();
    }
}

function fillQs(arr, value) {

    var O = arr.slice(0);
    var len = parseInt( O.length, 10 );
    var start = arguments[1];
    var relativeStart = parseInt( start, 10 ) || 0;
    var k = relativeStart < 0
            ? Math.max( len + relativeStart, 0)
            : Math.min( relativeStart, len );
    var end = arguments[2];
    var relativeEnd = end === undefined
                      ? len
                      : ( parseInt( end)  || 0) ;
    var final = relativeEnd < 0
                ? Math.max( len + relativeEnd, 0 )
                : Math.min( relativeEnd, len );

    for (; k < final; k++) {
        O[k] = value;
    }
    return O;
}

function loadDb() {
    db.transaction(function (txn) {
        txn.executeSql('CREATE TABLE IF NOT EXISTS server (ID INTEGER PRIMARY KEY, ADDRESS);', [], function(tx, res) {
            console.log("server database created");
            txn.executeSql('SELECT * FROM server', [], function(tx, res) {
                for(i = 0; i < res.rows.length; i++){
                    servers[res.rows.item(i).ID] = res.rows.item(i).ADDRESS;
                }
                console.log("server list loaded:");
                console.log(servers);
            });
        });
    });
        db.transaction(function (txn) {
            txn.executeSql('SELECT * FROM study', [], function(tx, res) {
                for(i = 0; i < res.rows.length; i++){
                    studies[res.rows.item(i).ID] = res.rows.item(i);
                }
                console.log("Study list loaded:");
                console.log(studies);
            });
            txn.executeSql("SELECT * FROM question WHERE subjectType = 'EGO_ID' ORDER BY ORDERING",  [], function(tx,res){
                egoIdQs = [];
                for(i = 0; i < res.rows.length; i++){
                    if(typeof egoIdQs[res.rows.item(i).STUDYID] == "undefined")
                        egoIdQs[res.rows.item(i).STUDYID] = [];
                    if(studies[res.rows.item(i).STUDYID].MULTISESSIONEGOID > 0){
                        if(res.rows.item(i).USEALTERLISTFIELD)
                          multiIdQs[res.rows.item(i).STUDYID] = res.rows.item(i);
                    }
                    egoIdQs[res.rows.item(i).STUDYID].push(res.rows.item(i));
                }
                multiIds = {};
                for(k in multiIdQs){
                    if(typeof multiIds[multiIdQs[k].TITLE] == "undefined")
                        multiIds[multiIdQs[k].TITLE] = [];
                    multiIds[multiIdQs[k].TITLE].push(multiIdQs[k].STUDYID);
                }
            });
            txn.executeSql("SELECT QUESTIONID, INTERVIEWID, VALUE FROM answer WHERE questionType = 'EGO_ID'",  [], function(tx,res){
                for(i = 0; i < res.rows.length; i++){
                    if(typeof egoAnswers[res.rows.item(i).INTERVIEWID] == "undefined")
                        egoAnswers[res.rows.item(i).INTERVIEWID] = [];
                    egoAnswers[res.rows.item(i).INTERVIEWID][res.rows.item(i).QUESTIONID] = res.rows.item(i).VALUE;
                }
            });
            txn.executeSql("SELECT ID, NAME FROM questionOption",  [], function(tx,res){
                for(i = 0; i < res.rows.length; i++){
                    egoOptions[res.rows.item(i).ID] = res.rows.item(i).NAME;
                }
            });
            txn.executeSql('SELECT * FROM interview',  [], function(tx,res){
                interviews = [];
                for(i = 0; i < res.rows.length; i++){
                    if(typeof interviews[res.rows.item(i).STUDYID] == "undefined")
                        interviews[res.rows.item(i).STUDYID] = [];
                    interviews[res.rows.item(i).STUDYID].push(res.rows.item(i));
                }
            });
        }, null, function(txn){
            /*
            for(k in studies){
                if(typeof interviews[studies[k].ID] == "undefined")
                    continue;
                for(i = 0; i < interviews[studies[k].ID].length; i++){
                    interviews[studies[k].ID][i].egoValue = "";
                    for(j in egoIdQs[interviews[studies[k].ID][i].STUDYID]){
                        if(interviews[studies[k].ID][i].egoValue)
                            interviews[studies[k].ID][i].egoValue = interviews[studies[k].ID][i].egoValue + "_";
                        if(egoIdQs[interviews[studies[k].ID][i].STUDYID][j].ANSWERTYPE == "MULTIPLE_SELECTION")
                            interviews[studies[k].ID][i].egoValue = interviews[studies[k].ID][i].egoValue + egoOptions[egoAnswers[interviews[studies[k].ID][i].ID][egoIdQs[interviews[studies[k].ID][i].STUDYID][j].ID]];
                        else
                            interviews[studies[k].ID][i].egoValue = interviews[studies[k].ID][i].egoValue + egoAnswers[interviews[studies[k].ID][i].ID][egoIdQs[interviews[studies[k].ID][i].STUDYID][j].ID];
                    }
                }
            }
            */
        });
}

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
        var url = servers[id] + '/mobile/getstudies';
        if (!url.match('http') && !url.match('https')) url = "http://" + url;
        return $.ajax({
            url: url,
            type: 'POST',
            data: $("#serverForm_" + id).serialize(),
            crossDomain: true,
            success: function(data) {
                if (data != "error" && data != "failed") {
                    return data;
                    $('#addServerButton').show();
                } else {
                    displayAlert("Validation failed", "alert-danger");
                    return data;
                }
            },
            error: function(data) {
                displayAlert(data, "alert-danger");
            }
        });
    }
    return {
        result: result
    }
});

app.factory("getServer", function($http, $q) {
    console.log("getServer");
    var result = function(address) {
        var url = address + '/mobile/check';
        if (!url.match('http') && !url.match('https'))
            url = "http://" + url;
        return $.ajax({
            url: url,
            type: 'GET',
            crossDomain: true,
            success: function(data) {
                if (data == "success") {
                    displayAlert('Connected to server', "alert-success");
                } else {
                    displayAlert('Mo response from server', "alert-danger");
                }
            },
            error: function(data) {
                displayAlert("Can't connect to server", "alert-danger");
            }
        });
    }
    return {
        result: result
    }
});

app.factory("importStudy", function($http, $q) {
    console.log("importStudy starting");
    var result = function(address, studyId) {
        displayAlert("Importing study...", "alert-warning")
        var url = address + '/mobile/ajaxdata/' + studyId;
        if (!url.match('http') && !url.match('https'))
            url = "http://" + url;
        return $.ajax({
            url: url,
            type: "GET",
            timeout: 30000,
            crossDomain: true,
            success: function(data) {
                data = JSON.parse(data);
                return data;
            }
        });
    }
    return {
        result: result
    }
});

app.factory("saveAlter", function($http, $q) {
    var getAlters = function(){
    var defer = $.Deferred();
    var name = $("#Alters_name").val();
    for(k in alters){
        if(alters[k].NAME == name){
            var oldAlter = alters[k];
            alters[k].NAMEGENQIDS = alters[k].NAMEGENQIDS + "," + $("#Alters_nameGenQIds").val();
        }
    }
    if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
        for(k in prevAlters){
            if(prevAlters[k].NAME == name){
                alters[k] = $.extend(true,{}, prevAlters[k]);
                var oldAlter = alters[k];
                alters[k].INTERVIEWID = alters[k].INTERVIEWID + "," + interviewId;
                alters[k].NAMEGENQIDS = alters[k].NAMEGENQIDS + "," + $("#Alters_nameGenQIds").val();
            }
        }
    }
    if(typeof oldAlter != "undefined" && oldAlter){
        newAlter = {INTERVIEWID: oldAlter.INTERVIEWID, NAMEGENQIDS: oldAlter.NAMEGENQIDS, ID: oldAlter.ID};
        var alterSQL = "UPDATE alters SET INTERVIEWID = ?, NAMEGENQIDS = ? WHERE id = ?";
    }else{
        newAlter = {
            ID: null,
            ACTIVE:1,
            ORDERING: Object.keys(alters).length,
            NAME: name,
            INTERVIEWID: interviewId.toString(),
            ALTERLISTID: '',
            NAMEGENQIDS: $("#Alters_nameGenQIds").val()
        };
        console.log(newAlter);
        var alterSQL = 'INSERT INTO alters VALUES (' +  fillQs(objToArray(newAlter),"?").join(",") + ')';
    }
    db.transaction(function (txn) {
        txn.executeSql(alterSQL, objToArray(newAlter), function(tx, res){
            console.log("made new alter");
            if(typeof oldAlter == "undefined"){
                newAlter.ID = res.insertId;
                alters[newAlter.ID] = newAlter;
                console.log(alters);
            }
        });
    },
    function(txn){
        console.log(txn);
    },
    function(txn){
        console.log("alter saved");
        defer.resolve(JSON.stringify(alters));
    });
    return defer.promise();
    }
    return {
        getAlters: getAlters
    }
});

app.factory("deleteAlter", function($http, $q) {
    var getAlters = function() {
        var defer = $.Deferred();
        var id = $("#deleteAlterId").val();
        var nameQId = $("#deleteNameGenQId").val();
        var nameQIds = Object.keys(name_gen_questions);
    	if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
    		var interviewIds = alters[id].INTERVIEWID.toString().split(",");
            if(alters[id].NAMEGENQIDS.toString().match(","))
                var nameGenQIds = alters[id].NAMEGENQIDS.toString().split(",");
            else
                var nameGenQIds = [alters[id].NAMEGENQIDS.toString()];
            var checkRemain = false;
            for(i = 0; i < nameGenQIds.length; i++){
                if(nameGenQIds[i] != nameQId && $.inArray(nameGenQIds[i], nameQIds) != -1){
                    checkRemain = true;
                }
            }
            $(nameGenQIds).each(function(index){
    			if(nameGenQIds[index] == nameQId)
    				nameGenQIds.splice(index,1);
    		});
    		alters[id].NAMEGENQIDS = nameGenQIds.join(",");
            if(checkRemain == false){
                $(interviewIds).each(function(index){
        			if(interviewIds[index] == interviewId)
        				interviewIds.splice(index,1);
        		});
        		alters[id].INTERVIEWID = interviewIds.join(",");
            }
    		alterSQL = "UPDATE alters SET INTERVIEWID = ?, NAMEGENQIDS = ? WHERE ID = ?";
            insert =  $.extend(true,{},  alters[id]);
            deleteAlter = [insert.INTERVIEWID, insert.NAMEGENQIDS, id];
            if(checkRemain == false){
                delete alters[id];
            }
    	}else{
            if(alters[id].NAMEGENQIDS.toString().match(",")){
                var nameGenQIds = alters[id].NAMEGENQIDS.toString().split(",");
                $(nameGenQIds).each(function(index){
                    if(nameGenQIds[index] == nameQId)
                        nameGenQIds.splice(index,1);
                });
                alters[id].NAMEGENQIDS = nameGenQIds.join(",");
                alterSQL = "UPDATE alters SET NAMEGENQIDS = ? WHERE ID = ?";
                deleteAlter = [alters[id].NAMEGENQIDS, id];
            }else{
                delete alters[id];
                alterSQL = "DELETE FROM alters WHERE ID = ?";
                deleteAlter = [id];
            }
    	}
        db.transaction(function (txn) {
            txn.executeSql(alterSQL, deleteAlter, function(tx, res){
                console.log(tx);
            });
        },
        function(txn){
            console.log(txn);
        },
        function(txn){
        	return defer.resolve(JSON.stringify(alters));
        });
        return defer.promise();
    }
    return {
        getAlters : getAlters
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
    $scope.studies = studies;
    $scope.interviews = interviews;
    $scope.done = [];
    for(k in studies){
        if(typeof $scope.done[studies[k].ID] == "undefined")
            $scope.done[studies[k].ID] = [];
        for(j in interviews[studies[k].ID]){
            interviews[studies[k].ID][j].egoValue = "";
            for(l in egoIdQs[interviews[studies[k].ID][j].STUDYID]){
                if(typeof egoAnswers[interviews[studies[k].ID][j].ID] == "undefined")
                    continue;
                if(interviews[studies[k].ID][j].egoValue)
                    interviews[studies[k].ID][j].egoValue = interviews[studies[k].ID][j].egoValue + "_";
                if(egoIdQs[interviews[studies[k].ID][j].STUDYID][l].ANSWERTYPE == "MULTIPLE_SELECTION")
                    interviews[studies[k].ID][j].egoValue = interviews[studies[k].ID][j].egoValue + egoOptions[egoAnswers[interviews[studies[k].ID][j].ID][egoIdQs[interviews[studies[k].ID][j].STUDYID][l].ID]];
                else
                    interviews[studies[k].ID][j].egoValue = interviews[studies[k].ID][j].egoValue + egoAnswers[interviews[studies[k].ID][j].ID][egoIdQs[interviews[studies[k].ID][j].STUDYID][l].ID];
            }
            if(interviews[studies[k].ID][j].COMPLETED == -1)
                $scope.done[studies[k].ID].push(interviews[studies[k].ID][j].ID);
        }
    }
    justUploaded = [];
    $scope.startSurvey = function(studyId, intId) {
	    study = studies[studyId];
        studyNames = [];
        questionList = [];
        masterList = [];
        name_gen_questions = [];
        ego_id_questions = [];
        questions = {};
        if(typeof multiIdQs[studyId] != "undefined"){
            multiStudyIds = multiIds[multiIdQs[studyId].TITLE];
        }else{
            multiStudyIds = [studyId];
        }
        for(k in studies){
            if($.inArray(studies[k].ID, multiStudyIds) != -1)
                studyNames[studies[k].ID] = studies[k].NAME;
        }
        db.readTransaction(function (txn) {
            txn.executeSql('SELECT * FROM question WHERE studyId IN (' + multiStudyIds.join(",") + ") ORDER BY ORDERING",  [], function(tx,res){
                console.log(res.rows);
                for(i = 0; i < res.rows.length; i++){
                    questions[parseInt(res.rows.item(i).ID)] = res.rows.item(i);
                    if(res.rows.item(i).STUDYID == study.ID){
                        if(res.rows.item(i).SUBJECTTYPE == "EGO_ID")
                            ego_id_questions[parseInt(res.rows.item(i).ID)] = res.rows.item(i);
                        if(res.rows.item(i).STUDYID == study.ID && res.rows.item(i).SUBJECTTYPE == "NAME_GENERATOR")
                            name_gen_questions[parseInt(res.rows.item(i).ID)] = res.rows.item(i);
                        questionList.push(res.rows.item(i));
                    }
                    if(typeof questionTitles[studyNames[res.rows.item(i).STUDYID]] == "undefined")
                        questionTitles[studyNames[res.rows.item(i).STUDYID]] = {};
                    questionTitles[studyNames[res.rows.item(i).STUDYID]][res.rows.item(i).TITLE] = res.rows.item(i).ID;
                };
                console.log("questions loaded...");
            }, function(tx, error){
                console.log(tx);
                console.log(error);
            });
            txn.executeSql('SELECT * FROM questionOption WHERE studyId  IN (' + multiStudyIds.join(",") + ") ORDER BY ORDERING", [], function(tx,res){
                options = [];
                for(i = 0; i < res.rows.length; i++){
                	if(typeof options[res.rows.item(i).QUESTIONID] == "undefined")
                    	options[res.rows.item(i).QUESTIONID] = [];
                	options[res.rows.item(i).QUESTIONID][res.rows.item(i).ORDERING] = res.rows.item(i);
            	}
                console.log("options loaded...");
            }, function(tx, error){
                console.log(tx);
                console.log(error);
            });
            txn.executeSql('SELECT * FROM expression WHERE studyId IN (' + multiStudyIds.join(",") + ")", [], function(tx,res){
                expressions = [];
                for(i = 0; i < res.rows.length; i++){
                    expressions[res.rows.item(i).ID] = res.rows.item(i);
            	}
                console.log("expressions loaded...");
            }, function(tx, error){
                console.log(tx);
                console.log(error);
            });
        },
        function(txn){
            console.log(txn);
        },
        function(txn){
            console.log("study loaded...");
            csrf = "";
            answers = {};
            audio = [];
    		    alters = {};
            prevAlters = {};
            graphs = {};
            allNotes = {};
            otherGraphs = {};
            alterPrompts = [];
            participantList = [];
        	if(typeof intId == "undefined"){
            	interviewId = undefined;
            	interview = false;
                var page = 0;
                var url = $location.absUrl().replace($location.url(),'');
                $("#studyTitle").html(study.NAME);
                document.location = url + "/page/" + parseInt(page);
        	}else{
        		interviewId = intId;
                db.readTransaction(function (txn) {
                    txn.executeSql("SELECT * FROM interview WHERE id = " + interviewId,  [], function(tx,res){
                        interview = res.rows.item(0);
                        page = interview.COMPLETED;
                        if(page == -1)
                            page = 0;
                    });
                    txn.executeSql("SELECT * FROM graphs WHERE interviewId = " + interviewId,  [], function(tx,res){
                      console.log(res);
                        for(i = 0; i < res.rows.length; i++){
                            graphs[res.rows.item(i).EXPRESSIONID] = res.rows.item(i);
                        }
                    });
                    txn.executeSql("SELECT * FROM notes WHERE interviewId = " + interviewId,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            if(typeof allNotes[res.rows.item(i).EXPRESSIONID] == "undefined")
                                allNotes[res.rows.item(i).EXPRESSIONID] = {};
                            allNotes[res.rows.item(i).EXPRESSIONID][res.rows.item(i).ALTERID] = res.rows.item(i).NOTES;
                        }
                    });
                    if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
                        interviewIds = getInterviewIds(interviewId, study.ID);
                        var aSQL = "SELECT * FROM answer WHERE interviewId in (" + interviewIds.join(",") + ")";
                    }else{
                        interviewIds = [interviewId];
                        var aSQL = "SELECT * FROM answer WHERE interviewId = " + interviewId;
                    }
                    txn.executeSql(aSQL,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            if(res.rows.item(i).QUESTIONTYPE == "ALTER")
                                array_id = res.rows.item(i).QUESTIONID + "-" + res.rows.item(i).ALTERID1;
                            else if(res.rows.item(i).QUESTIONTYPE == "ALTER_PAIR")
                                array_id = res.rows.item(i).QUESTIONID + "-" + res.rows.item(i).ALTERID1 + "and" + res.rows.item(i).ALTERID2;
                            else
                                array_id = res.rows.item(i).QUESTIONID;
                            answers[array_id] = res.rows.item(i);
                        }
                    });
                    for(k = 0; k < interviewIds.length; k++){
                        interviewIds[k] = interviewIds[k].toString();
                        txn.executeSql("SELECT * FROM alters WHERE interviewId = ? OR interviewId LIKE ? OR interviewId LIKE ? OR interviewId LIKE ?",  [interviewIds[k], "%," + interviewIds[k], interviewIds[k] + ",%", "%," + interviewIds[k] + ",%"], function(tx,res){
                            for(i = 0; i < res.rows.length; i++){
                                var alter = $.extend(true,{}, res.rows.item(i));
                                alterIntIds = alter.INTERVIEWID.toString().split(",");
                                console.log("alter name:");
                                console.log(alter);
                                console.log(alterIntIds);
                                if(multiStudyIds.length > 1 && $.inArray(interviewId.toString(), alterIntIds) == -1)
                                    prevAlters[res.rows.item(i).ID] = res.rows.item(i);
                                else
                                    alters[res.rows.item(i).ID] = res.rows.item(i);
                            }
                        });
                    }
                    txn.executeSql("SELECT * FROM alterList WHERE studyId = " + study.ID,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            console.log("participant from list", res.rows.item(i));
                            participantList.push(res.rows.item(i));
                        }
                    });
                    txn.executeSql("SELECT * FROM alterPrompt WHERE studyId = " + study.ID,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                          console.log(res.rows.item(i));
                          if(typeof alterPrompts[res.rows.item(i).QUESTIONID] == "undefined")
                            alterPrompts[res.rows.item(i).QUESTIONID] = [];
                          alterPrompts[res.rows.item(i).QUESTIONID][res.rows.item(i).AFTERALTERSENTERED] = res.rows.item(i).DISPLAY;
                        }
                    });
                }, function(txn){console.log(txn)}, function(txn){
                    console.log("load interview copmlete...")
                    var url = $location.absUrl().replace($location.url(),'');
                    $("#studyTitle").html(study.NAME);
                    document.location = url + "/page/" + parseInt(page);
                });
        	}
        });
    }
    $scope.deleteInterview = function(intId) {
        console.log("Deleting interview: " + intId);
        deleteInterview(intId);
        $route.reload();
    }
    $scope.upload = function(studyId){
        displayAlert('Uploading inerviews...', "alert-warning");
    	$("#uploader-" + studyId).prop('disabled', true);
    	var serverAddress = servers[studies[studyId].SERVER];
        var serverId = studies[studyId].SERVER;
    	var url = serverAddress + "/mobile/uploadData";
        if (!url.match('http') && !url.match('https'))
            url = "http://" + url;
        db.readTransaction(function (txn) {
            data = new Object;
            data['study'] = $.extend(true,{}, studies[studyId]);
            data['study'].ID = data['study'].SERVERSTUDYID;
            data['alters'] = [];
            data['answers'] = [];
            data['questions'] = [];
            data['questionOptions'] = [];
            data['expressions'] = [];
            data['interviews'] = [];
            data['graphs'] = [];
            data['notes'] = [];
            for(k in interviews[studyId]){
                if(interviews[studyId][k].COMPLETED == -1){
                    txn.executeSql("SELECT * FROM alters WHERE interviewId = ? OR interviewId LIKE ? OR interviewId LIKE ? OR interviewId LIKE ?",  [interviews[studyId][k].ID.toString(), "%," + interviews[studyId][k].ID.toString(), interviews[studyId][k].ID.toString() + ",%", "%," + interviews[studyId][k].ID.toString() + ",%"], function(tx,res){
                    //txn.executeSql("SELECT * FROM alters WHERE interviewId = " + interviews[studyId][k].ID,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            data['alters'].push(res.rows.item(i));
                        }
                        console.log("alters:",data['alters']);
                    });
                    txn.executeSql("SELECT * FROM answer WHERE interviewId = " + interviews[studyId][k].ID,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            var answer = res.rows.item(i);
                            answer.STUDYID = data['study'].ID;
                            data['answers'].push(answer);
                        }
                    });
                    txn.executeSql("SELECT * FROM notes WHERE interviewId = " + interviews[studyId][k].ID,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            var note = res.rows.item(i);
                            data['notes'].push(note);
                        }
                    });
                    txn.executeSql("SELECT * FROM graphs WHERE interviewId = " + interviews[studyId][k].ID,  [], function(tx,res){
                        for(i = 0; i < res.rows.length; i++){
                            var graph = res.rows.item(i);
                            data['graphs'].push(graph);
                        }
                    });
                    var interview = $.extend(true,{}, interviews[studyId][k]);
                    interview.STUDYID = data['study'].ID;
                    interview.ACTIVE = 2;
                    data['interviews'].push(interview);
                }
            }
            txn.executeSql('SELECT * FROM question WHERE studyId = ' + studyId + " ORDER BY ORDERING",  [], function(tx,res){
                console.log(res.rows);
                for(i = 0; i < res.rows.length; i++){
                    var question = res.rows.item(i);
                    question.STUDYID = data['study'].ID;
                    data['questions'].push(question);
                };
            });
            txn.executeSql('SELECT * FROM questionOption WHERE studyId = ' + studyId + " ORDER BY ORDERING", [], function(tx,res){
                for(i = 0; i < res.rows.length; i++){
                    var optiion = res.rows.item(i);
                    optiion.STUDYID = data['study'].ID;
                    data['questionOptions'].push(optiion);
        	    }
            });
            txn.executeSql('SELECT * FROM expression WHERE studyId = ' + studyId, [], function(tx,res){
                expressions = [];
                for(i = 0; i < res.rows.length; i++){
                    var expression = res.rows.item(i);
                    expression.STUDYID = data['study'].ID;
                    data['expressions'].push(expression);
            	}
            });
        },
        function(txn){
            console.log(txn);
        },
        function(txn){
          $('#data').val(JSON.stringify(data));
        	console.log($('#data').val());
        	$.ajax({
        		type:'POST',
        		url:url,
                crossDomain: true,
        		data:$('#hiddenForm').serialize(),
        		success:function(data){
        			if(data.match("Upload completed.  No Errors Found")){
        				//justUploaded.push(studyId);
        				//deleteInterviews(studyId);
                        db.transaction(function (txn) {
                            txn.executeSql('UPDATE interview SET ACTIVE = 2 WHERE COMPLETED = -1 AND STUDYID = ?', [studyId], function(tx, res){
                                displayAlert('Successfully uploaded data', "alert-success");
                                for(k in interviews[studyId]){
                                    if(interviews[studyId][k].COMPLETED == -1)
                                        interviews[studyId][k].ACTIVE = 2;
                                }
                                setTimeout(function() {
                                    $route.reload();
                                }, 2000);
                            });
                        });
        			}
        		},
        		error:function(xhr, ajaxOptions, thrownError){
        			displayAlert('Error: ' + xhr.status, "alert-danger");
        			$("#uploader-" + studyId).prop('disabled', false);
        		}
        	});
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
    $scope.servers = [];
    for(k in servers){
        $scope.servers.push({id:parseInt(k), address:servers[k]})
    }
    $scope.connect = function(id) {
        getStudies.result(id).then(function(data) {
            if(data == "error" || data == "failed")
                return;
            studyList[servers[id]] = JSON.parse(data);
            var names = [];
            var ids = [];
            var serverStudyids = [];
            for(k in studies){
                names.push(studies[k].NAME);
                serverStudyids.push(studies[k].SERVERSTUDYID);
                ids.push(studies[k].ID);
            }
            for(k in studyList[servers[id]]){
                var index = $.inArray(studyList[servers[id]][k].name, names);
                if(index != -1 && serverStudyids[index] == studyList[servers[id]][k].id){
                    studyList[servers[id]][k].localStudyId = ids[index];
                }
            }
            console.log(studyList);
            $route.reload();
        });
    }
    $scope.importStudy = function(serverId, studyId) {
        address = servers[serverId];
        importStudy.result(address, studyId).then(function(data) {
            console.log("importStudy:  " + address + " : " + studyId);
            data = JSON.parse(data);
            console.log(data['columns']);
            console.log(address);
            db.transaction(function (txn) {
                for(i = 0; i < tables.length; i++){
                    if(typeof data['columns'][tables[i]] == "undefined")
                        continue;
                    if($.inArray(tables[i], ["study", "interview", "answer", "alters", "graphs", "notes"]) != -1){
                        data['columns'][tables[i]][0] = "ID INTEGER PRIMARY KEY";
                    }
                    if(tables[i] == "study"){
                        if($.inArray("SERVER", data['columns'][tables[i]]) == -1)
                            data['columns'][tables[i]].push("SERVER");
                        data['columns'][tables[i]].push("SERVERSTUDYID");
                    }
                    txn.executeSql('CREATE TABLE IF NOT EXISTS ' + tables[i] + '(' + data['columns'][tables[i]].join(",") + ')', []);
                }
            },
            function(txn){
                console.log(txn);
            },
            function(txn){
              console.log(txn);
                db.transaction(function (txn) {
                    data.study = objToArray(data.study);
                    data.study[28] = data.study[0];
                    data.study[0] = null;
                    data.study[27] = serverId;
                    console.log(txn);
                    var fillQStr = fillQs(data.study,"?").join(",").toString();
                    console.log('INSERT INTO study VALUES (' +  fillQStr + ')');
                    console.log(data.study);
                    txn.executeSql('INSERT INTO study VALUES (' +  fillQStr + ')', data.study, function(tx, res){
                        newId = res.insertId;
                        console.log("made new study " + newId);
                    });
                },
                function(txn){
                    console.log(txn);
                },
                function(txn){
                    console.log("study created...");
                    for (k in data.questions) {
                        data.questions[k] = objToArray(data.questions[k]);
                    }
                    for (k in data.options) {
                        data.options[k] = objToArray(data.options[k]);
                    }
                    for (k in data.expressions) {
                        data.expressions[k] = objToArray(data.expressions[k]);
                    }
                    for (k in data.alterList) {
                        data.alterList[k] = objToArray(data.alterList[k]);
                    }
                    for (k in data.alterPrompts) {
                        data.alterPrompts[k] = objToArray(data.alterPrompts[k]);
                    }
                db.transaction(function (txn) {
                    txn.executeSql('SELECT * FROM study WHERE ID = ' + newId, [], function(tx, res){
                        studies[newId] = res.rows.item(0);
                    });
                    for (k in data.questions) {
                        data.questions[k][34] = newId;
                        data.questions[k][0] = parseInt(data.questions[k][0]);
                        data.questions[k][9] = parseInt(data.questions[k][9]);
                        data.questions[k][20] = parseInt(data.questions[k][20]);
                        data.questions[k][23] = parseInt(data.questions[k][23]);
                        txn.executeSql('INSERT INTO question VALUES (' + fillQs(data.questions[k], "?").join(",") + ')',  data.questions[k], function(){console.log("questions imported...");}, function(tx, res){console.log(tx);console.log(res);});
                    }
                    console.log("questions imported...");
                    for (k in data.options) {
                        console.log(data.options[k]);
                        data.options[k][0] = parseInt(data.options[k][0]);
                        data.options[k][6] = parseInt(data.options[k][6]);
                        data.options[k][2] = newId;
                        txn.executeSql('INSERT INTO questionOption VALUES (' + fillQs(data.options[k], "?").join(",") + ')',  data.options[k]);
                    }
                    console.log("options imported...");
                    for (k in data.expressions) {
                        data.expressions[k][0] = parseInt(data.expressions[k][0]);
                        data.expressions[k][7] = newId;
                        txn.executeSql('INSERT INTO expression VALUES (' + fillQs(data.expressions[k], "?").join(",") + ')',  data.expressions[k]);
                    }
                    console.log("expressions imported...");
                    for (k in data.alterList) {
                        data.alterList[k][0] = parseInt(data.alterList[k][0]);
                        data.alterList[k][1] = newId;
                        txn.executeSql('INSERT INTO alterList VALUES (' + fillQs(data.alterList[k],"?").join(",") + ')',  data.alterList[k]);
                    }
                    console.log("alterList imported...");
                    for (k in data.alterPrompts) {
                        data.alterPrompts[k][0] = parseInt(data.alterPrompts[k][0]);
                        data.alterPrompts[k][1] = newId;
                        console.log(data.alterPrompts[k]);
                        txn.executeSql('INSERT INTO alterPrompt VALUES (' + fillQs(data.alterPrompts[k],"?").join(",") + ')', data.alterPrompts[k]);
                    }
                    console.log("alter prompts imported...");
                },
                function(txn){
                    console.log(txn);
                },
                function(txn){
                    console.log("done importing...");
                    for(k in studyList[address]){
                        if(studies[newId].NAME == studyList[address][k].name){
                            studyList[address][k].localStudyId = newId;
                            console.log("server id match:" + newId)
                        }
                    }
                    loadDb();
                    $route.reload();
                });
                });
            });
            if(typeof data.audioFiles != "undefined"){
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
            }
        });
    }
    $scope.addServer = function() {
        console.log("addServer: " + $scope.address);
        check = false;
        db.transaction(function (txn) {
            txn.executeSql("SELECT address FROM server WHERE address = '" + $scope.address + "'", [], function(tx, res){
                if(res.rows.length == 0)
                    check = true;
                if (check == true) {
                    // check to make sure the form is completely valid
                    getServer.result($scope.address).then(function(data) {
                        if(data == "success"){
                            console.log("connected to server");
                            db.transaction(function (txn) {
                                txn.executeSql('INSERT INTO server VALUES (?,?)', [null, $scope.address], function(tx, res){
                                    newId = res.insertId;
                                    servers[newId] = $scope.address;
                                    console.log(tx);
                                    console.log(res.insertId);
                                    console.log('insert into database OK');
                                    displayAlert('Successfully added server', "alert-success");
                                    $route.reload();
                                });
                            });
                        }
                    });
                } else {
                    displayAlert('Server already exists', "alert-danger")
                }
            });
        });
    }
    $scope.editServer = function(serverId) {
        console.log("editServer: " + servers[serverId].address);
        db.transaction(function (txn) {
            address = $("#Server_" + serverId).val();
            getServer.result(address).then(function(data) {
                if(data == "success"){
                    console.log("connected to server");
                    db.transaction(function (txn) {
                        console.log("changed server address: " + serverId);
                        txn.executeSql('UPDATE server SET address = ? WHERE id = ?', [serverId, address], function(tx, res){
                            servers[serverId] = address;
                            console.log('updated database OK');
                            displayAlert('Successfully edited server address', "alert-success");
                            $route.reload();
                        });
                    });
                }
            });
        });
    }
    $scope.showForm = function(serverId) {
        $("#editServerForm_" + serverId).show();
        $("#editButton_" + serverId).hide();
    }
    $scope.deleteStudy = function(id){
    	if(typeof interviews[id] == "undefined" || interviews[id].length == 0){
        	if(confirm("Are you sure?  This will remove all interviews as well")){
        		console.log("Deleting study " + id);
                serverStudy = studies[id];
        		console.log(serverStudy);
                db.transaction(function (txn) {
                    txn.executeSql('DELETE FROM study WHERE ID = ' + id, [], function(tx, res) {});
                    txn.executeSql('DELETE FROM question WHERE STUDYID = ' + id, [], function(tx, res) {});
                    txn.executeSql('DELETE FROM questionOption WHERE STUDYID = ' + id, [], function(tx, res) {});
                    txn.executeSql('DELETE FROM expression WHERE STUDYID = ' + id, [], function(tx, res) {});
                    txn.executeSql('DELETE FROM alterList WHERE STUDYID = ' + id, [], function(tx, res) {});
                    txn.executeSql('DELETE FROM alterPrompt WHERE STUDYID = ' + id, [], function(tx, res) {});
                }, function(txn){}, function(txn){
                    for(k in studyList[servers[serverStudy.SERVER]]){
                        console.log(serverStudy.SERVERSTUDYID);
                        console.log(studyList[servers[serverStudy.SERVER]][k]);
                        if(studyList[servers[serverStudy.SERVER]][k].id == serverStudy.SERVERSTUDYID){
                            delete studyList[servers[serverStudy.SERVER]][k].localStudyId;
                            delete studies[serverStudy.ID];
                        }
                    }
                    $route.reload();
                });
    		}
    	}else{
    		alert("you must upload completed survey data before you can delete");
    	}
    }

}]);

function save(questions, page, url, scope){
    console.log("saving..")
    var post = node.objectify($('#answerForm'));
    if(typeof interviewId == "undefined"){
        var interview = [
            null,
            1,
            study.ID,
            0,
            Math.round(Date.now()/1000),
            ''
        ];
        var intSQL = 'INSERT INTO interview VALUES (' + fillQs(interview,"?").join(",") + ')';
    }else{
        var completed = parseInt(page) + 1;
        var interview = [completed, interviewId];
        var intSQL = 'UPDATE interview SET COMPLETED = ? WHERE ID = ?';
    }
    db.transaction(function (txn){
        txn.executeSql(intSQL, interview, function(tx, res){
            if(typeof interviewId == "undefined"){
                interviewId = res.insertId;
                if(typeof interviews[study.ID] == "undefined")
                    interviews[study.ID] = [];
                interviews[study.ID].push({
                    ID:interviewId,
                    ACTIVE: 1,
                    STUDYID: study.ID,
                    COMPLETED:0,
                    START_DATE:Math.round(Date.now()/1000),
                    COMPLETED_DATE:''
                });
                if(study.FILLALTERLIST == true){
                    db.transaction(function (txn){
                        for(k in participantList){
                            var newAlter = {
                                ID: null,
                                ACTIVE:1,
                                ORDERING: k,
                                NAME: participantList[k].NAME,
                                INTERVIEWID: interviewId,
                                ALTERLISTID: ''
                            };
                            var insert = objToArray(newAlter);
                            txn.executeSql('INSERT INTO interview VALUES (' + fillQs(insert, "?").join(",") + ')', insert, function(tx, res){
                                alters[res.insertId] = {
                                    ID: res.insertId,
                                    ACTIVE:1,
                                    ORDERING: k,
                                    NAME: participantList[k].NAME,
                                    INTERVIEWID: interviewId,
                                    ALTERLISTID: ''
                                };
                            });
                        }
                    });
                }
                console.log("created new interview: " + interviewId);
            }else{
                console.log("continue interview: " + interviewId);
            }
        });
    },function(txn){console.log(txn)}, function(txn){
      console.log("saving answer")
        db.transaction(function (txn){
            if(typeof questions[0] == "undefined"){
                for(k in post.ANSWER){
                    answer = post.ANSWER[k];
                    console.log(answer)
                    if(answer.QUESTIONTYPE == "ALTER")
                        var array_id = answer.QUESTIONID + "-" + answer.ALTERID1;
                    else if(answer.QUESTIONTYPE == "ALTER_PAIR")
                        var array_id = answer.QUESTIONID + "-" + answer.ALTERID1 + "and" + answer.ALTERID2;
                    else
                        var array_id = answer.QUESTIONID;
                    answer.VALUE = $("#Answer_" + array_id + "_VALUE").val();
                    console.log("answer value:" + answer.VALUE);
                    if(answer.QUESTIONTYPE == "EGO_ID"){
                        console.log("inserting ego answers");
                        if(typeof egoAnswers[interviewId] == "undefined")
                            egoAnswers[interviewId] = [];
                        egoAnswers[interviewId][answer.QUESTIONID] = answer.VALUE;
                        console.log(egoAnswers);
                    }
                    answer.INTERVIEWID = interviewId;
                    if(!answer.ID){
                        answers[array_id] = {
                            ID : null,
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
                        var insert = objToArray(answers[array_id]);
                        txn.executeSql('INSERT INTO answer VALUES (' + fillQs(insert,"?").join(",") + ')', insert, function(tx, res){
                            answers[array_id].ID = res.insertId;
                        }, function(tx, error){
                            console.log(tx);
                            console.log(error);
                        });
                    }else{
                      answers[array_id].VALUE = answer.VALUE;
                      answers[array_id].SKIPREASON = answer.SKIPREASON;
                      answers[array_id].OTHERSPECIFYTEXT = answer.OTHERSPECIFYTEXT;
                      console.log("updating answer")
                        txn.executeSql('UPDATE answer SET VALUE = ?, SKIPREASON = ?, OTHERSPECIFYTExT = ? WHERE ID = ?', [answers[array_id].VALUE, answers[array_id].SKIPREASON, answers[array_id].OTHERSPECIFYTEXT, answers[array_id].ID], function(tx, res){
                            console.log("answer " + array_id + " updated to " + answers[array_id].VALUE);
                        });
                    }
                }
            }
            if(typeof post.CONCLUSION != "undefined" && post.CONCLUSION == 1){
                txn.executeSql('UPDATE interview SET COMPLETED = ?, COMPLETE_DATE = ? WHERE ID = ?', [-1, Math.round(Date.now()/1000), interviewId], function(tx, res){
                    for(k in interviews[study.ID]){
                        if(interviews[study.ID][k].ID == interviewId){
                            interviews[study.ID][k].COMPLETED = -1;
                            interviews[study.ID][k].COMPLETE_DATE = Math.round(Date.now()/1000);
                        }
                    }
                    console.log("interview " + interviewId + " completed");
                });
            }
        }, null, function(txn){
            console.log("going to next page");
            if(typeof s != "undefined" && typeof s.isForceAtlas2Running != "undefined" && s.isForceAtlas2Running()){
                s.stopForceAtlas2();
                saveNodes();
            }
            evalQuestions();
          //  if(typeof questions[0] != "undefined" && questions[0].ANSWERTYPE == "NAME_GENERATOR")
            //    buildList();
        	if(typeof questions[0] != "undefined" && questions[0].ANSWERTYPE == "CONCLUSION"){
        		document.location = url + "/";
        	}else{
                document.location = url + "/page/" + (parseInt(page) + 1);
            }
        });
    });
}

function saveSkip(interviewId, questionId, alterId1, alterId2, arrayId)
{
    if(typeof answers[arrayId] != "undefined" && answers[arrayId].VALUE == study.VALUELOGICALSKIP)
        return;
    var array_id = "";
    array_id = questionId;
    if(alterId1)
        array_id = array_id + "-" + alterId1;
    if(alterId2)
        array_id = array_id + "and" + alterId2;
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
    var insert = objToArray(answers[array_id]);
    db.transaction(function (txn) {
        txn.executeSql('INSERT INTO answer VALUES (' + fillQs(insert,"?").join(",") + ')', insert, function(tx, res){
        });
    });
}

function getNote(node){
  $("#modalBody").val("");
  $("#modalHeader").html(alters[parseInt(node.id)].NAME);
  if(typeof notes[parseInt(node.id)] != "undefined"){
    $("#modalBody").val(notes[parseInt(node.id)]);
  }
  $("#alterId").val(parseInt(node.id));
  $("#myModal").modal();
}

function saveNote(){
    var newNote = {
      ID: null,
      INTERVIEWID: interviewId,
      EXPRESSIONID: parseInt(graphExpressionId),
      ALTERID: parseInt($("#alterId").val()),
      NOTES: $("#modalBody").val()
    };
    if(typeof notes[parseInt($("#alterId").val())] == "undefined"){
      var noteSQL = 'INSERT INTO notes VALUES (' +  fillQs(objToArray(newNote),"?").join(",") + ')';
      var insert = objToArray(newNote);
      var node = s.graph.nodes(parseInt($("#alterId").val()));
      node.label = node.label + " �";
      console.log("made new note");
    }else{
      var noteSQL = 'UPDATE notes SET NOTES = ? WHERE INTERVIEWID = ? AND EXPRESSIONID = ? AND ALTERID = ?';
      var insert = [$("#modalBody").val(), interviewId, graphExpressionId, parseInt($("#alterId").val())];
      console.log("update note");
    }
db.transaction(function (txn) {
    txn.executeSql(noteSQL, insert, function(tx, res){
        $('#myModal').modal('hide');
        notes[parseInt($("#alterId").val())] = $("#modalBody").val();
         $("#modalBody").val("");
         $("#alterId").val("");
    });
  });
}

function deleteNote(){
  db.transaction(function (txn) {
    txn.executeSql('DELETE FROM notes WHERE INTERVIEWID = ' + interviewId + ' AND ALTERID = ' + parseInt($("#alterId").val()) + " AND EXPRESSIONID = " + graphExpressionId, [], function(tx, res) {
      console.log(interviewId, parseInt($("#alterId").val()), graphExpressionId, res);
      delete notes[parseInt($("#alterId").val())];
      var node = s.graph.nodes(parseInt($("#alterId").val()));
      node.label = node.label.replace(" �","");
      $('#myModal').modal('hide');
    });
  });
}
function saveNodes() {
	var nodes = {};
  if(typeof s != "undefined" && typeof s.isForceAtlas2Running != "undefined" && s.isForceAtlas2Running()){
      s.stopForceAtlas2();
  }
	for(var k in s.graph.nodes()){
		nodes[s.graph.nodes()[k].id] = s.graph.nodes()[k];
	}
	$("#Graph_nodes").val(JSON.stringify(nodes));
  console.log($("#Graph_nodes").val());
    var post = node.objectify($('#graph-form'));
    if(typeof graphs[graphExpressionId] == "undefined"){
      post.GRAPH.ID = null;
      post.GRAPH.EXPRESSIONID = parseInt(post.GRAPH.EXPRESSIONID);
      post.GRAPH.INTERVIEWID = parseInt(post.GRAPH.INTERVIEWID);
      var insert = objToArray(post.GRAPH);
      console.log(insert);
        var graphSQL = 'INSERT INTO graphs VALUES (' + fillQs(insert,"?").join(",") + ')';
    }else{
        var graphSQL = "UPDATE graphs SET NODES = ?, PARAMS = ? WHERE ID = ?";
        var insert = [ post.GRAPH.NODES,  post.GRAPH.PARAMS,  post.GRAPH.ID];
    }
    db.transaction(function (txn) {
        txn.executeSql(graphSQL, insert, function(tx, res){
          console.log("saved graph");
          graphs[graphExpressionId] = post.GRAPH;
            if(res.insertId)
                graphs[graphExpressionId].ID = res.insertId;
        })
    });

}

function getInterviewIds(intId, studyId){
    var multiStudyIds = multiIds[multiIdQs[studyId].TITLE];
    var multiKey = egoAnswers[intId][multiIdQs[studyId].ID];
    var interviewIds = [];
    console.log("getting interview IDs...");
    console.log(multiStudyIds);
    console.log(multiKey);
    for(i = 0; i < multiStudyIds.length; i++){
        for(k in interviews[multiStudyIds[i]]){
          if(egoAnswers[interviews[multiStudyIds[i]][k].ID] == undefined)
            continue;
            if(multiKey == egoAnswers[interviews[multiStudyIds[i]][k].ID][multiIdQs[interviews[multiStudyIds[i]][k].STUDYID].ID])
                interviewIds.push(interviews[multiStudyIds[i]][k].ID);
        }
    }
	return interviewIds;
}

function displayAudioLoad() {
    window.scrollTo(0, 0);
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

function deleteInterview(intId){
    db.transaction(function (txn) {
        txn.executeSql('DELETE FROM interview WHERE ID = ' + intId, [], function(tx, res) {
        });
        txn.executeSql('DELETE FROM alters WHERE INTERVIEWID = ?', [intId.toString()], function(tx, res) {
        }, function(tx, res) {
          console.log(res);
        });
        txn.executeSql('DELETE FROM answer WHERE INTERVIEWID = ' + intId, [], function(tx, res) {
        });
        txn.executeSql('DELETE FROM notes WHERE INTERVIEWID = ' + intId, [], function(tx, res) {
        });
        txn.executeSql('DELETE FROM graphs WHERE INTERVIEWID = ' + intId, [], function(tx, res) {
        });
        for(k in interviews){
            for(i = 0; i < interviews[k].length; i++){
                if(interviews[k][i].ID == intId){
                    interviews[k].splice(i, 1);
                    return;
                }
            }
        }
    });
}

function displayAlert(message, type){
    $("#status").removeClass("alert-danger");
    $("#status").removeClass("alert-success");
    $("#status").removeClass("alert-warning");
    $("#status").addClass(type);
    $("#status").html(message);
    $("#status").show();
    console.log(message);
}

function testSQL(sql){
  sql = sql;
  db.readTransaction(function (txn) {
      txn.executeSql(sql,  [], function(tx,res){
          return res;
      });
  });
}
