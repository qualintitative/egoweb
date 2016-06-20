var app = angular.module('egowebApp', ['ngRoute', 'autocomplete']);

app.config(function ($routeProvider) {

    $routeProvider

    .when('/page/:page', {
        templateUrl: baseUrl + 'interview.html',
        controller: 'interviewController'
    })

});

app.controller('interviewController', ['$scope', '$log', '$routeParams','$sce', '$location', '$route', "saveAlter", "deleteAlter", function($scope, $log, $routeParams, $sce, $location, $route, saveAlter, deleteAlter) {
    $scope.questions = buildQuestions($routeParams.page, interviewId);
    $scope.page = $routeParams.page;
    $scope.study = study;
    $scope.csrf = csrf;
    $scope.interviewId = interviewId;
    $scope.answers = answers;
    $scope.options = new Object;
    $scope.alters = alters;
    $scope.prevAlters = prevAlters;
    $scope.alterPrompt = "";
    $scope.askingStyleList = false;
    $scope.hideQ = false;
    $scope.subjectType = false;
    $scope.answerType = false;
    $scope.qId = "";
    $scope.prompt = "";
    $scope.alterName = "";
    $scope.dates = new Object;
    $scope.time_spans = new Object;
    $scope.graphId = "";
    $scope.graphExpressionId = "";
    $scope.graphInterviewId = "";
    $scope.graphNodes = "";
    $scope.graphParams = "";
    $scope.otherSpecify = {};
    $scope.otherGraphs = {};
    $scope.audioFiles = {};
    $scope.audio = {};
    $scope.keys = Object.keys;
    $scope.interview = interview;
    $scope.footer = $sce.trustAsHtml(study.FOOTER);
    console.clear();

    for(k in audio){
        $scope.audio[k] = audio[k];
        $scope.audioFiles[k] = new Audio();
        $scope.audioFiles[k].src = audio[k];
    }

    $scope.nav = buildNav($scope.page);

    $('#navbox ul').html("");
    for(k in $scope.nav){
        if(baseUrl == "/www/")
    	    $('#navbox ul').append("<li id='menu_" + k + "'><a href='/interview/" + study.ID + (interviewId ? "/" + interviewId  : "") + "#page/" + k + "'>" + $scope.nav[k] + "</a></li>");
        else
    	    $('#navbox ul').append("<li id='menu_" + k + "']]><a href='" + $location.absUrl().replace($location.url(),'') + "page/" + k + "'>" + $scope.nav[k] + "</a></li>");
    }
    $("#second").show();
    $("#second").scrollTop($("#second").scrollTop() - $("#second").offset().top + $("#menu_" + $scope.page).offset().top);
    $("#questionMenu").removeClass("hidden");

    for(var k in $scope.questions){
        var array_id = $scope.questions[k].array_id;
        if($scope.questions[k].USEALTERLISTFIELD == "name" || $scope.questions[k].USEALTERLISTFIELD == "email"){
            $scope.participants = participantList[$scope.questions[k].USEALTERLISTFIELD];
        }
        if(typeof $scope.questions[k].CITATION == "string")
            $scope.questions[k].CITATION = $sce.trustAsHtml($scope.questions[k].CITATION);

        if($scope.questions[k].ALLBUTTON == true && !$scope.options["all"]){
            $scope.options['all'] = $.extend(true,{}, options[$scope.questions[k].ID]);
            if($scope.questions[k].DONTKNOWBUTTON == true){
                var button = new Object;
                button.NAME = "Don't Know";
                button.ID = "DONT_KNOW";
                button.checked = false;
                $scope.options['all'][Object.keys($scope.options['all']).length] = button;
            }

            if($scope.questions[k].REFUSEBUTTON == true){
                var button = new Object;
                button.NAME = "Refuse";
                button.ID = "REFUSE";
                button.checked = false;
                $scope.options['all'][Object.keys($scope.options['all']).length] = button;
            }
        }
        $scope.options[array_id] = $.extend(true,{}, options[$scope.questions[k].ID]);

        if($scope.questions[k].ASKINGSTYLELIST == true)
            $scope.askingStyleList = $scope.questions[k].array_id;
        if($scope.askingStyleList != false)
            $scope.fixedWidth = "120px";
        else
            $scope.fixedWidth = "auto";

        if($scope.subjectType == false){
            $scope.subjectType = $scope.questions[k].SUBJECTTYPE;
            $scope.answerType = $scope.questions[k].ANSWERTYPE;;
            if(typeof $scope.questions[k].ID != "undefined")
                $scope.qId = $scope.questions[k].ID;
        }

        if($scope.questions[k].ANSWERTYPE == "PREFACE" || $scope.questions[k].ANSWERTYPE == "ALTER_PROMPT"){
            $scope.hideQ = true;
            if(study.USEASALTERS == true){
                $scope.participants = participantList['name'];
            }
        }

        if($scope.questions[k].ANSWERTYPE == "ALTER_PROMPT"){
            if(typeof alterPrompts[Object.keys(alters).length] != "undefined")
                $scope.alterPrompt = alterPrompts[Object.keys(alters).length];
        }
        for(o in $scope.options[array_id]){
            $scope.options[array_id][o].checked = false;
            if(typeof answers[array_id] != "undefined"){
                var values = answers[array_id].VALUE.split(',');
                if(values.indexOf($scope.options[array_id][o].ID.toString()) != -1)
                    $scope.options[array_id][o].checked = true;
            }
        }

        if(typeof $scope.answers[array_id] == "undefined"){
            $scope.answers[array_id] = new Object;
            $scope.answers[array_id].VALUE = "";
            $scope.answers[array_id].INTERVIEWID = interviewId;
            $scope.answers[array_id].SKIPREASON = "NONE";
        }else{
            if($scope.answers[array_id].VALUE == "-4")
                $scope.answers[array_id].VALUE = "";
        }

        if($scope.questions[k].ANSWERTYPE == "TIME_SPAN"){
            $scope.time_spans[array_id] = new Object;
			if(answers[array_id].VALUE.match(/(\d*)\sYEARS/))
                $scope.time_spans[array_id].YEARS = answers[array_id].VALUE.match(/(\d*)\sYEARS/)[1];
			if(answers[array_id].VALUE.match(/(\d*)\sMONTHS/))
                $scope.time_spans[array_id].MONTHS = answers[array_id].VALUE.match(/(\d*)\sMONTHS/)[1];
			if(answers[array_id].VALUE.match(/(\d*)\sWEEKS/))
                $scope.time_spans[array_id].WEEKS = answers[array_id].VALUE.match(/(\d*)\sWEEKS/)[1];
			if(answers[array_id].VALUE.match(/(\d*)\sDAYS/))
                $scope.time_spans[array_id].DAYS = answers[array_id].VALUE.match(/(\d*)\sDAYS/)[1];
			if(answers[array_id].VALUE.match(/(\d*)\sHOURS/))
                $scope.time_spans[array_id].HOURS = answers[array_id].VALUE.match(/(\d*)\sHOURS/)[1];
			if(answers[array_id].VALUE.match(/(\d*)\sMINUTES/))
                $scope.time_spans[array_id].MINUTES = answers[array_id].VALUE.match(/(\d*)\sMINUTES/)[1];
        }

        if($scope.questions[k].ANSWERTYPE == "DATE"){
            $scope.dates[array_id] = new Object;
            var date = answers[array_id].VALUE.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
            var time = answers[array_id].VALUE.match(/(\d{1,2}):(\d{1,2}) (AM|PM)/);
			if(date && date.length > 3){
				$scope.dates[array_id].YEAR = date[3];
				$scope.dates[array_id].MONTH = date[1];
				$scope.dates[array_id].DAY = date[2];
			}
			if(time && time.length > 2){
				$scope.dates[array_id].HOUR = time[1];
				$scope.dates[array_id].MINUTE = time[2];
			}
			if(time && time.length > 3)
				$scope.dates[array_id].AMPM = time[3];
            else
				$scope.dates[array_id].AMPM = "";
        }

        if($scope.questions[k].DONTKNOWBUTTON == true){
            var button = new Object;
            button.NAME = "Don't Know";
            button.ID = "DONT_KNOW";
            button.checked = false;
            if($scope.answers[array_id].SKIPREASON == "DONT_KNOW")
                button.checked = true;
            $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
        }

        if($scope.questions[k].REFUSEBUTTON == true){
            var button = new Object;
            button.NAME = "Refuse";
            button.ID = "REFUSE";
            button.checked = false;
            if($scope.answers[array_id].SKIPREASON == "REFUSE")
                button.checked = true;
            $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
        }

        columns = Object.keys($scope.options[array_id]).length;
        if(typeof $scope.answers[array_id].OTHERSPECIFYTEXT != "undefined" && $scope.answers[array_id].OTHERSPECIFYTEXT != null && $scope.answers[array_id].OTHERSPECIFYTEXT != ""){
            $scope.otherSpecify[array_id] = {};
            var specify = $scope.answers[array_id].OTHERSPECIFYTEXT.split(";;");
            for(s in specify){
                var pair = specify[s].split(":");
                $scope.otherSpecify[array_id][pair[0]] = pair[1];
            }
        }
        for(a in $scope.options[array_id]){
            if(typeof $scope.otherSpecify[array_id] == "undefined")
                $scope.otherSpecify[array_id] = {};
            if($scope.otherSpecify[array_id][$scope.options[array_id][a].ID] && $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] != "")
                continue;
            if($scope.options[array_id][a].OTHERSPECIFY == true)
                $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] = "";
            else if($scope.options[array_id][a].NAME.match(/OTHER \(*SPECIFY\)*/i))
                $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] = "";
            else
                $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] = false;
        }

        if($scope.questions[k].SUBJECTTYPE != "EGO_ID"){
            $scope.prompt = $sce.trustAsHtml(interpretTags($scope.questions[k].PROMPT, $scope.questions[k].ALTERID1, $scope.questions[k].ALTERID2));
        }else{
            $scope.prompt = $sce.trustAsHtml(study.EGOIDPROMPT);
            $scope.questions[k].PROMPT = $scope.questions[k].PROMPT.replace(/(<([^>]+)>)/ig, '');
        }

        if($scope.questions[k].SUBJECTTYPE == "NETWORK"){
            expressionId = $scope.questions[k].NETWORKRELATIONSHIPEXPRID;
            notes = [];
            if(typeof otherGraphs[$scope.questions[k].ID] != "undefined")
                $scope.otherGraphs = otherGraphs[$scope.qId];
            if(typeof graphs[expressionId] != "undefined"){
                $scope.graphId = graphs[expressionId].ID;
                $scope.graphExpressionId = graphs[expressionId].EXPRESSIONID;
                $scope.graphInterviewId = graphs[expressionId].INTERVIEWID;
                $scope.graphNodes = graphs[expressionId].NODES;
                $scope.graphParams = $scope.questions[k].NETWORKPARAMS;
                if(typeof allNotes[expressionId] != "undefined")
                    notes = allNotes[expressionId];
            }else{
                $scope.graphExpressionId = expressionId;
                $scope.graphInterviewId = interviewId;

            }
            initStats($scope.questions[k]);
        }
        setTimeout(
            function(){
                if(typeof $(".answerInput")[0] != "undefined")
                    $(".answerInput")[0].focus();
                $("#second").scrollTop($("#second").scrollTop() - $("#second").offset().top + $("#menu_" + $scope.page).offset().top);
                eval($scope.questions[k].JAVASCRIPT);
            },
        1);
    }

    setTimeout(function(){
        eval(study.JAVASCRIPT);
    },1);

    $scope.errors = new Object;

	$scope.print = function(e_Id, i_Id){
        if(typeof e_Id == "undefined")
            e_Id = expressionId;
        if(typeof i_Id == "undefined")
            i_Id = interviewId;
		url = "/data/visualize?print&expressionId=" + e_Id + "&interviewId=" + i_Id + "&params=" + encodeURIComponent($("#Graph_params").val()) + "&labelThreshold=" + s.renderers[0].settings("labelThreshold");
		window.open(url);
	}

    $scope.playSound = function(file) {
        $scope.audioFiles[file].play();
    }

    $scope.goBack = function() {
        var url = $location.absUrl().replace($location.url(),'');
        document.location = url + "page/" + (parseInt($routeParams.page) - 1);
    }

    $scope.submitForm = function(isValid) {
        // check to make sure the form is completely valid
        if (isValid) {
            save($scope.questions, $routeParams.page, $location.absUrl().replace($location.url(),''));
        }
    };

    $scope.addAlter = function(isValid) {
        $scope.errors[0] = false;
        for(k in alters){
            if($("#Alters_name").val() == alters[k].NAME){
                $scope.errors[0] = 'That name is already listed';
            }
        }
        if($("#Alters_name").val().trim() == ""){
            $scope.errors[0] = 'Name cannot be blank';
        }
        // check to make sure the form is completely valid
        if($scope.errors[0] == false){
            saveAlter.getAlters().then(function(data){
                alters = JSON.parse(data);
                for(k in alters){
                    if(typeof prevAlters[k] != "undefined")
                        delete prevAlters[k];
                }
                $route.reload();
            });
        }
    };

    $scope.removeAlter = function(alterId) {
        $("#deleteAlterId").val(alterId);
        console.log(alterId);
        // check to make sure the form is completely valid
        deleteAlter.getAlters().then(function(data){
            alters = JSON.parse(data);
            $route.reload();
        });
    };

    $scope.unSkip = function (array_id){
        if(typeof $scope.answers[array_id].VALUE != "undefined" && $scope.answers[array_id].VALUE != "" && $scope.answers[array_id].VALUE != "SKIPREASON"){
    		for(k in $scope.options[array_id]){
            		$scope.options[array_id][k].checked = false;
    		}
            $scope.answers[array_id].SKIPREASON = "NONE";
        }
    }

    $scope.changeOther = function (array_id){
        var specify = [];
        for(a in $scope.options[array_id]){
            if($scope.otherSpecify[array_id][$scope.options[array_id][a].ID] != false && $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] != ""){
                specify.push($scope.options[array_id][a].ID + ":" + $scope.otherSpecify[array_id][$scope.options[array_id][a].ID])
            }
        }
        $scope.answers[array_id].OTHERSPECIFYTEXT = specify.join(";;");
        console.log($scope.answers[array_id].OTHERSPECIFYTEXT);
    }

    $scope.multiSelect = function (v, index, array_id){

        if(typeof $scope.questions[array_id] != "undefined")
            var question = $scope.questions[array_id];
        else
            var question = questions[$scope.options[array_id][index].QUESTIONID];
    	if($scope.answers[array_id].VALUE)
    		values = $scope.answers[array_id].VALUE.split(',');
    	else
    		values = [];

        if(v == "DONT_KNOW" || v == "REFUSE"){
            if($scope.options[array_id][index].checked){
        		for(k in $scope.options[array_id]){
            		console.log(k + ":" + index)
            		if(k != index)
                		$scope.options[array_id][k].checked = false;
        		}
        		$scope.answers[array_id].OTHERSPECIFYTEXT = "";
        		$scope.answers[array_id].SKIPREASON = v;
        		if(typeof $scope.dates[array_id] != "undefined"){
            		$scope.dates[array_id].MINUTE = "";
            		$scope.dates[array_id].HOUR = "";
            		$scope.dates[array_id].DAY = "";
            		$scope.dates[array_id].MONTH = "";
            		$scope.dates[array_id].AMPM = "";
            		$scope.dates[array_id].YEAR = "";
        		}
        		if(typeof $scope.errors[array_id] != "undefined")
                    delete $scope.errors[array_id];
                $('#Answer_' + array_id + '_VALUE').val("SKIPREASON").change();
                $('#Answer_' + array_id + '_VALUE').val("").change();

        	}else{
        		$scope.answers[array_id].SKIPREASON = "NONE";
        		$('#Answer_' + array_id + '_VALUE').val("SKIPREASON").change();
                $('#Answer_' + array_id + '_VALUE').val("").change();
        	}
        }else{
            if($scope.options[array_id][index].checked){
        		$scope.answers[array_id].SKIPREASON = "NONE";
        		for(k in $scope.options[array_id]){
            		if($scope.options[array_id][k].ID == "DONT_KNOW" || $scope.options[array_id][k].ID == "REFUSE")
                		$scope.options[array_id][k].checked = false;
        		}
    			if(values.indexOf(v.toString()) == -1)
    				values.push(v.toString());
    		}else{
        		if($scope.otherSpecify[$scope.options[array_id][index].ID] != false){
        		    $scope.otherSpecify[$scope.options[array_id][index].ID] = "";
        		    $scope.changeOther(array_id);
                }
    			if(values.indexOf(v.toString()) != -1){
    				values.splice(values.indexOf(v),1);
                }
    		}
        	if(values.length > question.MAXCHECKABLEBOXES){
        		value = values.shift();
        		for(k in $scope.options[array_id]){
            		if($scope.options[array_id][k].ID == value)
            		    $scope.options[array_id][k].checked = false;
        		}
        	}
        	$scope.answers[array_id].VALUE = values.join(',');
        }

    }

    $scope.setAll = function (v, index){
        for(k in $scope.questions){
            var array_id = $scope.questions[k].array_id;
            if($scope.answers[array_id].VALUE == undefined)
                $scope.answers[array_id].VALUE = "";
            if(
                ($scope.answers[array_id].VALUE == "" && $scope.answers[array_id].SKIPREASON == "NONE" && $scope.options['all'][index].checked == true) ||
                ((($scope.answers[array_id].VALUE != "" && $.inArray(v.toString(), $scope.answers[array_id].VALUE.split(",")) != -1) || ($scope.answers[array_id].SKIPREASON != "" && $.inArray(v.toString(), $scope.answers[array_id].SKIPREASON.split(",")) != -1)) && $scope.options['all'][index].checked == false)

            )
            {
                $scope.options[array_id][index].checked = $scope.options['all'][index].checked;
                $scope.multiSelect(v, index, k);
            }
        }
    }

    $scope.timeValue = function (array_id){
    	var date = "";
    	if(!isNaN($scope.time_spans[array_id].YEARS))
    	    date = $scope.time_spans[array_id].YEARS + ' YEARS ';
    	if(!isNaN($scope.time_spans[array_id].MONTHS))
    		date += $scope.time_spans[array_id].MONTHS + ' MONTHS ';
    	if(!isNaN($scope.time_spans[array_id].WEEKS))
    		date += $scope.time_spans[array_id].WEEKS + ' WEEKS ';
    	if(!isNaN($scope.time_spans[array_id].DAYS))
    		date += $scope.time_spans[array_id].DAYS + ' DAYS ';
    	if(!isNaN($scope.time_spans[array_id].HOURS))
    		date += $scope.time_spans[array_id].HOURS + ' HOURS ';
    	if(!isNaN($scope.time_spans[array_id].MINUTES))
    		date += $scope.time_spans[array_id].MINUTES + ' MINUTES';
    	$scope.answers[array_id].VALUE = date;
		$scope.answers[array_id].SKIPREASON = "NONE";
		for(k in $scope.options[array_id]){
    		if($scope.options[array_id][k].ID == "DONT_KNOW" || $scope.options[array_id][k].ID == "REFUSE")
        		$scope.options[array_id][k].checked = false;
		}
    	console.log(date);
    }

    $scope.dateValue = function (array_id){
    	var date = "";
    	if($scope.dates[array_id].MONTH)
    		date += $scope.dates[array_id].MONTH + ' ';
    	if($scope.dates[array_id].DAY)
    		date += $scope.dates[array_id].DAY + ' ';
    	if(!isNaN($scope.dates[array_id].YEAR))
    	    date += $scope.dates[array_id].YEAR + ' ';
    	if(!isNaN($scope.dates[array_id].HOUR))
    		date += $scope.dates[array_id].HOUR + ':';
    	if(!isNaN($scope.dates[array_id].MINUTE)){
        	if($scope.dates[array_id].MINUTE.toString().length < 2)
        	    $scope.dates[array_id].MINUTE = '0' + $scope.dates[array_id].MINUTE;
    		date += $scope.dates[array_id].MINUTE + ' ';
        }
    	if($scope.dates[array_id].AMPM)
    		date += $scope.dates[array_id].AMPM;
    	$scope.answers[array_id].VALUE = date;
		$scope.answers[array_id].SKIPREASON = "NONE";
		for(k in $scope.options[array_id]){
    		if($scope.options[array_id][k].ID == "DONT_KNOW" || $scope.options[array_id][k].ID == "REFUSE")
        		$scope.options[array_id][k].checked = false;
		}
    	console.log(date);

    }
    
    $scope.timeBits = function(timeUnits, span)
    {
        timeArray = [];
        bitVals = {
        	'BIT_YEAR' :1,
        	'BIT_MONTH' : 2,
        	'BIT_WEEK': 4,
        	'BIT_DAY' :8,
        	'BIT_HOUR' :16,
        	'BIT_MINUTE': 32,
        };
        for (var k in bitVals){
        	if(timeUnits & bitVals[k]){
        		timeArray.push(k);
        	}
        }

        if($.inArray("BIT_" + span, timeArray) != -1)
            return true;
        else
            return false;
    }
}]);

app.directive('checkAnswer', [function (){
   return {
        require: 'ngModel',
        link: function(scope, elem, attr, ngModel) {
          //For DOM . model validation
            ngModel.$parsers.unshift(function(value) {
                var valid = true;
                var array_id = attr.arrayId;
                var question = questions[attr.questionId];

                console.log(question);
                console.log("check:" + value);



		
                if(attr.answerType == "ALTER_PROMPT"){
        			if(Object.keys(alters).length < study.MINALTERS){
        				scope.errors[array_id] = 'Please list ' + study.MINALTERS + ' people';
                    	valid = false;
        			}
			    }

                if(attr.answerType == "TEXTUAL"){
                    if(scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW"){
                        if(value == ""){
                            scope.errors[array_id] = "Value cannot be blank";
                        	valid = false;
                    	}else{
                            delete scope.errors[array_id];
                    	}
                    }else{
                        delete scope.errors[array_id];
                    }
                    console.log(scope.answers[array_id].SKIPREASON +  ":" + value + ":" + valid + ":" + scope.errors[array_id]);
                }

        		if(attr.answerType == "NUMERICAL"){
            		console.log("check numeric");
        			var min = ""; var max = ""; var numberErrors = 0; var showError = false;
        			if((value == "" && scope.answers[array_id].SKIPREASON == "NONE") || (value != "" && isNaN(parseInt(value)))){
                        errorMsg = 'Please enter a number';
        				showError = true;
                    }
        			if(question.MINLIMITTYPE == "NLT_LITERAL"){
        				min = question.MINLITERAL;
        			}else if(question.MINLIMITTYPE == "NLT_PREVQUES"){
        				min = scope.answers[question.MINPREVQUES].VALUE;
        			}
        			if(question.MAXLIMITTYPE == "NLT_LITERAL"){
        				max = question.MAXLITERAL;
        			}else if(question.MAXLIMITTYPE == "NLT_PREVQUES"){
        				max = scope.answers[question.MAXPREVQUES].VALUE;
        			}
        			if(min !== "")
        				numberErrors++;
        			if(max !== "")
        				numberErrors = numberErrors + 2;
        			if(((max !== "" && parseInt(value) > parseInt(max))  ||  (min !== "" && parseInt(value) < parseInt(min))) && scope.answers[array_id].SKIPREASON == "NONE")
        				showError = true;

        			if(numberErrors == 3)
        				errorMsg = "The range of valid answers is " + min + " to " + max + ".";
        			else if (numberErrors == 2)
        				errorMsg = "The range of valid answers is " + max + " or fewer.";
        			else if (numberErrors == 1)
        				errorMsg = "The range of valid answers is " + min + " or greater.";

        			if(showError){
                        scope.errors[array_id] = errorMsg;
                        valid = false;
        			}else{
                        delete scope.errors[array_id];
                    }
        		}

                if(attr.answerType == "DATE"){
                    console.log(attr.answerType);
                    if(scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW"){
            			var date = value.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
            			var time = value.match(/(\d+):(\d+) (AM|PM)/);
            			if(typeof time != "undefined" && time && time.length > 2){
            			    if(parseInt(time[1]) < 1 || parseInt(time[1]) > 12){
                                scope.errors[array_id] = 'Please enter 1 to 12 for HH';
                            	valid = false;
            			    }
            			    console.log(time);
            			    if(parseInt(time[2]) < 0 || parseInt(time[2]) > 59){
            			    	scope.errors[array_id] = 'Please enter 0 to 59 for MM';
            				    valid = false;
            			    }
            			}else{
                			if(scope.timeBits(question.TIMEUNITS, 'HOUR')){
                		    	scope.errors[array_id] = 'Please enter the time of day';
                			    valid = false;
            			    }
            			}
            			if(typeof date != "undefined" && date && date.length > 3){
            			    if(parseInt(date[2]) < 1 || parseInt(date[2]) > 31){
            			    	scope.errors[array_id] = 'Please enter a different number for the day of month';
            					valid = false;
            			    }
            			}
        			}else{
                        delete scope.errors[array_id];
                    }
        		}

        		if(attr.answerType == "MULTIPLE_SELECTION"){
            		var showError = false;
        			min = question.MINCHECKABLEBOXES;
        			max = question.MAXCHECKABLEBOXES;
        			var numberErrors = 0; var showError = false; var errorMsg = "";
        			if(min !== "")
        				numberErrors++;
        			if(max !== "")
        				numberErrors = numberErrors + 2;

        			checkedBoxes = value.split(',').length;
        			if(!value)
        				checkedBoxes = 0;

        			if ((checkedBoxes < min || checkedBoxes > max) && scope.answers[array_id].SKIPREASON == "NONE")
        				showError = true;
        			//console.log('min:' + min + ':max:' + max + ':checked:' + checkedBoxes+ ":answer:" + value + ":showerror:" + showError);

        			adds = '';
        			if(max != 1)
        				adds = 's';
        			if(parseInt(question.ASKINGSTYLELIST) == 1)
        				adds += ' for each row';
        			if(numberErrors == 3 && min == max && showError)
        				errorMsg = "Select " + max  + " response" + adds + " please.";
        			else if(numberErrors == 3 && min != max && showError)
        				errorMsg = "Select " + min + " to " + max + " response" + adds + " please.";
        			else if (numberErrors == 2 && showError)
        				errorMsg = "You may select up to " + max + " response" + adds + " please.";
        			else if (numberErrors == 1 && showError)
        				errorMsg = "You must select at least " + min + " response" + adds + " please.";
        			//if(answer.OTHERSPECIFYTEXT && showError)
        			//	showError = false;

        			if(showError){
                        scope.errors[array_id] = errorMsg;
                        valid = false;
        			}
        		}

    		// check for list range limitations
    		var checks = 0;
    		if(typeof question != "undefined" && parseInt(question.WITHLISTRANGE) != 0){
    			for(i in answers){
    					console.log(answers[i].VALUE + ":" + question.LISTRANGESTRING);
    				if(answers[i].VALUE.split(',').indexOf(question.LISTRANGESTRING) != -1){
    					checks++;
    				}
    			}
                console.log("check list range: " + checks);
    
    			if(checks < question.MINLISTRANGE || checks > question.MAXLISTRANGE){
    				errorMsg = "";
    				if(question.MINLISTRANGE && question.MAXLISTRANGE){
    					if(question.MINLISTRANGE != question.MAXLISTRANGE)
    						errorMsg = question.MINLISTRANGE + " - " + question.MAXLISTRANGE;
    					else
    						errorMsg = "just " + question.MINLISTRANGE;
    				}else if(!question.MINLISTRANGE && !question.MAXLISTRANGE){
    						errorMsg = "up to " + question.MAXLISTRANGE;
    				}else{
    						errorMsg = "at least " + question.MINLISTRANGE;
    				}
    
                    valid = false;
                    scope.errors[array_id] = "Please select "  + errorMsg + " response(s).  You selected " + checks;
    
    			}else{
        			console.log("pass: " + valid);
        			for(k in scope.errors){
            			if(scope.errors[k].match("Please select "))
            			    delete scope.errors[k];
        			}
    			}
    		}
		
                ngModel.$setValidity('checkAnswer', valid);
                return valid ? value : undefined;
            });

          ngModel.$formatters.unshift(function(value) {
                var valid = true;
                var array_id = attr.arrayId;
                var question = questions[attr.questionId];

                if(attr.answerType == "ALTER_PROMPT"){
        			if(Object.keys(alters).length < study.MINALTERS){
        				scope.errors[array_id] = 'Please list ' + study.MINALTERS + ' people';
                    	valid = false;
        			}
			    }

                if(attr.answerType == "TEXTUAL"){
                    if(scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW"){
                        if(value == ""){
                            scope.errors[array_id] = "Value cannot be blank...";
                        	valid = false;
                    	}else{
                            delete scope.errors[array_id];
                    	}
                    }else{
                        delete scope.errors[array_id];
                    }
                    console.log(scope.answers[array_id].SKIPREASON +  ":" + value + ":" + valid);
                }
                if(attr.answerType == "NUMERICAL"){
        			var min = ""; var max = ""; var numberErrors = 0; var showError = false;
        			if((value == "" && scope.answers[array_id].SKIPREASON == "NONE") || (value != "" && isNaN(parseInt(value)))){
                        scope.errors[array_id] = 'Please enter a number';
                        valid = false;
                    }
        			if(question.MINLIMITTYPE == "NLT_LITERAL"){
        				min = question.MINLITERAL;
        			}else if(question.MINLIMITTYPE == "NLT_PREVQUES"){
        				min = scope.answers[question.MINPREVQUES].VALUE;
        			}
        			if(question.MAXLIMITTYPE == "NLT_LITERAL"){
        				max = question.MAXLITERAL;
        			}else if(question.MAXLIMITTYPE == "NLT_PREVQUES"){
        				max = scope.answers[question.MAXPREVQUES].VALUE;
        			}
        			if(min !== "")
        				numberErrors++;
        			if(max !== "")
        				numberErrors = numberErrors + 2;
        			if(((max !== "" && parseInt(value) > parseInt(max))  ||  (min !== "" && parseInt(value) < parseInt(min))) && scope.answers[array_id].SKIPREASON == "NONE")
        				showError = true;

        			if(numberErrors == 3 && showError)
        				errorMsg = "The range of valid answers is " + min + " to " + max + ".";
        			else if (numberErrors == 2 && showError)
        				errorMsg = "The range of valid answers is " + max + " or fewer.";
        			else if (numberErrors == 1 && showError)
        				errorMsg = "The range of valid answers is " + min + " or greater.";

        			if(showError){
                        scope.errors[array_id] = errorMsg;
                        valid = false;
        			}
        		}

                if(attr.answerType == "DATE"){
                    console.log(attr.answerType);
                    if(scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW"){
            			var date = value.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
            			var time = value.match(/(\d+):(\d+) (AM|PM)/);
            			if(typeof time != "undefined" && time && time.length > 2){
                            delete scope.errors[array_id];
            			    if(parseInt(time[1]) < 1 || parseInt(time[1]) > 12){
                                scope.errors[array_id] = 'Please enter 1 to 12 for HH';
                            	valid = false;
            			    }
            			    console.log(time);
            			    if(parseInt(time[2]) < 0 || parseInt(time[2]) > 59){
            			    	scope.errors[array_id] = 'Please enter 0 to 59 for MM';
            				    valid = false;
            			    }
            			}else{
                			if(scope.timeBits(question.TIMEUNITS, 'HOUR')){
                		    	scope.errors[array_id] = 'Please enter the time of day';
                			    valid = false;
            			    }
            			}
            			if(typeof date != "undefined" && date && date.length > 3){
            			    if(parseInt(date[2]) < 1 || parseInt(date[2]) > 31){
            			    	scope.errors[array_id] = 'Please enter a different number for the day of month';
            					valid = false;
            			    }
            			}
        			}else{
                        delete scope.errors[array_id];
                    }
        		}

        		if(attr.answerType == "MULTIPLE_SELECTION"){
            		var showError = false;
        			min = question.MINCHECKABLEBOXES;
        			max = question.MAXCHECKABLEBOXES;
        			var numberErrors = 0; var showError = false; var errorMsg = "";
        			if(min !== "")
        				numberErrors++;
        			if(max !== "")
        				numberErrors = numberErrors + 2;

        			checkedBoxes = value.split(',').length;
        			if(!value)
        				checkedBoxes = 0;

        			if ((checkedBoxes < min || checkedBoxes > max) && scope.answers[array_id].SKIPREASON == "NONE")
        				showError = true;

        			//console.log('min:' + min + ':max:' + max + ':checked:' + checkedBoxes+ ":answer:" + value + ":showerror:" + showError);

        			adds = '';
        			if(max != 1)
        				adds = 's';
        			if(parseInt(question.ASKINGSTYLELIST) == 1)
        				adds += ' for each row';
        			if(numberErrors == 3 && min == max && showError)
        				errorMsg = "Select " + max  + " response" + adds + " please.";
        			else if(numberErrors == 3 && min != max && showError)
        				errorMsg = "Select " + min + " to " + max + " response" + adds + " please.";
        			else if (numberErrors == 2 && showError)
        				errorMsg = "You may select up to " + max + " response" + adds + " please.";
        			else if (numberErrors == 1 && showError)
        				errorMsg = "You must select at least " + min + " response" + adds + " please.";
        			//if(answer.OTHERSPECIFYTEXT && showError)
        			//	showError = false;

        			if(showError){
                        scope.errors[array_id] = errorMsg;
                        valid = false;
        			}else{
            			if(typeof scope.errors[array_id] != "undefined")
            			    delete scope.errors[array_id];
                        valid = true;
        			}
        		}

    		// check for list range limitations
    		var checks = 0;
    		if(typeof question != "undefined" && parseInt(question.WITHLISTRANGE) != 0){
    			for(i in answers){
    					console.log(answers[i].VALUE + ":" + question.LISTRANGESTRING);
    				if(answers[i].VALUE.split(',').indexOf(question.LISTRANGESTRING) != -1){
    					checks++;
    				}
    			}
    
                console.log("check list range: " + checks);
    
    			if(checks < question.MINLISTRANGE || checks > question.MAXLISTRANGE){
    				errorMsg = "";
    				if(question.MINLISTRANGE && question.MAXLISTRANGE){
    					if(question.MINLISTRANGE != question.MAXLISTRANGE)
    						errorMsg = question.MINLISTRANGE + " - " + question.MAXLISTRANGE;
    					else
    						errorMsg = "just " + question.MINLISTRANGE;
    				}else if(!question.MINLISTRANGE && !question.MAXLISTRANGE){
    						errorMsg = "up to " + question.MAXLISTRANGE;
    				}else{
    						errorMsg = "at least " + question.MINLISTRANGE;
    				}
    
                    valid = false;
                    scope.errors[array_id] = "Please select "  + errorMsg + " response(s).  You selected " + checks;
    
    
    			}else{
        			console.log("pass: " + valid);
        			for(k in scope.errors){
            			if(scope.errors[k].match("Please select "))
            			    delete scope.errors[k];
        			}
    			}
    		}
		
            ngModel.$setValidity('checkAnswer', valid);
            return value;
          });

      }
   };
}]);


function buildQuestions(pageNumber, interviewId){
	var page = [];
	i = 0;
	page[i] = new Object;
	if(study.INTRODUCTION != ""){
		if(i == pageNumber){
			introduction = new Object;
			introduction.ANSWERTYPE = "INTRODUCTION";
			introduction.PROMPT = study.INTRODUCTION;
			page[i][0] = introduction;
			return page[i];
		}
		i++;
		page[i] = new Object;
	}
	if(pageNumber == i){
    	if(parseInt(study.HIDEEGOIDPAGE) != 1){
    		for(j in ego_id_questions){
        		if(ego_id_questions[j].ANSWERTYPE == "STORED_VALUE" || ego_id_questions[j].ANSWERTYPE == "RANDOM_NUMBER")
        		    continue;
                ego_id_questions[j].array_id = ego_id_questions[j].ID;
    			page[i][parseInt(ego_id_questions[j].ORDERING) + 1] = ego_id_questions[j];
    		}
    		return page[i];
		}
	}

    if(parseInt(study.HIDEEGOIDPAGE) != 1){
        i++;
        page[i] = new Object;
    }

	if(interviewId != null){
		ego_question_list = new Object;
		network_question_list = new Object;
		prompt = "";
		for(j in ego_questions){
            ego_questions[j].array_id = ego_questions[j].ID;
			if(Object.keys(ego_question_list).length > 0 && prompt != ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
				if(pageNumber == i){
					page[i] = ego_question_list;
					return page[i];
				}
				ego_question_list = new Object;
				prompt = "";
				i++;
				page[i] = new Object;
			}

			if(evalExpression(ego_questions[j].ANSWERREASONEXPRESSIONID) != true)
				continue;

			if(ego_questions[j].PREFACE != ""){
				if(pageNumber == i){
					preface = new Object;
					preface.ID = ego_questions[j].ID;
					preface.ANSWERTYPE = "PREFACE";
					preface.SUBJECTTYPE = "PREFACE";
					preface.PROMPT = ego_questions[j].PREFACE;
					page[i][0] = preface;
					return page[i];
				}
				i++;
				page[i] = new Object;
			}
			if(parseInt(ego_questions[j].ASKINGSTYLELIST) == 1){
			    //console.log(prompt + ":" +ego_questions[j].PROMPT);
			    if(prompt == "" || prompt == ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
			    	//console.log('list type question');
			    	prompt = ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"");
			    	ego_question_list[parseInt(ego_questions[j].ORDERING) + 1] = ego_questions[j];
			    }
			}else{
			    if(pageNumber == i){
		    		page[i][ego_questions[j].ID] = ego_questions[j];
			    	return page[i];
			    }
			    i++;
			    page[i] = new Object;
			}
		}

		if(Object.keys(ego_question_list).length > 0){
			if(pageNumber == i){
				page[i] = ego_question_list;
				return page[i];
			}
			i++;
			page[i] = new Object;
		}

		if(pageNumber == i && study.ALTERPROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"") != ""){
			alter_prompt = new Object;
			alter_prompt.ANSWERTYPE = "ALTER_PROMPT";
			alter_prompt.PROMPT = study.ALTERPROMPT;
			alter_prompt.studyId = study.ID;
			page[i][0] = alter_prompt;
			return page[i];
		}
		i++;
		page[i] = new Object;
		if(Object.keys(alters).length > 0){
			for(j in alter_questions){
				alter_question_list = new Object;
                var preface = new Object;
                preface.ID = alter_questions[j].ID;
                preface.ANSWERTYPE = "PREFACE";
                preface.SUBJECTTYPE = "PREFACE";
                preface.PROMPT = alter_questions[j].PREFACE;
				for(k in alters){
					if(evalExpression(alter_questions[j].ANSWERREASONEXPRESSIONID, alters[k].ID) != true)
						continue;

					var question = $.extend(true,{}, alter_questions[j]);
					question.PROMPT = question.PROMPT.replace(/\$\$/g, alters[k].NAME);
					question.ALTERID1 = alters[k].ID;
			    	question.array_id = question.ID + '-' + question.ALTERID1;

					if(parseInt(alter_questions[j].ASKINGSTYLELIST) == 1){
						alter_question_list[question.ID + '-' + question.ALTERID1] = question;
					}else{
						if(preface.PROMPT != ""){
							if(i == pageNumber){
								page[i][0] = preface;
								return page[i];
							}
							preface.PROMPT = "";
							i++;
							page[i] = new Object;
						}
						if(i == pageNumber){
							page[i][question.ID + '-' + question.ALTERID1] = question;
							return page[i];
						}else {
							i++;
							page[i] = new Object;
						}
					}
				}
				if(parseInt(alter_questions[j].ASKINGSTYLELIST) == 1){
					if(Object.keys(alter_question_list).length > 0){
						if(preface.PROMPT != ""){
							if(i == pageNumber){
								page[i][0] = preface;
								return page[i];
							}
                            preface.PROMPT = "";
							i++;
							page[i] = new Object;
						}
						if(i == pageNumber){
							page[i] = alter_question_list;
							return page[i];
						}
						i++;
						page[i] = new Object;
					}
				}
			}

			for(j in alter_pair_questions){
				var alters2 = $.extend(true,{}, alters);
				var preface = new Object;
				preface.ID = alter_pair_questions[j].ID;
				preface.ANSWERTYPE = "PREFACE";
				preface.SUBJECTTYPE = "PREFACE";
				preface.PROMPT = alter_pair_questions[j].PREFACE;
				for(k in alters){
					if(alter_pair_questions[j].SYMMETRIC){
    					var keys = Object.keys(alters2);
    					delete alters2[keys[0]];
					}
						//alters2.shift();
					alter_pair_question_list = new Object;
					for(l in alters2){
						if(alters[k].ID == alters2[l].ID)
							continue;
						if(evalExpression(alter_pair_questions[j].ANSWERREASONEXPRESSIONID, alters[k].ID, alters2[l].ID) != true)
							continue;
						var question = $.extend(true,{}, alter_pair_questions[j]);
						question.PROMPT = question.PROMPT.replace(/\$\$1/g, alters[k].NAME);
						question.PROMPT = question.PROMPT.replace(/\$\$2/g, alters2[l].NAME);
						question.ALTERID1 = alters[k].ID;
						question.ALTERID2 = alters2[l].ID;
                        question.array_id = question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2;

						if(parseInt(alter_pair_questions[j].ASKINGSTYLELIST) == 1){
							alter_pair_question_list[question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2] = question;
						}else{
							if(preface.PROMPT != ""){
								if(i == pageNumber){
									page[i][0] = preface;
									return page[i];
								}
								preface.PROMPT = "";
								i++;
								page[i] = new Object;
							}
							if(i == pageNumber){
								page[i][question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2] = question;
								return page[i];
							}else{
								i++;
								page[i] = new Object;
							}
						}
					}
					if(alter_pair_questions[j].ASKINGSTYLELIST){
						if(Object.keys(alter_pair_question_list).length > 0){
							if(preface.PROMPT != ""){
								if(i == pageNumber){
									page[i][0] = preface;
									return page[i];
								}
								preface.PROMPT = "";
								i++;
								page[i] = new Object;
							}
							if(i == pageNumber){
								page[i] = alter_pair_question_list;
								return page[i];
							}
							i++;
							page[i] = new Object;
						}
					}
				}
			}

		}
		for(j in network_questions){
            network_questions[j].array_id = network_questions[j].ID;

			if(Object.keys(network_question_list).length > 0 && prompt != network_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
				if(pageNumber == i){
					page[i] = network_question_list;
					return page[i];
				}
				network_question_list = new Object;
				prompt = "";
				i++;
				page[i] = new Object;
			}

			if(evalExpression(network_questions[j].ANSWERREASONEXPRESSIONID) != true)
				continue;



			if(network_questions[j].PREFACE != ""){
				if(pageNumber == i){
					var preface = new Object;
					preface.ID = network_questions[j].ID;
					preface.ANSWERTYPE = "PREFACE";
					preface.SUBJECTTYPE = "PREFACE";
					preface.PROMPT = network_questions[j].PREFACE;
					page[i][0] = preface;
					return page[i];
				}
				i++;
				page[i] = new Object;
			}

			if(parseInt(network_questions[j].ASKINGSTYLELIST) == 1){
			    //console.log(prompt + ":" +ego_questions[j].PROMPT);
			    if(prompt == "" || prompt == network_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
			    	//console.log('list type question');
			    	prompt = network_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"");
			    	network_question_list[parseInt(network_questions[j].ORDERING) + 1] = network_questions[j];
			    }
			}else{
			    if(pageNumber == i){
		    		page[i][network_questions[j].ID] = network_questions[j];
			    	return page[i];
			    }
			    i++;
			    page[i] = new Object;
			}
		}

		if(Object.keys(network_question_list).length > 0){
			if(pageNumber == i){
				page[i] = network_question_list;
				return page[i];
			}
			i++;
			page[i] = new Object;
		}

		conclusion = new Object;
		conclusion.ANSWERTYPE = "CONCLUSION";
		conclusion.PROMPT = study.CONCLUSION;
        conclusion.array_id = 0;
		page[i][0] = conclusion;
		return page[i];

	}
	return false;
}

function evalExpression(id, alterId1, alterId2)
{
	var array_id;
    if(!id || id == 0)
        return true;
    if(typeof expressions[id] == "undefined")
        return true;

    questionId = expressions[id].QUESTIONID;
    subjectType = "";
    if(questionId && questions[questionId])
        subjectType = questions[questionId].SUBJECTTYPE;

    comparers = {
    	'Greater':'>',
    	'GreaterOrEqual':'>=',
    	'Equals':'==',
    	'LessOrEqual':'<=',
    	'Less':'<'
    };

    if(questionId)
    	array_id = questionId;
    if(typeof alterId1 != 'undefined' && subjectType == 'ALTER')
    	array_id += "-" + alterId1;
    else if(typeof alterId2 != 'undefined' && subjectType == 'ALTER_PAIR')
    	array_id += "-" + alterId1 + 'and' + alterId2;

    if(typeof answers[array_id] != "undefined")
		answer = answers[array_id].VALUE;
    else
    	answer = "";

    if(expressions[id].TYPE == "Text"){
    	if(!answer)
    		return expressions[id].RESULTFORUNANSWERED;
    	if(expressions[id].OPERATOR == "Contains"){
    		if(answer.indexOf(expressions[id].VALUE) != -1){
                console.log(expressions[id].NAME + ":true");
    			return true;
            }
    	}else if(expressions[id].OPERATOR == "Equals"){
    		if(answer == expressions[id].VALUE){
                console.log(expressions[id].NAME + ":true");
    			return true;
            }
    	}
    }
    if(expressions[id].TYPE == "Number"){
    	if(!answer)
    		return expressions[id].RESULTFORUNANSWERED;
    	logic = answer + " " + comparers[expressions[id].OPERATOR] + " " + expressions[id].VALUE;
    	result = eval(logic);
        console.log(expressions[id].NAME + ":" + result);
    	return result;
    }
    if(expressions[id].TYPE == "Selection"){
    	if(!answer)
    		return expressions[id].RESULTFORUNANSWERED;
    	selectedOptions = answer.split(',');
    	var options = expressions[id].VALUE.split(',');
    	trues = 0;
    	for (var k in selectedOptions) {
    		if(expressions[id].OPERATOR == "Some" && options.indexOf(selectedOptions[k]) != -1){
                console.log(expressions[id].NAME + ":true");
    			return true;
            }
    		if(expressions[id].OPERATOR == "None" && options.indexOf(selectedOptions[k]) != -1){
                console.log(expressions[id].NAME + ":false");
    			return false;
            }
    		if(options.indexOf(selectedOptions[k]) != -1)
    			trues++;
    	}
    	if(expressions[id].OPERATOR == "None" || (expressions[id].OPERATOR == "All" && trues >= options.length)){
            console.log(expressions[id].NAME + ":true");
    		return true;
        }
    }
    if(expressions[id].TYPE == "Counting"){
    	countingSplit = expressions[id].VALUE.split(':');
		var times = parseInt(countingSplit[0]);
		var expressionIds = countingSplit[1];
		var questionIds = countingSplit[2];

    	var count = 0;
    	if(expressionIds != ""){
    		expressionIds = expressionIds.split(',');
    		for (var k in expressionIds) {
    			count = count + evalExpression(expressionIds[k], alterId1, alterId2);
    		}
    	}
    	if(questionIds != ""){
    		questionIds = questionIds.split(',');
    		for (var k in questionIds) {
    			count = count + countQuestion(questionIds[k], expressions[id].OPERATOR);
    		}
    	}
        console.log(expressions[id].NAME + ":" + (times * count));
    	return (times * count);
    }
    if(expressions[id].TYPE == "Comparison"){
    	compSplit =  expressions[id].VALUE.split(':');
    	value = parseInt(compSplit[0]);
    	expressionId = parseInt(compSplit[1]);
    	result = evalExpression(expressionId, alterId1, alterId2);
    	logic = result + " " + comparers[expressions[id].OPERATOR] + " " + value;
    	result = eval(logic);
        console.log(expressions[id].NAME + ":" + result);
    	return result;
    }
    if(expressions[id].TYPE == "Compound"){
    	var subexpressions = expressions[id].VALUE.split(',');
    	var trues = 0;
    	for (var k in subexpressions) {
    		// prevent infinite loops!
    		if(parseInt(subexpressions[k]) == id)
    			continue;
    		var isTrue = evalExpression(parseInt(subexpressions[k]), alterId1, alterId2);
    		if(expressions[id].OPERATOR == "Some" && isTrue == true){
            	console.log(expressions[id].NAME + ":true");
    			return true;
    		}
    		if(isTrue == true)
    			trues++;
    		console.log(expressions[id].NAME +":subexpression:"+ k +":" + isTrue);
    	}
    	if(expressions[id].OPERATOR == "None" && trues == 0){
        	console.log(expressions[id].NAME + ":true");
    		return true;
    	}else if (expressions[id].OPERATOR == "All" && trues == subexpressions.length){
        	console.log(expressions[id].NAME + ":true");
    		return true;
        }
    }
    console.log(expressions[id].NAME + ":false");
    return false;

}

function countExpression(id)
{
    if(evalExpression(id) == true)
    	return 1;
    else
    	return 0;
}

function countQuestion(questionId, operator, alterId1, alterId2)
{
    if(questionId)
    	array_id = questionId;
    if(typeof alterId1 != 'undefined' && subjectType == 'ALTER')
    	array_id += "-" + alterId1;
    else if(typeof alterId2 != 'undefined' && subjectType == 'ALTER_PAIR')
    	array_id += 'and' + alterId2;
    if(typeof answers[array_id] != "undefined")
		answer = answers[array_id].VALUE;
    else
    	answer = "";

    if(!answer){
    	return 0;
    }else{
    	if(operator == "Sum")
    		return parseInt(answer);
    	else
    		return 1;
    }
}

function interpretTags(string, alterId1, alterId2)
{

	// parse out and replace variables
	vars = string.match(/<VAR (.+?) \/>/g);
	for(k in vars){
		var thisVar = vars[k].match(/<VAR (.+?) \/>/)[1];
        var question = getQuestion(thisVar);
        if(!question)
            continue;

        var array_id = question.ID;
        if(typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
        	array_id += "-" + alterId1;
        else if(typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
        	array_id += 'and' + alterId2;

        var lastAnswer = "";
        var lastAnswerOps = [];
		if(typeof answers[array_id] != 'undefined'){
			if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
				for(o in options[question.ID]){
					if($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
					    lastAnswerOps.push(options[question.ID][o].NAME);
				}
				lastAnswer = lastAnswerOps.join("<br>")
			}else{
    			lastAnswer = answers[array_id].VALUE;
			}
			string = string.replace('<VAR ' + thisVar + ' />', lastAnswer);
		}else{
			string = string.replace('<VAR ' + thisVar + ' />', '');
		}
	}

	// performs calculations on questions
	calcs = string.match(/<CALC (.+?) \/>/g);
	for(j in calcs){
		calc = calcs[j].match(/<CALC (.+?) \/>/)[1];
		vars = calc.match(/(\w+)/g);
		for(k in vars){
	    	var thisVar = vars[k].match(/<VAR (.+?) \/>/)[1];
			if(vars[k].match(/<VAR (.+?) \/>/)){
                var question = getQuestion(thisVar);
                if(!question)
                    continue;

                var array_id = question.ID;
                if(typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
                	array_id += "-" + alterId1;
                else if(typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
                	array_id += 'and' + alterId2;

                var lastAnswer = "0";
    			if(typeof answers[array_id] != 'undefined'){
    				if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
        				for(o in options[question.ID]){
        					if($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
        					    lastAnswer = options[question.ID][o].NAME;
        				}
    				}else{
        				lastAnswer = answers[array_id].VALUE;
    				}
    				logic =  calc.replace(thisVar, lastAnswer);
    			}else{
    				logic =  calc.replace(thisVar, '0');
    			}
			}
		}
		try{
			calculation = eval(calc);
		}catch(err){
			calculation = "";
		}
		string = string.replace("<CALC " + calc + " />", calculation);
	}

	// counts numbers of times question is answered with string
	counts = string.match(/<COUNT (.+?) \/>/g);
	for(k in counts){
		var count = counts[k].match(/<COUNT (.+?) \/>/)[1];
		var parts = count.split(' ');
		var qTitle = parts[0];
		var answer = parts[1];
		answer = answer.replace ('"', '');

        var question = getQuestion(qTitle);
        if(!question)
            continue;

        var array_id = question.ID;
        if(typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
        	array_id += "-" + alterId1;
        else if(typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
        	array_id += 'and' + alterId2;

        var lastAnswer = "";
        var lastAnswerOps = [];
		if(typeof answers[array_id] != 'undefined'){
    		if(typeof answers[array_id] != 'undefined'){
    			if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
    				for(o in options[question.ID]){
    					if($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
    					    lastAnswerOps.push(options[question.ID][o].NAME);
                    }
                    lastAnswer = lastAnswerOps.join("<br>")
    			}else{
        			lastAnswer = answers[array_id].VALUE;
    			}
    		}
			string = string.replace('<COUNT ' + count + ' />', lastAnswer ? 1 : 0);
		}else{
			string = string.replace('<COUNT ' + count + ' />', 0);
		}
	}

	// same as count, but limited to specific alter / alter pair questions
	containers  = string.match(/<CONTAINS (.+?) \/>/g);
	for(k in containers){
		var contains = containers[k].match(/<CONTAINS (.+?) \/>/)[1];
		var parts = contains.split(/\s/);
		var qTitle = parts[0];
		var answer = parts[1];
		answer = answer.replace ('"', '');

        var question = getQuestion(qTitle);
        if(!question)
            continue;

        var array_id = question.ID;
        if(typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
        	array_id += "-" + alterId1;
        else if(typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
        	array_id += 'and' + alterId2;

        var lastAnswer = "";
		if(typeof answers[array_id] != 'undefined'){
			if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
				for(o in options[question.ID]){
					if($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
					    lastAnswer = options[question.ID][o].NAME;
				}
			}else{
    			lastAnswer = answers[array_id].VALUE;
			}
			string = string.replace("<CONTAINS " + contains + " />", lastAnswer ? 1 : 0);
		}else{
			string = string.replace("<CONTAINS " + contains + " />", 0);
		}
	}

	// parse out and show logics
	showlogics = string.match(/<IF (.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\" \/>/g);
	for(k in showlogics){
		showlogic = showlogics[k];
		exp = showlogic.match(/\<IF (.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\"/);
		if(exp.length > 1){
			for(i = 1; i < 3; i++){
				if(i == 2 || !isNaN(parseInt(exp[i])))
					continue;
				if(exp[i].match("/>")){
					exp[i] = interpretTags(exp[i]);
				}else{

                    var qTitle = exp[i];
                    var question = getQuestion(qTitle);
                    if(!question)
                        continue;

                    var array_id = question.ID;
                    if(typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
                    	array_id += "-" + alterId1;
                    else if(typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
                    	array_id += 'and' + alterId2;

                    var lastAnswer = "";
                    var lastAnswerOps = [];

        			if(typeof answers[array_id] != 'undefined'){
            			if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
            				for(o in options[question.ID]){
            					if($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
            					    lastAnswerOps.push(options[question.ID][o].NAME);
            				}
            				lastAnswer = lastAnswerOps.join("<br>");
            			}else{
                			lastAnswer = answers[array_id].VALUE;
            			}
                    }else{
						return false;
                    }
					exp[i] = lastAnswer;
				}
			}
			logic = exp[1] + ' ' + exp[2] + ' ' + exp[3];
			show = eval(logic);
			if(show){
				string =  string.replace(showlogic, exp[4]);
			}else{
				string =  string.replace(showlogic, "");
			}
		}
	}
	return string;
}

function getQuestion(title){
    if(title.match(/:/)){
        if(typeof questionList[title.split(":")[0]] != "undefined" && typeof questions[questionList[title.split(":")[0]][title.split(":")[1]]] != "undefined")
            return questions[questionList[title.split(":")[0]][title.split(":")[1]]];
    }else{
        if(typeof questionList[study.NAME] != "undefined" && typeof questions[questionList[study.NAME][title]] != "undefined")
            return questions[questionList[study.NAME][title]];
    }
    return false;
}

function initStats(question){
    shortPaths  = new Object;
    connections = [];
    nodes = [];
    edges = [];
    var n = [];
    var expressionId = question.NETWORKRELATIONSHIPEXPRID;
    if(!question.NETWORKPARAMS)
        question.NETWORKPARAMS = "[]";
    this.params = JSON.parse(question.NETWORKPARAMS);
    if(this.params == null)
        this.params = [];
    alterNames = new Object;
    betweennesses = [];
	if(alters.length == 0)
		return false;

    var alters2 = $.extend(true,{}, alters);

    if(typeof expressions[expressionId] != "undefined")
    	var expression = expressions[expressionId];
	else
	    return;

	if(expression.QUESTIONID)
		var question = questions[expression.QUESTIONID];

	for(a in alters){
		betweennesses[alters[a].ID] = 0;
		var keys = Object.keys(alters2);
		delete alters2[keys[0]];
		alterNames[alters[a].ID] = alters[a].NAME;
		for(b in alters2){
			if(alters[a].ID == alters2[b].ID)
				continue;
			if(evalExpression(expressionId, alters[a].ID, alters2[b].ID) == true){
				if($.inArray(alters[a].ID, n) == -1)
				    n.push(alters[a].ID);
				if($.inArray(alters2[b].ID, n) == -1)
				    n.push(alters2[b].ID);
				if(typeof connections[alters[a].ID] == "undefined")
				    connections[alters[a].ID] = [];
				if(typeof connections[alters2[b].ID] == "undefined")
				    connections[alters2[b].ID] = [];
				connections[alters[a].ID].push(alters2[b].ID);
				connections[alters2[b].ID].push(alters[a].ID);
			}
		}
	}

	this.getDistance = function (visited, node2){
		var node1 =  visited[visited.length - 1];

		if($.inArray(node2, connections[node1]) != -1){
    		var trail = visited.slice(0);
			trail.push(node2);
			if(typeof shortPaths[visited[0] + "-" + node2] == "undefined"){

    			shortPaths[visited[0] + "-" + node2] = [];
				shortPaths[visited[0] + "-" + node2].push(trail);
				if(typeof shortPaths[node2 + "-" + visited[0]] == "undefined")
				    shortPaths[node2 + "-" + visited[0]] = [];
				shortPaths[node2 + "-" + visited[0]].push(trail);
			}else{
				if(trail.length < shortPaths[visited[0] + "-" + node2][0].length){
					shortPaths[visited[0] + "-" + node2] = [];
					shortPaths[node2 + "-" + visited[0]] = [];
				}
				if(shortPaths[visited[0] + "-" + node2].length == 0 || trail.length == shortPaths[visited[0] + "-" + node2][0].length){
					shortPaths[visited[0] + "-" + node2].push(trail);
					shortPaths[node2 + "-" + visited[0]].push(trail);
				}
			}
		}else{
			for(k in connections[node1]){

    			var endNode = connections[node1][k];

				if($.inArray(endNode, visited) == -1){

    				var trail = visited.slice(0);
					trail.push(endNode);
					if (typeof shortPaths[visited[0] + "-" + endNode] != "undefined"){

						if(trail.length < shortPaths[visited[0] + "-" + endNode][0].length){

							shortPaths[visited[0] + "-" + endNode] = [];
							shortPaths[endNode + "-" + visited[0]] = [];
						}
						if(shortPaths[visited[0] + "-" + endNode].length == 0 || trail.length == shortPaths[visited[0] + "-" + endNode][0].length){

							shortPaths[visited[0] + "-" + endNode].push(trail);
							shortPaths[endNode + "-" + visited[0]].push(trail);
						}else{
							continue;
						}
					} else {
    					shortPaths[visited[0] + "-" + endNode] = [];
						shortPaths[visited[0] + "-" + endNode].push(trail);
						if(typeof shortPaths[endNode + "-" + visited[0]] == "undefined")
						    shortPaths[endNode + "-" + visited[0]] = [];
						shortPaths[endNode + "-" + visited[0]].push(trail);
					}
					this.getDistance(trail, node2);
				}
		    }
		}
	}



	for(k in alters){
		if(typeof connections[alters[k].ID] == "undefined"){
			//this.isolates[] = $alter.id;
			//this.nodes[] = $alter.id;
			n.push(alters[k].ID);
			connections[alters[k].ID] = [];
		}
	}

    var n2 = n.slice(0);
	for(a in n){
        n2.shift();
		for(b in n2){
			this.getDistance([n[a]], n2[b]);
		}
	}


	for(k in shortPaths){
		var between = [];

		for(p in shortPaths[k]){

			var path = shortPaths[k][p].slice(0);
			path.pop();
			path.shift();

			for(n in path){
				if(typeof between[path[n]] == "undefined")
					between[path[n]] = 1;
				else
                    between[path[n]] = between[path[n]] + 1;
			}
		}
		for(b in between){
			betweennesses[b] = betweennesses[b] + (between[b] / shortPaths[k].length);
		}
	}


    closenesses = [];
    var alters2 = $.extend(true,{}, alters);
	for(a in alters){
        var total = 0;
        var reachable = 0;
		for(b in alters2){
			if(typeof shortPaths[alters[a].ID + "-" + alters2[b].ID] != "undefined"){
				distance = shortPaths[alters[a].ID + "-" + alters2[b].ID][0].length - 1;
				total = total + distance;
				reachable++;
			}
		}
		if(reachable < 1){
			closenesses[alters[a].ID] = 0.0;
        }else{
		    average = total / reachable;
            closenesses[alters[a].ID] = reachable / (average * (Object.keys(alters2).length - 1));
        }
	}

	this.nextEigenvectorGuess = function(guess) {
		var results = [];
		for(g in guess) {
			var result = 0.0;
			if(typeof connections[g] != "undefined"){
				for(c in connections[g]) {
					result = result + guess[connections[g][c]];
				}
			}
			results[g] = result;
		}
		return this.normalize(results);
	}

	this.tinyNum = 0.0000001;

	this.normalize = function(vec) {
		var magnitudeSquared = 0.0;
		for(g in vec) {
			magnitudeSquared = magnitudeSquared + Math.pow(vec[g],2);
		}
		var magnitude =  Math.sqrt(magnitudeSquared);
		var factor = 1 / (magnitude < this.tinyNum ? this.tinyNum : magnitude);
		var normalized = [];
		for(g in vec) {
			normalized[g] = vec[g]  * factor;
		}
		return normalized;
	}

	this.change = function (vec1, vec2) {
		var total = 0.0;
		for(g in vec1) {
			total = total + Math.abs(vec1[g] - vec2[g]);
		}
		return total;
	}

	var tries = (n.length+5)*(n.length+5);
	var guess = closenesses;
	while(tries >= 0) {
		var nextGuess = this.nextEigenvectorGuess(guess);
		if(this.change(guess,nextGuess) < this.tinyNum || tries == 0) {
			eigenvectors = nextGuess;
		}
		guess = nextGuess;
		tries--;
	}

	var all = [];
	for(k in betweennesses){
    	all.push(betweennesses[k]);
	}
	maxBetweenness = Math.max.apply(Math, all);
	minBetweenness = Math.min.apply(Math, all);

	var all = [];
	for(k in eigenvectors){
    	all.push(eigenvectors[k]);
	}
	maxEigenvector = Math.max.apply(Math, all);
	minEigenvector = Math.min.apply(Math, all);

	var all = [];
	for(k in connections){
    	all.push(connections[k].length);
	}
	maxDegree = Math.max.apply(Math, all);
	minDegree = Math.min.apply(Math, all);

    this.edgeColors = {
		'#000':'black',
		'#ccc':'gray',
		'#07f':'blue',
		'#0c0':'green',
		'#F80':'orange',
		'#fa0':'yellow',
		'#f00':'red',
		'#c0f':'purple',
	};
	this.edgeSizes = {
		"0.5":'0.5',
		"2":'2',
		"4":'4',
		"8":'8',
	};
	this.nodeColors = {
		'#000':'black',
		'#ccc':'gray',
		'#07f':'blue',
		'#0c0':'green',
		'#F80':'orange',
		'#fa0':'yellow',
		'#f00':'red',
		'#c0f':'purple',
	};
	this.nodeShapes = {
		'circle':'circle',
		'star':'star',
		'diamond':'diamond',
		'cross':'cross',
		'equilateral':'triangle',
		'square':'square',
	};
	this.nodeSizes = {
		2:'1',
		4:'2',
		6:'3',
		8:'4',
		10:'5',
		12:'6',
		14:'7',
		16:'8',
		18:'9',
		20:'10',
	};
	this.gradient = {
		0:"#F5D6D6",
		1:"#ECBEBE",
		2:"#E2A6A6",
		3:"#D98E8E",
		4:"#CF7777",
		5:"#C65F5F",
		6:"#BC4747",
		7:"#B32F2F",
		8:"#A91717",
		9:"#A00000",
	};

	this.getNodeColor = function(nodeId){
		if(typeof this.params['nodeColor'] != "undefined"){
			if($.inArray(this.params['nodeColor']['questionId'], ["degree", "betweenness", "eigenvector"]) != -1){
				if(this.params['nodeColor']['questionId'] == "degree"){
					max = maxDegree;
					min = minDegree;
					value = connections[nodeId].length;
				}
				if(this.params['nodeColor']['questionId'] == "betweenness"){
					max = maxBetweenness;
					min = minBetweenness;
					value = betweennesses[nodeId];
				}
				if(this.params['nodeColor']['questionId'] == "eigenvector"){
					max = maxEigenvector;
					min = minEigenvector;
					value = eigenvectors[nodeId];
				}
				range = max - min;
				if(range == 0)
					range = 1;
				value = Math.round(((value-min) / (range)) * 9);
				return this.gradient[value];
			}else if(this.params['nodeColor']['questionId'].search("expression") != -1){
				var qId = this.params['nodeColor']['questionId'].split("_");
				if(evalExpression(qId[1], nodeId) == true){
					for(p in this.params['nodeColor']['options']){
						if(this.params['nodeColor']['options'][p]['id'] == 1)
							return this.params['nodeColor']['options'][p]['color'];
					}
				}else{
					for(p in this.params['nodeColor']['options']){
						if(this.params['nodeColor']['options'][p]['id'] == 0)
							return this.params['nodeColor']['options'][p]['color'];
					}
				}
			}else if(!isNaN(this.params['nodeColor']['questionId'])){
                if(typeof answers[this.params['nodeColor']['questionId'] + "-" + nodeId] != "undefined")
	    			var answer = answers[this.params['nodeColor']['questionId'] + "-" + nodeId].VALUE.split(",");
                else
                    var answer = "";
				for(p in this.params['nodeColor']['options']){
					if(this.params['nodeColor']['options'][p]['id'] == answer || $.inArray(this.params['nodeColor']['options'][p]['id'], answer) != -1)
                        return this.params['nodeColor']['options'][p]['color'];
				}
			}
		}
		return "#07f";
	}

	this,getNodeSize = function(nodeId){
		if(typeof this.params['nodeSize'] != "undefined"){
			if($.inArray(this.params['nodeSize']['questionId'], ["degree", "betweenness", "eigenvector"]) != -1){
				if(this.params['nodeSize']['questionId'] == "degree"){
					max = maxDegree;
					min = minDegree;
					value = connections[nodeId].length;
				}
				if(this.params['nodeSize']['questionId'] == "betweenness"){
					max = maxBetweenness;
					min = minBetweenness;
					value = betweennesses[nodeId];
				}
				if(this.params['nodeSize']['questionId'] == "eigenvector"){
					max = maxEigenvector;
					min = minEigenvector;
					value = eigenvectors[nodeId];
				}
				range = max - min;
				if(range == 0)
					range = 1;
				value = Math.round(((value-min) / (range)) * 9) + 1;
				return value * 2;
			}else{
    			if(typeof answers[this.params['nodeSize']['questionId'] + "-" + nodeId] != "undefined")
				    var answer = answers[this.params['nodeSize']['questionId'] + "-" + nodeId].VALUE.split(",");
				else
				    var answer = "";
    			for(p in this.params['nodeSize']['options']){
    				if(this.params['nodeSize']['options'][p]['id'] == answer || $.inArray(this.params['nodeSize']['options'][p]['id'], answer) != -1)
    				    return this.params['nodeSize']['options'][p]['size'];
    			}
			}

		}
		return 4;
	}

	this.getNodeShape = function(nodeId){
		if(typeof this.params['nodeShape'] != "undefined"){
            if(typeof answers[this.params['nodeShape']['questionId'] + "-" + nodeId] != "undefined")
                var answer = answers[this.params['nodeShape']['questionId'] + "-" + nodeId].VALUE.split(",");
            else
                var answer = "";
			for(p in this.params['nodeShape']['options']){
				if(this.params['nodeShape']['options'][p]['id'] == answer || $.inArray(this.params['nodeShape']['options'][p]['id'], answer) != -1)
				    return this.params['nodeShape']['options'][p]['shape'];
			}
		}
		return "circle";
	}

	this.getEdgeColor = function(nodeId1, nodeId2){
		if(typeof this.params['edgeColor'] != "undefined"){
            if(typeof answers[this.params['edgeColor']['questionId'] + "-" + nodeId1 + "and" + nodeId2] != "undefined")
                var answer = answers[this.params['edgeColor']['questionId'] + "-" + nodeId1 + "and" + nodeId2].VALUE.split(",");
            else
                var answer = "";
			for(p in this.params['edgeColor']['options']){
				if(this.params['edgeColor']['options'][p]['id'] == answer || $.inArray(this.params['edgeColor']['options'][p]['id'], answer) != -1)
				    return this.params['edgeColor']['options'][p]['color'];
			}
		}
		return "#ccc";
	}

	this.getEdgeSize = function(nodeId1, nodeId2){
		if(typeof this.params['edgeSize'] != "undefined"){
            if(typeof answers[this.params['edgeSize']['questionId'] + "-" + nodeId1 + "and" + nodeId2] != "undefined")
                var answer = answers[this.params['edgeSize']['questionId'] + "-" + nodeId1 + "and" + nodeId2].VALUE.split(",");
            else
                var answer = "";
			for(p in this.params['edgeSize']['options']){
				if(this.params['edgeSize']['options'][p]['id'] == answer || $.inArray(this.params['edgeSize']['options'][p]['id'], answer) != -1)
				    return this.params['edgeSize']['options'][p]['size'];
			}
		}
		return 1;
	}

    var alters2 = $.extend(true,{}, alters);
	for(a in alters){
		nodes.push(
			{
				'id'   : alters[a].ID.toString(),
				'label': alters[a].NAME + (typeof notes[alters[a].ID] != "undefined" ? " �" : ""),
				'x'    : Math.random(),
				'y'    : Math.random(),
				"type" : this.getNodeShape(alters[a].ID),
				"color": this.getNodeColor(alters[a].ID),
				"size" : this.getNodeSize(alters[a].ID),
			}
		);
		var keys = Object.keys(alters2);
		delete alters2[keys[0]];
		for(b in alters2){
			if(evalExpression(expressionId, alters[a].ID, alters2[b].ID) == true){
				edges.push({
					"id"    : alters[a].ID + "_" + alters2[b].ID,
					"source": alters2[b].ID.toString(),
					"target": alters[a].ID.toString(),
					"color" : this.getEdgeColor(alters[a].ID, alters2[b].ID),
					"size"  : this.getEdgeSize(alters[a].ID, alters2[b].ID),
				});
			}
		}
	}


g = {
	nodes: nodes,
	edges: edges,
	//legends:  <?= json_encode($legends); ?>
};

sizes = [];
for(y in g.nodes){sizes.push(g.nodes[y].size)}
	max_node_size = Math.max.apply(Math, sizes);

sizes = [];
for(y in g.edges){sizes.push(g.edges[y].size)}
	max_edge_size = Math.max.apply(Math, sizes);

setTimeout(function(){
	sigma.renderers.def = sigma.renderers.canvas;
	s = new sigma({
		graph: g,
		renderer: {
			container: document.getElementById('infovis'),
			type: 'canvas'
		},
		settings: {
			doubleClickEnabled: false,
			labelThreshold: 1,
			minNodeSize: 2,
			maxNodeSize: max_node_size,
			minEdgeSize: 0.5,
			maxEdgeSize: max_edge_size,
			zoomingRatio: 1.0,
			sideMargin: 2
		}
	});
	if(typeof graphs[expressionId] != "undefined"){
        savedNodes = JSON.parse(graphs[expressionId].NODES);
		for(var k in savedNodes){
			var node = s.graph.nodes(k.toString());
			if(node){
				node.x = savedNodes[k].x;
				node.y = savedNodes[k].y;
			}
		}
	}else{
		s.startForceAtlas2({
			"worker":false,
			"outboundAttractionDistribution":true,
			"speed":2000,
			"gravity": 0.2,
			"jitterTolerance": 0,
			"strongGravityMode": true,
			"barnesHutOptimize": false,
			"totalSwinging": 0,
			"totalEffectiveTraction": 0,
			"complexIntervals":500,
			"simpleIntervals": 1000
		});
		setTimeout("s.stopForceAtlas2(); saveNodes(); $('#fullscreenButton').prop('disabled', false);", 5000);
    }
	s.refresh();
    initNotes(s);
	},1);
}

function fullscreen(){
	elem = document.getElementById("visualizePlugin");
	if (typeof elem.requestFullscreen != "undefined") {
		elem.requestFullscreen();
	} else if (typeof elem.msRequestFullscreen != "undefined") {
		elem.msRequestFullscreen();
	} else if (typeof elem.mozRequestFullScreen != "undefined") {
		elem.mozRequestFullScreen();
	} else if (typeof elem.webkitRequestFullscreen != "undefined") {
		elem.webkitRequestFullscreen();
	}
	$("#infovis").height(640);
    setTimeout( function (){
    document.addEventListener('webkitfullscreenchange', exitHandler, false);
    document.addEventListener('mozfullscreenchange', exitHandler, false);
    document.addEventListener('fullscreenchange', exitHandler, false);
    document.addEventListener('MSFullscreenChange', exitHandler, false);

    }, 500);

}

function toggleLabels(){
    var labelT = s.renderers[0].settings("labelThreshold");
    if(labelT == 1)
        s.renderers[0].settings({labelThreshold:100});
    else
        s.renderers[0].settings({labelThreshold:1});
    s.refresh();
}


function exitHandler()
{
    if (document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement !== null)
    {
    	$("#infovis").height(480);
    document.removeEventListener('webkitfullscreenchange', exitHandler, false);
    document.removeEventListener('mozfullscreenchange', exitHandler, false);
    document.removeEventListener('fullscreenchange', exitHandler, false);
    document.removeEventListener('MSFullscreenChange', exitHandler, false);
    }
}

			function redraw(params){
				url = "/data/deleteGraph?id=" + $("#Graph_id").val();
				$.get(url, function(data){
					url = "/data/visualize?expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent(JSON.stringify(params));
					document.location = document.location + "&params=" + encodeURIComponent(JSON.stringify(params));
				});
			}



function buildNav(pageNumber){
	var i = 0;
	var pages = [];

	this.checkPage = function (currentPage, pageNumber, text){
		if(currentPage == pageNumber){
            $("#questionTitle").html(text);
			text = "<b>" + text + "</b>";
		}
		return text;
	};

	if(study.INTRODUCTION != ""){
		pages[i] = this.checkPage(i, pageNumber, "INTRODUCTION");
		i++;
	}
	if(parseInt(study.HIDEEGOIDPAGE) != 1){
    	pages[i] = this.checkPage(i, pageNumber, "EGO ID");
        i++;
    }
	if(!interviewId){
		return pages;
	}
	var prompt = "";
	var ego_question_list = '';
	for(j in ego_questions){
		if(evalExpression(ego_questions[j].ANSWERREASONEXPRESSIONID, interviewId) != true)
			continue;

		if((parseInt(ego_questions[j].ASKINGSTYLELIST) != 1 || prompt != ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")) && ego_question_list){
		    pages[i] = this.checkPage(i, pageNumber, ego_question_list.TITLE);
			prompt = "";
		    ego_question_list = '';
		    i++;
		}
		if(ego_questions[j].PREFACE != ""){
			pages[i] = this.checkPage(i, pageNumber, "PREFACE");
			i++;
		}
		if(parseInt(ego_questions[j].ASKINGSTYLELIST) == 1){
		    prompt = ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"");
		    if(ego_question_list == '')
			    ego_question_list = ego_questions[j];
		}else{
		    pages[i] = this.checkPage(i, pageNumber, ego_questions[j].TITLE);
		    i++;
		}

	}
	if(ego_question_list){
		pages[i] = this.checkPage(i, pageNumber, ego_question_list.TITLE);
		ego_question_list = '';
		i++;
	}
	if(study.ALTERPROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
		pages[i] = this.checkPage(i, pageNumber, "ALTER_PROMPT");
		i++;
	}

	if(Object.keys(alters).length > 0){
		for(j in alter_questions){
            prompt = "";
			var alter_question_list = '';
			for(k in alters){
				if(evalExpression(alter_questions[j].ANSWERREASONEXPRESSIONID, alters[k].ID) != true)
					continue;

				if(parseInt(alter_questions[j].ASKINGSTYLELIST) == 1){
			    	alter_question_list = alter_questions[j];
			    }else{
					if(alter_questions[j].PREFACE != "" && prompt == ""){
			    		pages[i] = this.checkPage(i, pageNumber, "PREFACE");
                        prompt = alter_questions[j].PREFACE;
			    		i++;
			    	}
			    	pages[i] = this.checkPage(i, pageNumber, alter_questions[j].TITLE + " - " + alters[k].NAME);
			    	i++;
			    }
			}
			if(parseInt(alter_questions[j].ASKINGSTYLELIST) == 1){
			    if(alter_question_list){
			    	if(alter_questions[j].PREFACE != ""){
			    		pages[i] = this.checkPage(i, pageNumber, "PREFACE");
			    		i++;
			    	}
			    	pages[i] = this.checkPage(i, pageNumber, alter_question_list.TITLE);
			    	i++;
			    }
			}
		}
		prompt = "";
		for(j in alter_pair_questions){
			var alters2 = $.extend(true,{}, alters);
			preface = new Object;
			preface.ANSWERTYPE = "PREFACE";
			preface.PROMPT = alter_pair_questions[j].PREFACE;
			for(k in alters){
				if(alter_pair_questions[j].SYMMETRIC){
					var keys = Object.keys(alters2);
					delete alters2[keys[0]];
				}
				var alter_pair_question_list = '';
				for(l in alters2){
		    		if(alters[k].ID == alters2[l].ID)
		    			continue;
					if(evalExpression(alter_pair_questions[j].ANSWERREASONEXPRESSIONID, alters[k].ID, alters2[l].ID) != true)
		    			continue;

    				if(parseInt(alter_pair_questions[j].ASKINGSTYLELIST) == 1){
    			    	alter_pair_question_list = alter_pair_questions[j];
    			    }else{
    					if(alter_pair_questions[j].PREFACE != "" && prompt == ""){
    			    		pages[i] = this.checkPage(i, pageNumber, "PREFACE");
                            prompt = alter_pair_questions[j].PREFACE;
    			    		i++;
    			    	}
    			    	pages[i] = this.checkPage(i, pageNumber, alter_pair_questions[j].TITLE + " - " + alters[k].NAME + "and" + alters2[l].NAME);
    			    	i++;
    			    }
    			    
		    	}
		    	if(alter_pair_question_list){
					if(preface.PROMPT != ""){
			    		pages[i] = this.checkPage(i, pageNumber, "PREFACE");
						preface.PROMPT = "";
			    		i++;
			    	}
			    	pages[i] = this.checkPage(i, pageNumber, alter_pair_question_list.TITLE + " - " + alters[k].NAME);
			    	i++;
				}
			}
		}

	}

	var network_question_list = '';
	for(j in network_questions){
	    if(interviewId){
	    	if(evalExpression(network_questions[j].ANSWERREASONEXPRESSIONID) != true)
	    		continue;
	    }

		if((parseInt(network_questions[j].ASKINGSTYLELIST) != 1 || prompt != network_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")) && network_question_list){
		    pages[i] = this.checkPage(i, pageNumber, network_question_list.TITLE);
			prompt = "";
		    network_question_list = '';
		    i++;
		}
		if(network_questions[j].PREFACE != ""){
			pages[i] = this.checkPage(i, pageNumber, "PREFACE");
			i++;
		}
		if(parseInt(network_questions[j].ASKINGSTYLELIST) == 1){
		    prompt = network_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"");
		    if(network_question_list == '')
			    network_question_list = network_questions[j];
		}else{
		    pages[i] = this.checkPage(i, pageNumber, network_questions[j].TITLE);
		    i++;
		}
	}

	if(network_question_list){
		pages[i] = this.checkPage(i, pageNumber, network_question_list.TITLE);
		network_question_list = '';
		i++;
	}

	pages[i] = this.checkPage(i, pageNumber, "CONCLUSION");
	return pages;
}
