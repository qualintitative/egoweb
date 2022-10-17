var app = angular.module('egowebApp', ['ngRoute', 'autocomplete']);
var masterList = [];
var evalQList = {};
var evalQIndex = [];
var currentPage = 0;
var alterPromptPage = false;
deletedPrevAlters = {};

app.config(function($routeProvider) {

    $routeProvider

        .when('/page/:page', {
        templateUrl: baseUrl + 'interview.html',
        controller: 'interviewController'
    })

    .when('/page/:page/:key', {
        templateUrl: baseUrl + 'interview.html',
        controller: 'interviewController'
    })

});

app.controller('interviewController', ['$scope', '$log', '$routeParams', '$sce', '$location', '$route', "saveAlter", "deleteAlter", function($scope, $log, $routeParams, $sce, $location, $route, saveAlter, deleteAlter) {
    for (xx in answers) {
        answers[xx].VALUE = answers[xx].VALUE.toString().replace(/[\u0000-\u001F]+/ig, "\n")
    }
    if (masterList.length == 0) {
        buildList();
        evalQuestions();
    }
    var style = document.createElement('style');
    style.innerHTML = study.STYLE;

    // Insert our new styles before the first script tag
    $('head').append(style);
    $scope.page = 0;
    if($routeParams.page != -1)
        $scope.page = $routeParams.page;
    $scope.questions = qFromList($scope.page)
    $scope.study = study;
    $scope.csrf = csrf;
    $scope.interviewId = interviewId;
    $scope.answers = $.extend(true, {}, answers);
    $scope.options = new Object;
    $scope.alters = $.extend(true, {}, alters);
    $scope.nGalters = []
    $scope.alterPrompt = "";
    $scope.askingStyleList = false;
    $scope.hideQ = false;
    $scope.subjectType = false;
    $scope.answerType = false;
    $scope.qId = "";
    $scope.prompt = "";
    $scope.setAllText = "Set All";
    $scope.alterName = "";
    $scope.dates = new Object;
    $scope.time_spans = new Object;
    $scope.graphId = "";
    $scope.graphExpressionId = "";
    $scope.graphInterviewId = "";
    $scope.graphQuestionId = "";
    $scope.graphNodes = "";
    $scope.graphParams = "";
    $scope.otherSpecify = {};
    $scope.otherGraphs = {};
    $scope.audioFiles = {};
    $scope.audio = {};
    $scope.keys = Object.keys;
    $scope.hashKey = "";
    $scope.interview = interview;
    $scope.header = $sce.trustAsHtml(study.HEADER);
    $scope.footer = $sce.trustAsHtml(study.FOOTER);
    $scope.phrase = "";
    $scope.conclusion = false;
    $scope.redirect = false;
    $scope.participants = [];
    $scope.listedAlters = {};
    $scope.prevAlters = prevAlters;
    $scope.starExpressionId = false;
    $scope.colspan = false;
    $scope.refuseCount = 0;
    $scope.hasRefuse = false;
    $scope.showPrevAlters = false;
    $scope.alterMatchName = "";
    $scope.errors = new Object;
    $scope.graphTitle = [];
    $scope.graphSize = [];
    $scope.nGraphs = [];
    current_array_ids = [];

    $(".interviewee").text(egoIdString);
    if (typeof $scope.questions[0] != "undefined" && $scope.questions[0].SUBJECTTYPE == "NAME_GENERATOR") {
        alterPromptPage = true;
        for (k in $scope.alters) {
            if ($scope.alters[k].INTERVIEWID.match(",")) {
                deletedPrevAlters[k] = $scope.alters[k];
            }
            if ($scope.alters[k].NAMEGENQIDS != null) {
                var nGs = $scope.alters[k].NAMEGENQIDS.split(",");
                if ($scope.alters[k].ORDERING.toString().match("{")) {
                    var nGorder = JSON.parse($scope.alters[k].ORDERING);
                } else {
                    for (var q in questions) {
                        if (questions[q].SUBJECTTYPE == "NAME_GENERATOR")
                            if ($scope.alters[k].ORDERING == "")
                                $scope.alters[k].ORDERING = 0;
                        var nGorder = {};
                        nGorder[questions[q].ID] = $scope.alters[k].ORDERING;

                    }

                }
                // we put alters in lists according to the name generator question id
                if (nGs.indexOf($scope.questions[0].ID.toString()) != -1) {
                   // if (typeof nGorder[$scope.questions[0].ID] != "undefined"){
                        //if(typeof $scope.nGalters[parseInt(nGorder[$scope.questions[0].ID])] == "undefined")
                        //    $scope.nGalters[parseInt(nGorder[$scope.questions[0].ID])] = $scope.alters[k];
                        //else 
                        var ordering = JSON.parse($scope.alters[k].ORDERING);
                        if(typeof ordering[$scope.questions[0].ID.toString()] != "undefined"){
                            var listOrder = ordering[$scope.questions[0].ID.toString()];
                            $scope.nGalters[parseInt(listOrder)] = $scope.alters[k];
                        }else{
                            $scope.nGalters.push($scope.alters[k]);
                        }
                          //  $scope.nGalters=    sortByKey($scope.nGalters);
                 //   }else{
                   //       $scope.nGalters.push($scope.alters[k]);
                    //}
                } else {
                    if (typeof $scope.listedAlters[k] == "undefined")
                        $scope.listedAlters[k] = alters[k];
                }
            }
        }
        console.log(alters, $scope.listedAlters);
        $scope.prevAlters = prevAlters;
        console.log($scope.nGalters);
    } else {
        alterPromptPage = false;
    }


    if (typeof hashKey != "undefined") {
        $scope.hashKey = hashKey;
    } else {
        if (typeof $routeParams.key != "undefined") {
            $scope.hashKey = $routeParams.key;
            hashKey = $routeParams.key;
        }
    }

    if (typeof redirect !== 'undefined' && redirect)
        $scope.redirect = redirect;

    for (k in audio) {
        $scope.audio[k] = audio[k];
        $scope.audioFiles[k] = new Audio();
        $scope.audioFiles[k].src = audio[k];
    }

    navFromList($scope.page, $scope);
    //$scope.nav = buildNav($scope.page, $scope);

    if (!isGuest) {
        $('#navbox ul').html("");
        for (k in $scope.nav) {
            if (baseUrl == "/www/")
                $('#navbox ul').append("<li class='dropdown-submenu' id='menu_" + k + "'><a class='dropdown-item' href='/interview/" + study.ID + (interviewId ? "/" + interviewId : "") + "#page/" + k + "'>" + $scope.nav[k] + "</a></li>");
            else
                $('#navbox ul').append("<li class='dropdown-submenu' id='menu_" + k + "'><a class='dropdown-item' href='" + $location.absUrl().replace($location.url(), '') + "page/" + k + "'>" + $scope.nav[k] + "</a></li>");
        }
        if ($("#menu_" + $scope.page).length > 0)
            $("#second").scrollTop($("#second").scrollTop() - $("#second").offset().top + $("#menu_" + $scope.page).offset().top);
    }
    if($("#second li").length != 0 && $routeParams.page == -1){
        var nextUrl =  rootUrl + "/interview/" + study.ID + "/" + interviewId + "#/page/" + ($("#second li").length - 1);
        document.location = nextUrl;
    }

    for (var k in $scope.questions) {
        if(typeof interviewId == "undefined" && $scope.questions[k].SUBJECTTYPE != "EGO_ID"){
            $scope.errors[0] = 'The this interview is currently set to provide an ID from an external platform or url. The interview cannot be initiated within the EgoWeb platform. Please change this setting or initiate this interview externally. Settings can be changed in study authoring by deselecting Hide Ego Id Page (for studies will Ego Id prefills)';
        }
        var array_id = $scope.questions[k].array_id;
        current_array_ids.push(array_id);
        if(!$scope.questions[k].DONTKNOWTEXT)
            $scope.questions[k].DONTKNOWTEXT = "Don't Know";
        if(!$scope.questions[k].REFUSETEXT)
            $scope.questions[k].REFUSETEXT = "Refuse";
        if($scope.questions[k].SETALLTEXT)
            $scope.setAllText = $scope.questions[k].SETALLTEXT;
        if ($scope.questions[k].USEALTERLISTFIELD == "name" || $scope.questions[k].USEALTERLISTFIELD == "email") {
            for (p in participantList) {
                var qIds = [];
                if (participantList[p].NAMEGENQIDS && participantList[p].NAMEGENQIDS.match(","))
                    qIds = participantList[p].NAMEGENQIDS.split(",");
                else if (participantList[p].NAMEGENQIDS)
                    qIds.push(participantList[p].NAMEGENQIDS);
                if (qIds.length != 0) {
                    if ($.inArray($scope.questions[k].ID.toString(), qIds) != -1 && $.inArray(participantList[p][$scope.questions[k].USEALTERLISTFIELD.toUpperCase()], $scope.participants) == -1) {
                        $scope.participants.push(participantList[p][$scope.questions[k].USEALTERLISTFIELD.toUpperCase()]);
                    } else if ($scope.questions[k].SUBJECTTYPE == "EGO_ID") {
                        $scope.participants.push(participantList[p][$scope.questions[k].USEALTERLISTFIELD.toUpperCase()]);
                    }
                } else {
                    if ($.inArray(participantList[p][$scope.questions[k].USEALTERLISTFIELD.toUpperCase()], $scope.participants) == -1)
                        $scope.participants.push(participantList[p][$scope.questions[k].USEALTERLISTFIELD.toUpperCase()]);
                }
            }
        }
        if(($scope.questions[k].RESTRICTPREV == true || $scope.questions[k].AUTOCOMPLETEPREV == true) && Object.keys($scope.prevAlters).length > 0) {
            for (n in $scope.prevAlters) {
                //if (study.RESTRICTALTERS) {
                    if ($scope.participants.indexOf($scope.prevAlters[n].NAME) == -1)
                        $scope.participants.push($scope.prevAlters[n].NAME);
               // } else {
                //    $scope.participants.push($scope.prevAlters[n].NAME);
               // }
            }
        }
      //  if(($scope.questions[0].RESTRICTLIST == true || $scope.questions[0].AUTOCOMPLETELIST == true) && Object.keys($scope.listedAlters).length > 0) {
        //    for (n in $scope.listedAlters) {
                //if (study.RESTRICTALTERS) {
          //          if ($.inArray($scope.listedAlters[n].NAME, $scope.participants) == -1)
            //            $scope.participants.push($scope.listedAlters[n].NAME);
               // } else {
                //    $scope.participants.push($scope.listedAlters[n].NAME);
                //}
       //     }
        //}
        if ($scope.questions[k].ALTERID1 && typeof alters[parseInt($scope.questions[k].ALTERID1)] != "undefined") {
            $scope.alterName = alters[parseInt($scope.questions[k].ALTERID1)].NAME;
            console.log("alter name", $scope.alterName)
        }

        if ($scope.questions[k].ALTERID2 && typeof prevAlters[parseInt($scope.questions[k].ALTERID2)] != "undefined") {
            $scope.alterMatchName = prevAlters[parseInt($scope.questions[k].ALTERID2)].NAME;
            console.log("alter match name", $scope.alterMatchName)
        }

        if (typeof $scope.questions[k].CITATION == "string")
            $scope.questions[k].CITATION = $sce.trustAsHtml(interpretTags($scope.questions[k].CITATION, $scope.questions[k].ALTERID1, $scope.questions[k].ALTERID2));

        if ($scope.questions[k].ALLBUTTON == true && !$scope.options["all"]) {
            $scope.options['all'] = $.extend(true, {}, options[$scope.questions[k].ID]);
            if ($scope.questions[k].DONTKNOWBUTTON == true) {
                var button = new Object;
                button.NAME = $scope.questions[k].DONTKNOWTEXT;
                button.ID = "DONT_KNOW";
                button.checked = false;
                $scope.options['all'][Object.keys($scope.options['all']).length] = button;
            }

            if ($scope.questions[k].REFUSEBUTTON == true) {
                var button = new Object;
                button.NAME = $scope.questions[k].REFUSETEXT;
                button.ID = "REFUSE";
                button.checked = false;
                $scope.options['all'][Object.keys($scope.options['all']).length] = button;
            }
        }
        $scope.options[array_id] = $.extend(true, {}, options[$scope.questions[k].ID]);
        if ($scope.questions[k].ASKINGSTYLELIST != false)
            $scope.askingStyleList = $scope.questions[k].array_id;
        if ($scope.askingStyleList != false)
            $scope.fixedWidth = "auto";
        else
            $scope.fixedWidth = "auto";

        if ($scope.subjectType == false) {
            $scope.subjectType = $scope.questions[k].SUBJECTTYPE;
            $scope.answerType = $scope.questions[k].ANSWERTYPE;;
            if (typeof $scope.questions[k].ID != "undefined")
                $scope.qId = $scope.questions[k].ID;
        }

        if ($scope.questions[k].ANSWERTYPE == "PREFACE") {
            $scope.hideQ = true;
            if (study.USEASALTERS == true) {
                $scope.participants = participantList['name'];
            }
        }

        if ($scope.questions[k].SUBJECTTYPE == "NAME_GENERATOR") {
            $scope.showPrevAlters = $scope.questions[k].KEEPONSAMEPAGE == 1 ? true : false;
            
            if (typeof alterPrompts[$scope.questions[k].ID] != "undefined" && typeof alterPrompts[$scope.questions[k].ID][Object.keys($scope.nGalters).length] != "undefined")
                $scope.alterPrompt = alterPrompts[$scope.questions[k].ID][Object.keys($scope.nGalters).length];
        }
        for (o in $scope.options[array_id]) {
            $scope.options[array_id][o].checked = false;
            if (typeof answers[array_id] != "undefined") {
                var values = answers[array_id].VALUE.split(',');
                if (values.indexOf($scope.options[array_id][o].ID.toString()) != -1)
                    $scope.options[array_id][o].checked = true;
            }
        }
        if (typeof $scope.answers[array_id] == "undefined") {
            $scope.answers[array_id] = new Object;
            $scope.answers[array_id].VALUE = "";
            $scope.answers[array_id].INTERVIEWID = interviewId;
            $scope.answers[array_id].SKIPREASON = "NONE";
        } else {
            if ($scope.answers[array_id].VALUE == study.VALUELOGICALSKIP || $scope.answers[array_id].VALUE == study.VALUENOTYETANSWERED)
                $scope.answers[array_id].VALUE = "";
        }
        if ($scope.questions[k].ANSWERTYPE == "TIME_SPAN") {
            $scope.time_spans[array_id] = new Object;
            if (answers[array_id] && answers[array_id].VALUE.match(/(\d*)\sYEARS/i))
                $scope.time_spans[array_id].YEARS = answers[array_id].VALUE.match(/(\d*)\sYEARS/i)[1];
            if (answers[array_id] && answers[array_id].VALUE.match(/(\d*)\sMONTHS/i))
                $scope.time_spans[array_id].MONTHS = answers[array_id].VALUE.match(/(\d*)\sMONTHS/i)[1];
            if (answers[array_id] && answers[array_id].VALUE.match(/(\d*)\sWEEKS/i))
                $scope.time_spans[array_id].WEEKS = answers[array_id].VALUE.match(/(\d*)\sWEEKS/i)[1];
            if (answers[array_id] && answers[array_id].VALUE.match(/(\d*)\sDAYS/i))
                $scope.time_spans[array_id].DAYS = answers[array_id].VALUE.match(/(\d*)\sDAYS/i)[1];
            if (answers[array_id] && answers[array_id].VALUE.match(/(\d*)\sHOURS/i))
                $scope.time_spans[array_id].HOURS = answers[array_id].VALUE.match(/(\d*)\sHOURS/i)[1];
            if (answers[array_id] && answers[array_id].VALUE.match(/(\d*)\sMINUTES/i))
                $scope.time_spans[array_id].MINUTES = answers[array_id].VALUE.match(/(\d*)\sMINUTES/i)[1];
        }

        if ($scope.questions[k].ANSWERTYPE == "DATE" && typeof answers[array_id] != "undefined") {
            $scope.dates[array_id] = new Object;
            var date = answers[array_id].VALUE.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
            var time = answers[array_id].VALUE.match(/(\d{1,2}):(\d{1,2}) (AM|PM)/);
            if (date && date.length > 3) {
                $scope.dates[array_id].YEAR = date[3];
                $scope.dates[array_id].MONTH = date[1];
                $scope.dates[array_id].DAY = date[2];
            }
            if (time && time.length > 2) {
                $scope.dates[array_id].HOUR = time[1];
                $scope.dates[array_id].MINUTE = time[2];
            }
            if (time && time.length > 3)
                $scope.dates[array_id].AMPM = time[3];
            else
                $scope.dates[array_id].AMPM = "";
        }

        if ($scope.questions[k].ANSWERTYPE == "MULTIPLE_SELECTION") {
            $scope.phrase = "Please select ";
            if ($scope.questions[k].MINCHECKABLEBOXES != null && $scope.questions[k].MINCHECKABLEBOXES != null && $scope.questions[k].MAXCHECKABLEBOXES != "" && $scope.questions[k].MINCHECKABLEBOXES == $scope.questions[k].MAXCHECKABLEBOXES)
                $scope.phrase += $scope.questions[k].MAXCHECKABLEBOXES;
            else if ($scope.questions[k].MINCHECKABLEBOXES != null && $scope.questions[k].MAXCHECKABLEBOXES != null && $scope.questions[k].MINCHECKABLEBOXES != $scope.questions[k].MAXCHECKABLEBOXES)
                $scope.phrase += $scope.questions[k].MINCHECKABLEBOXES + " to " + $scope.questions[k].MAXCHECKABLEBOXES;
            else if ($scope.questions[k].MINCHECKABLEBOXES == null && $scope.questions[k].MAXCHECKABLEBOXES != null)
                $scope.phrase += " up to " + $scope.questions[k].MAXCHECKABLEBOXES;
            if ($scope.questions[k].MINCHECKABLEBOXES != null && $scope.questions[k].MAXCHECKABLEBOXES == null) {
                $scope.phrase += " all that apply" //" at least " + $scope.questions[k].MINCHECKABLEBOXES;
            } else {
                if ($scope.questions[k].MAXCHECKABLEBOXES > 1 || $scope.questions[k].MINCHECKABLEBOXES > 1)
                    $scope.phrase += " responses";
                else
                    $scope.phrase += " response";
            }
            if ($scope.questions[k].ASKINGSTYLELIST == 1 && $scope.questions[k].WITHLISTRANGE == false)
                $scope.phrase += " for each row";
        }

        if ($scope.questions[k].ANSWERTYPE == "NUMERICAL" && $scope.questions[k].SUBJECTTYPE != "EGO_ID") {
            var min = "";
            var max = "";
            if ($scope.questions[k].MINLIMITTYPE == "NLT_LITERAL") {
                min = $scope.questions[k].MINLITERAL;
            } else if ($scope.questions[k].MINLIMITTYPE == "NLT_PREVQUES") {
                if (typeof answers[$scope.questions[k].MINPREVQUES] != "undefined")
                    min = answers[$scope.questions[k].MINPREVQUES].VALUE;
                else
                    min = "";
            }
            if ($scope.questions[k].MAXLIMITTYPE == "NLT_LITERAL") {
                max = $scope.questions[k].MAXLITERAL;
            } else if ($scope.questions[k].MAXLIMITTYPE == "NLT_PREVQUES") {
                if (typeof answers[$scope.questions[k].MAXPREVQUES] != "undefined")
                    max = answers[$scope.questions[k].MAXPREVQUES].VALUE;
                else
                    max = "";
            }

            if (min != "" && max != "")
                $scope.phrase = "Please enter a number from " + min + " to " + max;
            else if (min == "" && max != "")
                $scope.phrase = "Please enter a number (" + max + " or lower)";
            else if (min != "" && max == "")
                $scope.phrase = "Please enter a number (" + min + " or higher)";
            if ($scope.questions[k].ASKINGSTYLELIST == 1 && $scope.questions[k].WITHLISTRANGE == false && $scope.phrase != "" && !$scope.phrase.match("for each row"))
                $scope.phrase += " for each row";
        }
        if ($scope.questions[k].ANSWERTYPE == "TEXTUAL" || $scope.questions[k].ANSWERTYPE == "TEXTUAL_PP")
            $scope.answers[array_id].VALUE = htmldecode($scope.answers[array_id].VALUE);

        if ($scope.questions[k].DONTKNOWBUTTON == true) {
            var button = new Object;
            button.NAME = $scope.questions[k].DONTKNOWTEXT;
            button.ID = "DONT_KNOW";
            button.checked = false;
            if ($scope.answers[array_id].SKIPREASON == "DONT_KNOW")
                button.checked = true;
            $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
        }

        if ($scope.questions[k].REFUSEBUTTON == true) {
            var button = new Object;
            button.NAME = $scope.questions[k].REFUSETEXT;
            button.ID = "REFUSE";
            $scope.hasRefuse = true;
            button.checked = false;
            if ($scope.answers[array_id].SKIPREASON == "REFUSE")
                button.checked = true;
            $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
        }

        if ($scope.questions[k].SUBJECTTYPE == "MERGE_ALTER") {
            var button = new Object;
            $scope.questions[k].MAXCHECKABLEBOXES = 1;
            var allOptions = JSON.parse($scope.questions[k].ALLOPTIONSTRING);
            button.NAME = allOptions["YES_LABEL"];
            button.ID = "MATCH";
            button.checked = false;
            $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
            var button = new Object;
            button.NAME = allOptions["NO_LABEL"];
            button.ID = "UNMATCH";
            button.checked = false;
            if ($scope.alterName.trim().toLowerCase() == $scope.alterMatchName.trim().toLowerCase())
                button.OTHERSPECIFY = true
            $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
            if(typeof allOptions["NEW_NAME_LABEL"] != "undefined" && allOptions["NEW_NAME_LABEL"] != ""){
                var button = new Object;
                button.NAME = allOptions["NEW_NAME_LABEL"];
                button.ID = "NEW_NAME";
                button.checked = false;
                if ($scope.alterName.trim().toLowerCase() == $scope.alterMatchName.trim().toLowerCase())
                    button.OTHERSPECIFY = true;
                $scope.options[array_id][Object.keys($scope.options[array_id]).length] = button;
            }
        }
        if ($scope.colspan == false) {
            $scope.colspan = 1
            $scope.colspan = $scope.colspan + Object.keys($scope.options[array_id]).length;
            if ($scope.askingStyleList != false && ($scope.questions[k].ANSWERTYPE == "NUMERICAL" || $scope.questions[k].ANSWERTYPE == "TEXTUAL")) {
                $scope.colspan = $scope.colspan + 1;
            }
        }
        if (typeof $scope.answers[array_id].OTHERSPECIFYTEXT != "undefined" && $scope.answers[array_id].OTHERSPECIFYTEXT != null && $scope.answers[array_id].OTHERSPECIFYTEXT != "") {
            $scope.otherSpecify[array_id] = {};
            var specify = $scope.answers[array_id].OTHERSPECIFYTEXT.split(";;");
            for (s in specify) {
                var pair = specify[s].split(":");
                //        $scope.otherSpecify[array_id][pair[0]] = pair[1];
                $scope.otherSpecify[array_id][pair[0]] = htmldecode(pair[1]);
            }
        }
        for (a in $scope.options[array_id]) {
            if (typeof $scope.otherSpecify[array_id] == "undefined")
                $scope.otherSpecify[array_id] = {};
            if ($scope.otherSpecify[array_id][$scope.options[array_id][a].ID] && $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] != "")
                continue;
            if ($scope.options[array_id][a].OTHERSPECIFY == true)
                $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] = "";
            else if ($scope.options[array_id][a].NAME.match(/OTHER \(*SPECIFY\)*/i))
                $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] = "";
            else
                $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] = false;
        }
        //console.log("other spec", $scope.otherSpecify);
        if ($scope.questions[k].SUBJECTTYPE != "EGO_ID") {
            $scope.prompt = $sce.trustAsHtml(interpretTags($scope.questions[k].PROMPT, $scope.questions[k].ALTERID1, $scope.questions[k].ALTERID2) + '<br><div class="orangeText">' + $scope.phrase + "</div>");
        } else {
            $scope.prompt = $sce.trustAsHtml(study.EGOIDPROMPT);
            $scope.questions[k].PROMPT = $scope.questions[k].PROMPT.replace(/(<([^>]+)>)/ig, '');
        }

        if ($scope.questions[k].SUBJECTTYPE == "NETWORK") {
            var expressionId = $scope.questions[k].NETWORKRELATIONSHIPEXPRID;
            notes = [];
            s = [];
            $scope.graphExpressionId = $scope.questions[k].NETWORKRELATIONSHIPEXPRID;
            $scope.graphQuestionId = $scope.questions[k].ID;
            if (typeof otherGraphs[$scope.questions[k].TITLE] != "undefined")
                $scope.otherGraphs = otherGraphs[$scope.questions[k].TITLE];
            if (typeof graphs[expressionId] != "undefined") {
                $scope.graphId = graphs[expressionId].ID;
                //$scope.graphExpressionId = graphs[expressionId].EXPRESSIONID;
                graphExpressionId = $scope.graphExpressionId;
                $scope.graphInterviewId = graphs[expressionId].INTERVIEWID;
                $scope.graphNodes = graphs[expressionId].NODES;
                $scope.graphParams = $scope.questions[k].NETWORKPARAMS;
                if (typeof allNotes[expressionId] != "undefined")
                    notes = allNotes[expressionId];
            } else {
                graphExpressionId = $scope.graphExpressionId;
                if (typeof allNotes[graphExpressionId] != "undefined")
                    notes = allNotes[graphExpressionId];
                $scope.graphInterviewId = interviewId;
                $scope.graphParams = $scope.questions[k].NETWORKPARAMS;

            }
            if ($scope.questions[k].USELFEXPRESSION && parseInt($scope.questions[k].USELFEXPRESSION) != 0)
                $scope.starExpressionId = parseInt($scope.questions[k].USELFEXPRESSION);
            initStats($scope.questions[k]);

        }
        if($scope.questions[k].SUBJECTTYPE == "MULTI_GRAPH"){
            notes = [];
            s = [];
            $scope.nGraphs = JSON.parse($scope.questions[k].NETWORKGRAPHS);
            for(var g = 0; g <  $scope.nGraphs.length; g++){
                if($scope.nGraphs[g].questionId && $scope.nGraphs[g].questionId != "")
                initStats(questions[parseInt($scope.nGraphs[g].questionId)], "infovis" + g, 1);
            }
        }
        setTimeout(
            function() {
                eval($scope.questions[k].JAVASCRIPT);
                if (typeof $(".answerInput")[0] != "undefined")
                    $(".answerInput")[0].focus();
                if (!isGuest && $("#menu_" + $scope.page).length != 0)
                    $("#second").scrollTop($("#second").scrollTop() - $("#second").offset().top + $("#menu_" + $scope.page).offset().top);
            },
            1);
    }

    setTimeout(function() {

        if ($scope.askingStyleList != false && $(window).width() > 768) {
            console.log("fixing header")
            $("table.qTable").floatThead({ top: parseInt($("#content").css("margin-top")) })
            window.scrollTo(0, 0);
            $(window).resize();
        } else {
            //    $("#realHeader").css("display","none");
            //$("#floater").hide();
            //unfixHeader();
            //$("#realHeader").css("height","1px");

            //      $("#realHeader").height(1);
        }
        //$(window).scrollTop(0);
        window.scrollTo(0, 0);
        eval(study.JAVASCRIPT);
    }, 100);

    $scope.print = function(i_Id, g_Id, q_Id) {
        var expressionId = $scope.graphExpressionId;
        console.log(g_Id, graphs[expressionId])
        if (g_Id == "" && typeof graphs[expressionId] != "undefined")
            g_Id = graphs[expressionId].ID;
        url = "/interview/graph/" + i_Id + "/" + g_Id + "/" + q_Id;
        window.open(url);
    }

    $scope.playSound = function(file) {
        $scope.audioFiles[file].play();
    }

    $scope.goBack = function() {
        var url = $location.absUrl().replace($location.url(), '');
        url = url + "page/" + (parseInt($scope.page) - 1);
        if (typeof hashKey != "undefined")
            url = url + "/" + hashKey;
        document.location = url;
    }

    $scope.submitForm = function(isValid) {
        // check to make sure the form is completely valid
        console.log(isValid)
        if (isValid || $scope.refuseCount > 0) {
            for (r in current_array_ids) {
                if ($scope.refuseCount > 0 && $scope.answers[current_array_ids[r]].SKIPREASON == "NONE" && $('#Answer_' + current_array_ids[r] + '_VALUE').val() == "") {
                    $scope.answers[current_array_ids[r]].SKIPREASON = "REFUSE";
                    $('input[name="Answer[' + current_array_ids[r] + '][skipReason]').val("REFUSE");
                    for(o in $scope.options[array_id]){
                        if($scope.options[array_id][o].ID == "REFUSE"){
                            $("label[for='multiselect-"+ array_id+"_" + o + "']").click();
                            //$scope.options[array_id][o].checked = true;
                        }
                    }
                }
            }
            $scope.answerForm.$setDirty();
            save($scope.questions, $scope.page, $location.absUrl().replace($location.url(), ''), $scope);
        } else {
            if ($scope.hasRefuse)
                $scope.refuseCount++;
            window.scrollTo(0, 0);
            $("table.qTable").floatThead('reflow');
        }
    };

    $scope.addAlter = function(isValid) {
        $scope.errors[0] = false;
        $("#Alters_name").val($("#Alters_name").val().trim());
        for (k in $scope.alters) {
            if ($("#Alters_name").val().toLowerCase() == $scope.alters[k].NAME.toLowerCase()) {
                // dis-allow names entered from previous name generators in current interview
                if ($scope.questions[0].NONEBUTTON != true)
                    $scope.errors[0] = 'That name has already been listed';
                var nameGenQIds = [];
                if ($scope.alters[k].NAMEGENQIDS != null)
                    nameGenQIds = $scope.alters[k].NAMEGENQIDS.split(",");
                if (nameGenQIds.indexOf($("#Alters_nameGenQIds").val()) > -1)
                    $scope.errors[0] = 'That name is already on this list';
            }
        }

        // check pre-defined participant list
        if ($scope.participants.length > 0 && ($scope.questions[0].RESTRICTLIST == true || $scope.questions[0].RESTRICTPREV == true)) {
            if ($scope.participants.indexOf($("#Alters_name").val().trim()) == -1 || $scope.participants.length == 0) {
                $scope.errors[0] = 'Name not found in list';
            }
        }

        if ($("#Alters_name").val().trim() == "") {
            $scope.errors[0] = 'Name cannot be blank';
        }

        // check to make sure the form is completely valid
        if ($scope.errors[0] == false) {
            $('.alterSubmit').prop("disabled", true);
            saveAlter.getAlters().then(function(data) {
                alters = JSON.parse(data);
                console.log(alters);
                for (k in alters) {
                    if (typeof prevAlters[k] != "undefined") {
                        deletedPrevAlters[k] = $.extend(true, {}, prevAlters[k]);
                        delete prevAlters[k];
                    }
                }
                for (k in alters) {
                    if (typeof $scope.listedAlters[k] != "undefined")
                        delete $scope.listedAlters[k];
                }
                masterList = [];
                $route.reload();
            });
        }
    };

    $scope.removeAlter = function(alterId, nameGenQId) {
        $("#deleteAlterId").val(alterId);
        $("#deleteNameGenQId").val(nameGenQId);
        $('.alterSubmit').prop("disabled", true);
        // check to make sure the form is completely valid
        deleteAlter.getAlters().then(function(data) {
            alters = JSON.parse(data);
            if (typeof deletedPrevAlters[alterId] != "undefined" && typeof prevAlters[alterId] == "undefined" && typeof alters[alterId] == "undefined") {
                prevAlters[alterId] = $.extend(true, {}, deletedPrevAlters[alterId]);
                let nameGenQIds = prevAlters[alterId].NAMEGENQIDS.split(",");
                let newNameGenQIds = [];
                for(k in nameGenQIds){
                    if(nameGenQIds[k] != nameGenQId)
                        newNameGenQIds.push(nameGenQIds[k]);
                }
                prevAlters[alterId].NAMEGENQIDS = newNameGenQIds.join(",");
                $scope.prevAlters = prevAlters;

                delete deletedPrevAlters[alterId];
            }
            masterList = [];
            $route.reload();
        });
    };

    $scope.unSkip = function(array_id) {
        if (typeof $scope.answers[array_id].VALUE != "undefined" && $scope.answers[array_id].VALUE != "" && $scope.answers[array_id].VALUE != "SKIPREASON") {
            for (k in $scope.options[array_id]) {
                $scope.options[array_id][k].checked = false;
            }
            $scope.answers[array_id].SKIPREASON = "NONE";
        }
    }

    $scope.changeOther = function(array_id) {
        var specify = [];
        for (a in $scope.options[array_id]) {
            if ($scope.otherSpecify[array_id][$scope.options[array_id][a].ID] != false && $scope.otherSpecify[array_id][$scope.options[array_id][a].ID] != "") {
                specify.push($scope.options[array_id][a].ID + ":" + $scope.otherSpecify[array_id][$scope.options[array_id][a].ID])
            }
        }
        $scope.answers[array_id].OTHERSPECIFYTEXT = specify.join(";;");
    }

    $scope.multiSelect = function(v, index, array_id) {
        //  if(1 == 1){
        //   alert('g')
        if (v == "UNMATCH" || v == "NEW_NAME") {
            if ($scope.options[array_id][index].checked) {
                if ($scope.alterName.trim().toLowerCase() == $scope.alterMatchName.trim().toLowerCase())
                    $scope.errors[array_id] = "Please modify the name so it's not identical to the previous name entered.";
            } else {
                delete $scope.errors[array_id];
            }
        } else if (v == "MATCH") {
            delete $scope.errors[array_id];
        }
        if (typeof $scope.questions[array_id] != "undefined")
            var question = $scope.questions[array_id];
        else
            var question = questions[$scope.options[array_id][index].QUESTIONID];
        if ($scope.answers[array_id].VALUE)
            values = $scope.answers[array_id].VALUE.split(',');
        else
            values = [];

        if (v == "DONT_KNOW" || v == "REFUSE") {
            if ($scope.options[array_id][index].checked) {
                for (k in $scope.options[array_id]) {
                    if (k != index)
                        $scope.options[array_id][k].checked = false;
                }
                $scope.answers[array_id].OTHERSPECIFYTEXT = "";
                $scope.answers[array_id].SKIPREASON = v;
                if (typeof $scope.dates[array_id] != "undefined") {
                    $scope.dates[array_id].MINUTE = "";
                    $scope.dates[array_id].HOUR = "";
                    $scope.dates[array_id].DAY = "";
                    $scope.dates[array_id].MONTH = "";
                    $scope.dates[array_id].AMPM = "";
                    $scope.dates[array_id].YEAR = "";
                }
                if (typeof $scope.errors[array_id] != "undefined")
                    delete $scope.errors[array_id];
                if (typeof $scope.errors[0] != "undefined")
                    delete $scope.errors[0];
                $('#Answer_' + array_id + '_VALUE').val("SKIPREASON").change();
                $('#Answer_' + array_id + '_VALUE').val("").change();
            } else {
                $scope.answers[array_id].SKIPREASON = "NONE";
                $('#Answer_' + array_id + '_VALUE').val("SKIPREASON").change();
                $('#Answer_' + array_id + '_VALUE').val("").change();
            }
        } else {
            if ($scope.options[array_id][index].checked) {
                $scope.answers[array_id].SKIPREASON = "NONE";
                delete $scope.errors[array_id];
                console.log($scope.answerForm.$submitted, index, $scope.errors[array_id])
                for (k in $scope.options[array_id]) {
                    if ($scope.options[array_id][k].ID == "DONT_KNOW" || $scope.options[array_id][k].ID == "REFUSE")
                        $scope.options[array_id][k].checked = false;
                    if ($scope.options[array_id][index].SINGLE == true && k != index)
                        $scope.options[array_id][k].checked = false;
                    if ($scope.options[array_id][k].SINGLE == true && k != index) {
                        $scope.options[array_id][k].checked = false;
                        if (values.indexOf($scope.options[array_id][k].ID) != -1) {
                            values.splice(values.indexOf($scope.options[array_id][k].ID), 1);
                        }
                    }

                }
                if ($scope.options[array_id][index].SINGLE == true)
                    values = [v.toString()];
                if (values.indexOf(v.toString()) == -1)
                    values.push(v.toString());
            } else {
                if ($scope.otherSpecify[$scope.options[array_id][index].ID] != false) {
                    $scope.otherSpecify[$scope.options[array_id][index].ID] = "";
                    $scope.changeOther(array_id);
                }
                if (values.indexOf(v.toString()) != -1) {
                    values.splice(values.indexOf(v), 1);
                }
            }
            if (question.MAXCHECKABLEBOXES != null && values.length > question.MAXCHECKABLEBOXES) {
                value = values.shift();
                for (k in $scope.options[array_id]) {
                    if ($scope.options[array_id][k].ID == value)
                        $scope.options[array_id][k].checked = false;
                }
            }
            $scope.answers[array_id].VALUE = values.join(',');
        }

    }

    $scope.setAll = function(v, index) {
        for (k in $scope.questions) {
            var array_id = $scope.questions[k].array_id;
            if ($scope.answers[array_id].VALUE == undefined)
                $scope.answers[array_id].VALUE = "";
            if (
                ($scope.answers[array_id].VALUE == "" && $scope.answers[array_id].SKIPREASON == "NONE" && $scope.options['all'][index].checked == true) ||
                ((($scope.answers[array_id].VALUE != "" && $.inArray(v.toString(), $scope.answers[array_id].VALUE.split(",")) != -1) || ($scope.answers[array_id].SKIPREASON != "" && $.inArray(v.toString(), $scope.answers[array_id].SKIPREASON.split(",")) != -1)) && $scope.options['all'][index].checked == false)

            ) {
                $scope.options[array_id][index].checked = $scope.options['all'][index].checked;
                $scope.multiSelect(v, index, k);
            }
        }
    }

    $scope.timeValue = function(array_id) {
        var date = [];
        $scope.answers[array_id].VALUE = "";
        //alert($scope.time_spans[array_id].YEARS)
        if (!isNaN($scope.time_spans[array_id].YEARS) && $scope.time_spans[array_id].YEARS)
            date.push($scope.time_spans[array_id].YEARS + ' YEARS');
        if (!isNaN($scope.time_spans[array_id].MONTHS) && $scope.time_spans[array_id].MONTHS)
            date.push($scope.time_spans[array_id].MONTHS + ' MONTHS');
        if (!isNaN($scope.time_spans[array_id].WEEKS) && $scope.time_spans[array_id].WEEKS)
            date.push($scope.time_spans[array_id].WEEKS + ' WEEKS');
        if (!isNaN($scope.time_spans[array_id].DAYS) && $scope.time_spans[array_id].DAYS)
            date.push($scope.time_spans[array_id].DAYS + ' DAYS');
        if (!isNaN($scope.time_spans[array_id].HOURS) && $scope.time_spans[array_id].HOURS)
            date.push($scope.time_spans[array_id].HOURS + ' HOURS');
        if (!isNaN($scope.time_spans[array_id].MINUTES) && $scope.time_spans[array_id].MINUTES)
            date.push($scope.time_spans[array_id].MINUTES + ' MINUTES');
        if (date.length > 0) {
            $scope.answers[array_id].VALUE = date.join("; ");
            //alert($scope.answers[array_id].VALUE)
            $scope.answers[array_id].SKIPREASON = "NONE";
            for (k in $scope.options[array_id]) {
                if ($scope.options[array_id][k].ID == "DONT_KNOW" || $scope.options[array_id][k].ID == "REFUSE")
                    $scope.options[array_id][k].checked = false;
            }
        }
    }

    $scope.dateValue = function(array_id) {
        var date = "";
        if ($scope.dates[array_id].MONTH)
            date += $scope.dates[array_id].MONTH + ' ';
        if ($scope.dates[array_id].DAY)
            date += $scope.dates[array_id].DAY + ' ';
        if (!isNaN($scope.dates[array_id].YEAR))
            date += $scope.dates[array_id].YEAR + ' ';
        if (!isNaN($scope.dates[array_id].HOUR))
            date += $scope.dates[array_id].HOUR + ':';
        if (!isNaN($scope.dates[array_id].MINUTE)) {
            if ($scope.dates[array_id].MINUTE.toString().length < 2)
                $scope.dates[array_id].MINUTE = '0' + $scope.dates[array_id].MINUTE;
            date += $scope.dates[array_id].MINUTE + ' ';
        }
        if ($scope.dates[array_id].AMPM)
            date += $scope.dates[array_id].AMPM;
        $scope.answers[array_id].VALUE = date;
        $scope.answers[array_id].SKIPREASON = "NONE";
        for (k in $scope.options[array_id]) {
            if ($scope.options[array_id][k].ID == "DONT_KNOW" || $scope.options[array_id][k].ID == "REFUSE")
                $scope.options[array_id][k].checked = false;
        }

    }

    $scope.timeBits = function(timeUnits, span) {
        timeArray = [];
        bitVals = {
            'BIT_YEAR': 1,
            'BIT_MONTH': 2,
            'BIT_WEEK': 4,
            'BIT_DAY': 8,
            'BIT_HOUR': 16,
            'BIT_MINUTE': 32,
        };
        for (var k in bitVals) {
            if (timeUnits & bitVals[k]) {
                timeArray.push(k);
            }
        }

        if ($.inArray("BIT_" + span, timeArray) != -1)
            return true;
        else
            return false;
    }
}]);

app.directive('checkAnswer', [function() {
    return {
        require: 'ngModel',
        link: function(scope, elem, attr, ngModel) {
            //For DOM . model validation
            ngModel.$parsers.unshift(function(value) {
                var valid = true;
                var array_id = attr.arrayId;
                var question = questions[attr.questionId];
                console.log(question);
                console.log("parsers check:" + value);

                if (attr.answerType == "NAME_GENERATOR") {
                    if ((typeof scope.answers[array_id] != "undefined" && scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW" || typeof scope.answers[array_id] == "undefined") && Object.keys(scope.nGalters).length < scope.questions[0].MINLITERAL) {
                        var noun = " people";
                        if (scope.questions[0].MINLITERAL == 1)
                            noun = " person";
                        scope.errors[array_id] = 'Please list at least ' + scope.questions[0].MINLITERAL + noun + ".";
                        valid = false;
                    } else {
                        delete scope.errors[0];
                        delete scope.errors[array_id];
                        delete scope.answerForm.$error.checkAnswer;
                    }
                }

                if (attr.answerType == "TEXTUAL") {
                    if (scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW") {
                        if (value == "") {
                            scope.errors[array_id] = "Value cannot be blank.";
                            valid = false;
                        } else {
                            delete scope.errors[array_id];
                        }
                    } else {
                        delete scope.errors[array_id];
                    }
                }

                if (attr.answerType == "NUMERICAL") {
                    var min = "";
                    var max = "";
                    var numberErrors = 0;
                    var showError = false;
                    if ((value == "" && scope.answers[array_id].SKIPREASON == "NONE") || (value != "" && isNaN(value))) {
                        errorMsg = 'Please enter a number.';
                        showError = true;
                    }
                    if (question.MINLIMITTYPE == "NLT_LITERAL") {
                        min = question.MINLITERAL;
                    } else if (question.MINLIMITTYPE == "NLT_PREVQUES") {
                        min = scope.answers[question.MINPREVQUES].VALUE;
                    }
                    if (question.MAXLIMITTYPE == "NLT_LITERAL") {
                        max = question.MAXLITERAL;
                    } else if (question.MAXLIMITTYPE == "NLT_PREVQUES") {
                        max = scope.answers[question.MAXPREVQUES].VALUE;
                    }
                    if (min !== "")
                        numberErrors++;
                    if (max !== "")
                        numberErrors = numberErrors + 2;
                    if (((max !== "" && parseInt(value) > parseInt(max)) || (min !== "" && parseInt(value) < parseInt(min))) && scope.answers[array_id].SKIPREASON == "NONE")
                        showError = true;

                    if (numberErrors == 3)
                        errorMsg = "The range of valid answers is " + min + " to " + max + ".";
                    else if (numberErrors == 2)
                        errorMsg = "The range of valid answers is " + max + " or fewer.";
                    else if (numberErrors == 1)
                        errorMsg = "The range of valid answers is " + min + " or greater.";

                    if (showError) {
                        scope.errors[array_id] = errorMsg;
                        valid = false;
                    } else {
                        delete scope.errors[array_id];
                    }
                }

                if (attr.answerType == "DATE") {
                    if (scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW") {
                        var date = value.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
                        var month = value.match(/January|February|March|April|May|June|July|August|September|October|November|December/);
                        var year = value.match(/\d{4}/);
                        var time = value.match(/(\d+):(\d+) (AM|PM)/);
                        if (time && time.length > 2) {
                            if (parseInt(time[1]) < 1 || parseInt(time[1]) > 12) {
                                scope.errors[array_id] = 'Please enter 1 to 12 for HH.';
                                valid = false;
                            }
                            if (parseInt(time[2]) < 0 || parseInt(time[2]) > 59) {
                                scope.errors[array_id] = 'Please enter 0 to 59 for MM.';
                                valid = false;
                            }
                        } else {
                            if (scope.timeBits(question.TIMEUNITS, 'MINUTE')) {
                                scope.errors[array_id] = 'Please enter the minutes.';
                                valid = false;
                            }
                            if (scope.timeBits(question.TIMEUNITS, 'HOUR')) {
                                scope.errors[array_id] = 'Please enter the time of day.';
                                valid = false;
                            }
                        }
                        if (scope.timeBits(question.TIMEUNITS, 'YEAR') && !year) {
                            scope.errors[array_id] = 'Please enter a valid year.';
                            valid = false;
                        }
                        if (scope.timeBits(question.TIMEUNITS, 'MONTH') && !month) {
                            scope.errors[array_id] = 'Please enter a month.';
                            valid = false;
                        }
                        if (scope.timeBits(question.TIMEUNITS, 'MONTH') && scope.timeBits(question.TIMEUNITS, 'YEAR') && scope.timeBits(question.TIMEUNITS, 'DAY') && year && !date) {
                            scope.errors[array_id] = 'Please enter a day of the month.';
                            valid = false;
                        }
                        if (date) {
                            if (parseInt(date[2]) < 1 || parseInt(date[2]) > 31) {
                                scope.errors[array_id] = 'Please enter a different number for the day of month.';
                                valid = false;
                            }
                        }
                        if (valid == true)
                            delete scope.errors[array_id];
                    } else {
                        delete scope.errors[array_id];
                    }
                }

                if (attr.answerType == "TIME_SPAN") {
                    if (scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW") {
                        if (value.trim() == "") {
                            scope.errors[array_id] = 'Please fill in at least one of the fields.';
                            valid = false;
                        }
                    } else {
                        delete scope.errors[array_id];
                    }
                }

                if (attr.answerType == "MULTIPLE_SELECTION") {
                    var showError = false;
                    min = question.MINCHECKABLEBOXES;
                    max = question.MAXCHECKABLEBOXES;
                    var numberErrors = 0;
                    var errorMsg = "";

                    if (min !== null && min != null)
                        numberErrors++;
                    if (max !== null && max != null)
                        numberErrors = numberErrors + 2;

                    checkedBoxes = value.split(',').length;
                    if (!value)
                        checkedBoxes = 0;
                    if (numberErrors != 0 && (checkedBoxes < min || checkedBoxes > parseInt(max)) && scope.answers[array_id].SKIPREASON == "NONE")
                        showError = true;
                    //console.log('min:' + min + ':max:' + max + ':checked:' + checkedBoxes+ ":answer:" + value + ":showerror:" + showError);

                    adds = '';
                    if (max != 1)
                        adds = 's';
                    if (parseInt(question.ASKINGSTYLELIST) == 1)
                        adds += ' for each row';
                    if (numberErrors == 3 && min == max && showError)
                        errorMsg = "Select " + max + " response" + adds + " please.";
                    else if (numberErrors == 3 && min != max && showError)
                        errorMsg = "Select " + min + " to " + max + " response" + adds + " please.";
                    else if (numberErrors == 2 && showError)
                        errorMsg = "You may select up to " + max + " response" + adds + " please.";
                    else if (numberErrors == 1 && showError)
                        errorMsg = "You must select at least " + min + " response" + adds + " please.";

                    if (showError) {
                        scope.errors[array_id] = errorMsg;
                        valid = false;
                    }

                    // check for list range limitations
                    var checks = 0;
                    if (typeof question != "undefined" && parseInt(question.WITHLISTRANGE) != 0) {
                        for (i in scope.answers) {
                            if ((scope.answers[i].VALUE != undefined && scope.answers[i].VALUE.split(',').indexOf(question.LISTRANGESTRING) != -1) || scope.answers[i].VALUE == question.LISTRANGESTRING) {
                                checks++;
                            }
                        }
                        //console.log("check list range: " + checks);

                        if (checks < question.MINLISTRANGE || checks > question.MAXLISTRANGE) {
                            errorMsg = "";
                            if (question.MINLISTRANGE && question.MAXLISTRANGE) {
                                if (question.MINLISTRANGE != question.MAXLISTRANGE)
                                    errorMsg = question.MINLISTRANGE + " - " + question.MAXLISTRANGE;
                                else
                                    errorMsg = "just " + question.MINLISTRANGE;
                            } else if (!question.MINLISTRANGE && !question.MAXLISTRANGE) {
                                errorMsg = "up to " + question.MAXLISTRANGE;
                            } else {
                                errorMsg = "at least " + question.MINLISTRANGE;
                            }

                            valid = false;
                            scope.errors[array_id] = "Please select " + errorMsg + " response(s).  You selected " + checks + ".";

                        } else {
                            for (k in scope.errors) {
                                if (scope.errors[k].match("Please select "))
                                    delete scope.errors[k];
                            }
                        }
                    }
                }
                if (typeof scope.errors[0] == "undefined" && Object.keys(scope.errors).length > 0) {

                    for (k in scope.errors) {
                        if (scope.hasRefuse && !scope.errors[k].match("again to skip to the next question")) {
                            scope.errors[k] += " Click \"Next\" again to skip to the next question.";
                            break;
                        }
                    }
                }
                $("table.qTable").floatThead('reflow');
                ngModel.$setValidity('checkAnswer', valid);
                return valid ? value : undefined;
            });

            ngModel.$formatters.unshift(function(value) {
                var valid = true;
                var array_id = attr.arrayId;
                var question = questions[attr.questionId];
                if (attr.answerType == "NAME_GENERATOR") {
                    if ((typeof scope.answers[array_id] != "undefined" && scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW" || typeof scope.answers[array_id] == "undefined") && Object.keys(scope.nGalters).length < scope.questions[0].MINLITERAL) {
                        var noun = " people";
                        if (scope.questions[0].MINLITERAL == 1)
                            noun = " person";
                        scope.errors[array_id] = 'Please list at least ' + scope.questions[0].MINLITERAL + noun + ".";
                        valid = false;
                    } else {
                        delete scope.errors[0];
                        delete scope.errors[array_id];
                        delete scope.answerForm.$error.checkAnswer;
                    }
                }

                if (attr.answerType == "TEXTUAL") {
                    if (scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW") {
                        if (value == "") {
                            scope.errors[array_id] = "Value cannot be blank.";
                            valid = false;
                        } else {
                            delete scope.errors[array_id];
                        }
                    } else {
                        delete scope.errors[array_id];
                    }
                }
                if (attr.answerType == "NUMERICAL") {
                    var min = "";
                    var max = "";
                    var numberErrors = 0;
                    var showError = false;
                    if ((value == "" && scope.answers[array_id].SKIPREASON == "NONE") || (value != "" && value.match(/[^$,.\d]/) != null)) {
                        scope.errors[array_id] = 'Please enter a number.';
                        valid = false;
                    }
                    if (question.MINLIMITTYPE == "NLT_LITERAL") {
                        min = question.MINLITERAL;
                    } else if (question.MINLIMITTYPE == "NLT_PREVQUES") {
                        min = scope.answers[question.MINPREVQUES].VALUE;
                    }
                    if (question.MAXLIMITTYPE == "NLT_LITERAL") {
                        max = question.MAXLITERAL;
                    } else if (question.MAXLIMITTYPE == "NLT_PREVQUES") {
                        max = scope.answers[question.MAXPREVQUES].VALUE;
                    }
                    if (min !== "")
                        numberErrors++;
                    if (max !== "")
                        numberErrors = numberErrors + 2;
                    if (((max !== "" && parseInt(value) > parseInt(max)) || (min !== "" && parseInt(value) < parseInt(min))) && scope.answers[array_id].SKIPREASON == "NONE")
                        showError = true;

                    if (numberErrors == 3 && showError)
                        errorMsg = "The range of valid answers is " + min + " to " + max + ".";
                    else if (numberErrors == 2 && showError)
                        errorMsg = "The range of valid answers is " + max + " or fewer.";
                    else if (numberErrors == 1 && showError)
                        errorMsg = "The range of valid answers is " + min + " or greater.";

                    if (showError) {
                        scope.errors[array_id] = errorMsg;
                        valid = false;
                    }
                }

                if (attr.answerType == "DATE") {
                    //console.log(scope.timeUnits);
                    if (scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW") {
                        var date = value.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
                        var month = value.match(/January|February|March|April|May|June|July|August|September|October|November|December/);
                        var year = value.match(/\d{4}/);
                        var time = value.match(/(\d+):(\d+) (AM|PM)/);
                        if (time && time.length > 2) {
                            if (parseInt(time[1]) < 1 || parseInt(time[1]) > 12) {
                                scope.errors[array_id] = 'Please enter 1 to 12 for HH.';
                                valid = false;
                            }
                            if (parseInt(time[2]) < 0 || parseInt(time[2]) > 59) {
                                scope.errors[array_id] = 'Please enter 0 to 59 for MM.';
                                valid = false;
                            }
                        } else {
                            if (scope.timeBits(question.TIMEUNITS, 'MINUTE')) {
                                scope.errors[array_id] = 'Please enter the minutes.';
                                valid = false;
                            }
                            if (scope.timeBits(question.TIMEUNITS, 'HOUR')) {
                                scope.errors[array_id] = 'Please enter the time of day.';
                                valid = false;
                            }
                        }
                        if (scope.timeBits(question.TIMEUNITS, 'YEAR') && !year) {
                            scope.errors[array_id] = 'Please enter a valid year.';
                            valid = false;
                        }
                        if (scope.timeBits(question.TIMEUNITS, 'MONTH') && !month) {
                            scope.errors[array_id] = 'Please enter a month.';
                            valid = false;
                        }
                        if (scope.timeBits(question.TIMEUNITS, 'MONTH') && scope.timeBits(question.TIMEUNITS, 'YEAR') && scope.timeBits(question.TIMEUNITS, 'DAY') && year && !date) {
                            scope.errors[array_id] = 'Please enter a day of the month.';
                            valid = false;
                        }
                        if (date) {
                            if (parseInt(date[2]) < 1 || parseInt(date[2]) > 31) {
                                scope.errors[array_id] = 'Please enter a different number for the day of month.';
                                valid = false;
                            }
                        }
                        if (valid == true)
                            delete scope.errors[array_id];
                    } else {
                        delete scope.errors[array_id];
                    }
                }

                if (attr.answerType == "TIME_SPAN") {
                    if (scope.answers[array_id].SKIPREASON != "REFUSE" && scope.answers[array_id].SKIPREASON != "DONT_KNOW") {
                        if (value.trim() == "") {
                            scope.errors[array_id] = 'Please fill in one of the fields.';
                            valid = false;
                        }
                    } else {
                        delete scope.errors[array_id];
                    }
                }

                if (attr.answerType == "MULTIPLE_SELECTION") {
                    min = question.MINCHECKABLEBOXES;
                    max = question.MAXCHECKABLEBOXES;
                    var numberErrors = 0;
                    var showError = false;
                    var errorMsg = "";

                    if (min !== "" && min != null)
                        numberErrors++;
                    if (max !== "" && max != null)
                        numberErrors = numberErrors + 2;

                    checkedBoxes = value.split(',').length;
                    if (!value)
                        checkedBoxes = 0;

                    if (numberErrors != 0 && (checkedBoxes < min || checkedBoxes > parseInt(max)) && scope.answers[array_id].SKIPREASON == "NONE")
                        showError = true;

                    //console.log('min:' + min + ':max:' + max + ':checked:' + checkedBoxes+ ":answer:" + value + ":showerror:" + showError);

                    adds = '';
                    if (max != 1)
                        adds = 's';
                    if (parseInt(question.ASKINGSTYLELIST) == 1)
                        adds += ' for each row';
                    if (numberErrors == 3 && min == max && showError)
                        errorMsg = "Select " + max + " response" + adds + " please.";
                    else if (numberErrors == 3 && min != max && showError)
                        errorMsg = "Select " + min + " to " + max + " response" + adds + " please.";
                    else if (numberErrors == 2 && showError)
                        errorMsg = "You may select up to " + max + " response" + adds + " please.";
                    else if (numberErrors == 1 && showError)
                        errorMsg = "You must select at least " + min + " response" + adds + " please.";

                    if (showError) {
                        scope.errors[array_id] = errorMsg;
                        valid = false;
                    } else {
                        if (typeof scope.errors[array_id] != "undefined")
                            delete scope.errors[array_id];
                        valid = true;
                    }
                    // check for list range limitations
                    var checks = 0;
                    if (typeof question != "undefined" && parseInt(question.WITHLISTRANGE) != 0) {
                        for (i in scope.answers) {
                            if ((scope.answers[i].VALUE != undefined && scope.answers[i].VALUE.split(',').indexOf(question.LISTRANGESTRING) != -1) || scope.answers[i].VALUE == question.LISTRANGESTRING) {
                                checks++;
                            }
                        }

                        //console.log("check list range: " + checks);

                        if (checks < question.MINLISTRANGE || checks > question.MAXLISTRANGE) {
                            errorMsg = "";
                            if (question.MINLISTRANGE && question.MAXLISTRANGE) {
                                if (question.MINLISTRANGE != question.MAXLISTRANGE)
                                    errorMsg = question.MINLISTRANGE + " - " + question.MAXLISTRANGE;
                                else
                                    errorMsg = "just " + question.MINLISTRANGE;
                            } else if (!question.MINLISTRANGE && !question.MAXLISTRANGE) {
                                errorMsg = "up to " + question.MAXLISTRANGE;
                            } else {
                                errorMsg = "at least " + question.MINLISTRANGE;
                            }

                            valid = false;
                            scope.errors[array_id] = "Please select " + errorMsg + " response(s).  You selected " + checks + ".";

                        } else {
                            for (k in scope.errors) {
                                if (scope.errors[k].match("Please select "))
                                    delete scope.errors[k];
                            }
                            for (k in scope.answerForm) {
                                if (k.match("Answer")) {
                                    scope.answerForm[k].$setValidity("checkAnswer", true);
                                }
                            }
                        }
                    }
                }
                if (typeof scope.errors[0] == "undefined" && Object.keys(scope.errors).length > 0) {
                    for (k in scope.errors) {
                        if (scope.hasRefuse && !scope.errors[k].match("again to skip to the next question")) {
                            scope.errors[k] += " Click \"Next\" again to skip to the next question.";
                            break;
                        }
                    }
                }
                $("table.qTable").floatThead('reflow');
                ngModel.$setValidity('checkAnswer', valid);
                return value;
            });

        }
    };
}]);

function buildList() {
    console.log("building master list..");
    i = 0;
    masterList[i] = new Object;
    var alter_non_list_qs = [];
    var prev_alter_non_list_qs = [];
    if (study.INTRODUCTION != "") {
        introduction = new Object;
        introduction.TITLE = "INTRODUCTION";
        introduction.ANSWERTYPE = "INTRODUCTION";
        introduction.PROMPT = study.INTRODUCTION;
        masterList[i][0] = introduction;
        i++;
        masterList[i] = new Object;
    }
    if (parseInt(study.HIDEEGOIDPAGE) != 1 || isMobile == true) {
        for (j in ego_id_questions) {
            if (ego_id_questions[j].ANSWERTYPE == "STORED_VALUE" || ego_id_questions[j].ANSWERTYPE == "RANDOM_NUMBER")
                continue;
            ego_id_questions[j].array_id = ego_id_questions[j].ID;
            masterList[i][parseInt(ego_id_questions[j].ORDERING) + 1] = ego_id_questions[j];
        }
    }
    if (parseInt(study.HIDEEGOIDPAGE) != 1 || isMobile == true) {
        i++;
        masterList[i] = new Object;
    }

    //if(interviewId != null){
    ego_question_list = new Object;
    prompt = "";
    for (j in questionList) {
        if ((prev_alter_non_list_qs.length > 0 && (questionList[j].SUBJECTTYPE != "PREVIOUS_ALTER" || parseInt(questionList[j].ASKINGSTYLELIST) == 1)) || (j == questionList.length - 1 && questionList[j].SUBJECTTYPE == "PREVIOUS_ALTER" && parseInt(questionList[j].ASKINGSTYLELIST) != 1)) {
            if (j == questionList.length - 1 && questionList[j].SUBJECTTYPE == "PREVIOUS_ALTER" && parseInt(questionList[j].ASKINGSTYLELIST) != 1)
                prev_alter_non_list_qs.push(questionList[j]);
            var preface = new Object;
            preface.ID = prev_alter_non_list_qs[0].ID;
            preface.ANSWERTYPE = "PREFACE";
            preface.SUBJECTTYPE = "PREFACE";
            preface.TITLE = prev_alter_non_list_qs[0].TITLE + " - PREFACE";
            preface.PROMPT = prev_alter_non_list_qs[0].PREFACE;
            for (k in prevAlters) {
                for (l in prev_alter_non_list_qs) {
                    var question = $.extend(true, {}, prev_alter_non_list_qs[l]);
                    question.PROMPT = question.PROMPT.replace(/\$\$/g, prevAlters[k].NAME);
                    question.TITLE = question.TITLE + " - " + prevAlters[k].NAME;
                    question.ALTERID1 = prevAlters[k].ID;
                    question.array_id = question.ID + '-' + question.ALTERID1;
                    if (prev_alter_non_list_qs[0].PREFACE != "") {
                        if (prev_alter_non_list_qs[l].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i][0] = preface;
                        prev_alter_non_list_qs[0].PREFACE = "";
                        i++;
                        masterList[i] = new Object;
                    }
                    if (prev_alter_non_list_qs[l].ANSWERREASONEXPRESSIONID > 0)
                        evalQIndex.push(i);
                    masterList[i][question.array_id] = question;
                    i++;
                    masterList[i] = new Object;
                }
            }
            prev_alter_non_list_qs = [];
        }
        if ((alter_non_list_qs.length > 0 && (questionList[j].SUBJECTTYPE != "ALTER" || parseInt(questionList[j].ASKINGSTYLELIST) == 1)) || (j == questionList.length - 1 && questionList[j].SUBJECTTYPE == "ALTER" && parseInt(questionList[j].ASKINGSTYLELIST) != 1)) {
            if (j == questionList.length - 1 && questionList[j].SUBJECTTYPE == "ALTER" && parseInt(questionList[j].ASKINGSTYLELIST) != 1)
                alter_non_list_qs.push(questionList[j]);
            var preface = new Object;
            preface.ID = alter_non_list_qs[0].ID;
            preface.ANSWERTYPE = "PREFACE";
            preface.SUBJECTTYPE = "PREFACE";
            preface.TITLE = alter_non_list_qs[0].TITLE + " - PREFACE";
            preface.PROMPT = alter_non_list_qs[0].PREFACE;
            for (k in alters) {
                for (l in alter_non_list_qs) {
                    var question = $.extend(true, {}, alter_non_list_qs[l]);
                    question.PROMPT = question.PROMPT.replace(/\$\$/g, alters[k].NAME);
                    question.TITLE = question.TITLE + " - " + alters[k].NAME;
                    question.ALTERID1 = alters[k].ID;
                    question.array_id = question.ID + '-' + question.ALTERID1;
                    if (alter_non_list_qs[0].PREFACE != "") {
                        if (alter_non_list_qs[l].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i][0] = preface;
                        alter_non_list_qs[0].PREFACE = "";
                        i++;
                        masterList[i] = new Object;
                    }
                    if (alter_non_list_qs[l].ANSWERREASONEXPRESSIONID > 0)
                        evalQIndex.push(i);
                    masterList[i][question.array_id] = question;
                    i++;
                    masterList[i] = new Object;
                }
            }
            alter_non_list_qs = [];
        }
        if (questionList[j - 1] != undefined && (questionList[j].SUBJECTTYPE != "EGO" || questionList[j].ASKINGSTYLELIST != 1 || questionList[j].PROMPT != questionList[j - 1].PROMPT) && questionList[j - 1].SUBJECTTYPE == "EGO" && Object.keys(ego_question_list).length > 0) {
            console.log("wait over " + Object.keys(ego_question_list).length);
            if (ego_question_list[Object.keys(ego_question_list)[0]].ANSWERREASONEXPRESSIONID > 0)
                evalQIndex.push(i);
            var stemTitle = ego_question_list[Object.keys(ego_question_list)[0]].TITLE
            for (sl = 1; sl < Object.keys(ego_question_list).length; sl++) {
                ego_question_list[Object.keys(ego_question_list)[sl]].TITLE = stemTitle;
            }
            masterList[i] = ego_question_list;
            ego_question_list = new Object;
            prompt = "";
            i++;
            masterList[i] = new Object;
        }
        if (questionList[j].SUBJECTTYPE == "NAME_GENERATOR") {
            if(questionList[j].HIDENAMEGENQ == true)
                continue;
            if (questionList[j].PREFACE != "") {
                var preface = new Object;
                preface.ID = questionList[j].ID;
                preface.ANSWERTYPE = "PREFACE";
                preface.SUBJECTTYPE = "PREFACE";
                preface.TITLE = questionList[j].TITLE + " - PREFACE";
                preface.PROMPT = questionList[j].PREFACE;
                if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                    evalQIndex.push(i);
                masterList[i][0] = $.extend(true, {}, preface);
                i++;
                masterList[i] = new Object;
            }
            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                evalQIndex.push(i);
            questionList[j].SUBJECTTYPE = "NAME_GENERATOR";
            questionList[j].array_id = questionList[j].ID;
            masterList[i][0] = questionList[j];
            i++;
            masterList[i] = new Object;
        }
        if (questionList[j].SUBJECTTYPE == "MERGE_ALTER" && Object.keys(prevAlters).length > 0) {
            if (Object.keys(alters).length == 0)
                continue;
            var alters2 = $.extend(true, {}, prevAlters);
            var preface = new Object;
            preface.ID = questionList[j].ID;
            preface.ANSWERTYPE = "PREFACE";
            preface.SUBJECTTYPE = "PREFACE";
            preface.TITLE = questionList[j].TITLE + " - PREFACE";
            preface.PROMPT = questionList[j].PREFACE;

            matchedIds = new Object;

            dm = new DoubleMetaphone;
            discardNames = ["i", "ii", "iii", "iv", "v", "jr", "sr"]
            dm.maxCodeLen = 64;
            maxdTol = questionList[j].MINLITERAL == null ? 1 : parseInt(questionList[j].MINLITERAL);
            maxlTol = questionList[j].MAXLITERAL == null ? 1 : parseInt(questionList[j].MAXLITERAL);
            var lDist = {};
            var dDist = {};
            for (k in alters) {
                for (l in alters2) {
                    if (alters[k].NAME.toLowerCase().trim() == alters2[l].NAME.toLowerCase().trim()) {
                        if (typeof matchedIds[k] == "undefined")
                            matchedIds[k] = [];
                        dDist[k] = 0;
                        matchedIds[k].unshift(l);
                    }
                }
            }
            for (k in alters) {
                if (typeof matchedIds[k] == "undefined")
                    matchedIds[k] = [];

                if (alters[k].ALTERLISTID == interviewId.toString() || (alters[k].ALTERLISTID && alters[k].ALTERLISTID.split(",").indexOf(interviewId.toString()) != -1))
                    continue;

                for (l in alters2) {
                    if (typeof dDist[k] == "undefined") {
                        dDist[k] = 100;
                    }
                    if (typeof lDist[k] == "undefined") {

                        lDist[k] = 100;
                    }

                    if (alters2[l].ALTERLISTID == alters[k].ID.toString() || (alters2[l].ALTERLISTID && alters2[l].ALTERLISTID.split(",").indexOf(alters[k].ID.toString()) != -1))
                        continue;

                    //match first letter of first name
                    if (alters[k].NAME.toLowerCase().charAt(0) != alters2[l].NAME.toLowerCase().charAt(0))
                        continue;
                    //  if(alters[k].NAME.toLowerCase() == alters2[l].NAME.toLowerCase())
                    //   continue;
                    name1 = alters[k].NAME.trim().toLowerCase().replace(/\./g, ' ').trim().split(" ");
                    name2 = alters2[l].NAME.trim().toLowerCase().replace(/\./g, ' ').trim().split(" ");
                    console.log(name1, name2, name1[0], name2[0])
                    last1 = false;
                    last2 = false;
                    first1 = name1[0].charAt(0).toLowerCase();
                    first2 = name2[0].charAt(0).toLowerCase();

                    if (name1.length > 1 && discardNames.includes(name1[name1.length - 1]))
                        name1.pop();
                    if (name2.length > 1 && discardNames.includes(name2[name2.length - 1]))
                        name2.pop();

                    if (name1.length > 1) {
                        last1 = name1[name1.length - 1].charAt(0).toLowerCase();
                    }
                    if (name2.length > 1) {
                        last2 = name2[name2.length - 1].charAt(0).toLowerCase();
                    }
                    if (typeof name1[0] == "undefined")
                        continue;
                    d1 = dm.doubleMetaphone(name1[0]).primary;
                    d2 = dm.doubleMetaphone(name2[0]).primary;
                    ds = new Levenshtein(d1, d2);
                    if (ds.distance <= maxdTol && matchedIds[k].indexOf(l) == -1) {
                        if (!last1 || !last2 || last1 == last2) {
                            // full name match
                            if (ds.distance < dDist[k]) {
                                dDist[k] = Number(ds.distance);
                                matchedIds[k].unshift(l);
                            } else {
                                matchedIds[k].push(l);
                            }
                        }
                    }
                    if (last1 && last2) {
                        l1 = dm.doubleMetaphone(name1[name1.length - 1]).primary;
                        l2 = dm.doubleMetaphone(name2[name2.length - 1]).primary;
                        ls = new Levenshtein(l1, l2);
                        console.log(l1, l2, ls.distance, maxlTol);
                        // last name distance
                        if (ls.distance <= maxlTol) {
                            // first letter of first name matches
                            if (first1 == first2 && matchedIds[k].indexOf(l) == -1) {
                                // l is alter2 id
                                if (ls.distance < lDist[k]) {
                                    lDist[k] = ls.distance;
                                    matchedIds[k].unshift(l);
                                } else {
                                    matchedIds[k].push(l);
                                }
                            }
                        }
                    }
                }
            }
            for (k in alters) {
                var id = k;
                merge_alter_question_list = new Object;
                if (matchedIds[id].length == 0)
                    continue;
                console.log(matchedIds[k])

                for (dId in matchedIds[id]) {
                    var l = matchedIds[id][dId];
                    var question = $.extend(true, {}, questionList[j]);
                    question.PROMPT = question.PROMPT.replace(/\$\$1/g, alters[k].NAME);
                    question.PROMPT = question.PROMPT.replace(/\$\$2/g, alters2[l].NAME);
                    question.TITLE = question.TITLE + " - " + alters[k].NAME + " and " + alters2[l].NAME;
                    question.ALTERID1 = alters[k].ID;
                    question.ALTERID2 = alters2[l].ID;
                    question.array_id = question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2;
                    if (parseInt(questionList[j].ASKINGSTYLELIST) == 1) {
                        merge_alter_question_list[question.array_id] = question;
                    } else {
                        if (preface.PROMPT != "") {
                            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                                evalQIndex.push(i);
                            masterList[i][question.array_id] = $.extend(true, {}, preface);
                            preface.PROMPT = "";
                            i++;
                            masterList[i] = new Object;
                        }
                        if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i][question.array_id] = question;
                        i++;
                        masterList[i] = new Object;
                    }
                    if (questionList[j].ASKINGSTYLELIST == 1) {
                        if (Object.keys(merge_alter_question_list).length > 0) {
                            if (preface.PROMPT != "") {
                                if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                                    evalQIndex.push(i);
                                masterList[i][0] = $.extend(true, {}, preface);
                                preface.PROMPT = "";
                                i++;
                                masterList[i] = new Object;
                            }
                            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                                evalQIndex.push(i);
                            masterList[i] = merge_alter_question_list;
                            i++;
                            masterList[i] = new Object;
                        }
                    }
                }
            }
        }
        if (questionList[j].SUBJECTTYPE == "PREVIOUS_ALTER") {
            if (Object.keys(prevAlters).length == 0)
                continue;
            alter_question_list = new Object;
            if (parseInt(questionList[j].ASKINGSTYLELIST) != 1) {
                //console.log("non list alter qs")
                prev_alter_non_list_qs.push(questionList[j]);
            } else {
                for (k in prevAlters) {
                    var question = $.extend(true, {}, questionList[j]);
                    question.PROMPT = question.PROMPT.replace(/\$\$/g, prevAlters[k].NAME);
                    question.ALTERID1 = prevAlters[k].ID;
                    question.array_id = question.ID + '-' + question.ALTERID1;
                    alter_question_list[question.array_id] = question;
                }
                if (Object.keys(alter_question_list).length > 0) {
                    var preface = new Object;
                    preface.ID = questionList[j].ID;
                    preface.ANSWERTYPE = "PREFACE";
                    preface.SUBJECTTYPE = "PREFACE";
                    preface.TITLE = questionList[j].TITLE + " - PREFACE";
                    preface.PROMPT = questionList[j].PREFACE;
                    if (preface.PROMPT != "") {
                        if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i][0] = $.extend(true, {}, preface);
                        preface.PROMPT = "";
                        i++;
                        masterList[i] = new Object;
                    }
                    if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                        evalQIndex.push(i);
                    masterList[i] = alter_question_list;
                    i++;
                    masterList[i] = new Object;
                }
            }
        }
        if (questionList[j].SUBJECTTYPE == "ALTER") {
            if (Object.keys(alters).length == 0)
                continue;
            alter_question_list = new Object;
            if (parseInt(questionList[j].ASKINGSTYLELIST) != 1) {
                //console.log("non list alter qs")
                alter_non_list_qs.push(questionList[j]);
            } else {
                for (k in alters) {
                    var question = $.extend(true, {}, questionList[j]);
                    question.PROMPT = question.PROMPT.replace(/\$\$/g, alters[k].NAME);
                    question.ALTERID1 = alters[k].ID;
                    question.array_id = question.ID + '-' + question.ALTERID1;
                    alter_question_list[question.array_id] = question;
                }
                if (Object.keys(alter_question_list).length > 0) {
                    var preface = new Object;
                    preface.ID = questionList[j].ID;
                    preface.ANSWERTYPE = "PREFACE";
                    preface.SUBJECTTYPE = "PREFACE";
                    preface.TITLE = questionList[j].TITLE + " - PREFACE";
                    preface.PROMPT = questionList[j].PREFACE;
                    if (preface.PROMPT != "") {
                        if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i][0] = $.extend(true, {}, preface);
                        preface.PROMPT = "";
                        i++;
                        masterList[i] = new Object;
                    }
                    if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                        evalQIndex.push(i);
                    masterList[i] = alter_question_list;
                    i++;
                    masterList[i] = new Object;
                }
            }
        }

        if (questionList[j].SUBJECTTYPE == "ALTER_PAIR") {
            if (Object.keys(alters).length == 0)
                continue;
            var alters2 = $.extend(true, {}, alters);
            var preface = new Object;
            preface.ID = questionList[j].ID;
            preface.ANSWERTYPE = "PREFACE";
            preface.SUBJECTTYPE = "PREFACE";
            preface.TITLE = questionList[j].TITLE + " - PREFACE";
            preface.PROMPT = questionList[j].PREFACE;
            for (k in alters) {
                console.log("alter pair q...");
                questionList[j].SYMMETRIC = 1;
                if (questionList[j].SYMMETRIC) {
                    var keys = Object.keys(alters2);
                    delete alters2[keys[0]];
                }
                alter_pair_question_list = new Object;
                for (l in alters2) {
                    var question = $.extend(true, {}, questionList[j]);
                    if (alters[k].ID == alters2[l].ID && question.SYMMETRIC == 1)
                        continue;
                    question.PROMPT = question.PROMPT.replace(/\$\$1/g, alters[k].NAME);
                    question.PROMPT = question.PROMPT.replace(/\$\$2/g, alters2[l].NAME);
                    question.TITLE = question.TITLE + " - " + alters[k].NAME;
                    question.ALTERID1 = alters[k].ID;
                    question.ALTERID2 = alters2[l].ID;
                    question.array_id = question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2;
                    if (parseInt(questionList[j].ASKINGSTYLELIST) == 1) {
                        alter_pair_question_list[question.array_id] = question;
                    } else {
                        if (preface.PROMPT != "") {
                            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                                evalQIndex.push(i);
                            masterList[i][question.array_id] = $.extend(true, {}, preface);
                            preface.PROMPT = "";
                            i++;
                            masterList[i] = new Object;
                        }
                        if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i][question.array_id] = question;
                        i++;
                        masterList[i] = new Object;
                    }
                }
                if (questionList[j].ASKINGSTYLELIST == 1) {
                    if (Object.keys(alter_pair_question_list).length > 0) {
                        if (preface.PROMPT != "") {
                            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                                evalQIndex.push(i);
                            masterList[i][0] = $.extend(true, {}, preface);
                            preface.PROMPT = "";
                            i++;
                            masterList[i] = new Object;
                        }
                        if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                            evalQIndex.push(i);
                        masterList[i] = alter_pair_question_list;
                        i++;
                        masterList[i] = new Object;
                    }
                }
            }
        }

        if (questionList[j].SUBJECTTYPE == "NETWORK") {
            questionList[j].array_id = questionList[j].ID;
            /*
            if (questionList[j].PREFACE != "") {
                var preface = new Object;
                preface.ID = questionList[j].ID;
                preface.ANSWERTYPE = "PREFACE";
                preface.SUBJECTTYPE = "PREFACE";
                preface.TITLE = questionList[j].TITLE + " - PREFACE";
                preface.PROMPT = questionList[j].PREFACE;
                if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                    evalQIndex.push(i);
                masterList[i][0] = $.extend(true, {}, preface);
                i++;
                masterList[i] = new Object;
            }
            */
            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                evalQIndex.push(i);
            masterList[i][questionList[j].ID] = questionList[j];
            i++;
            masterList[i] = new Object;
        }
        if (questionList[j].SUBJECTTYPE == "MULTI_GRAPH") {
            questionList[j].array_id = questionList[j].ID;
            if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                evalQIndex.push(i);
            masterList[i][questionList[j].ID] = questionList[j];
            i++;
            masterList[i] = new Object;
        }
        if (questionList[j].SUBJECTTYPE == "EGO") {
            questionList[j].array_id = questionList[j].ID;
            /*
                  if (Object.keys(ego_question_list).length > 0 && (parseInt(questionList[j].ASKINGSTYLELIST) != 1 || prompt != questionList[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm, ""))) {
                    console.log(questionList[j])
                    console.log("wait over " + Object.keys(ego_question_list).length);
                    if (ego_question_list[Object.keys(ego_question_list)[0]].ANSWERREASONEXPRESSIONID > 0)
                      evalQIndex.push(i);
                    masterList[i] = ego_question_list;
                    ego_question_list = new Object;
                    prompt = "";
                    i++;
                    masterList[i] = new Object;
                  }
                  */
            if (questionList[j].PREFACE != "") {
                preface = new Object;
                preface.ID = questionList[j].ID;
                preface.ANSWERTYPE = "PREFACE";
                preface.SUBJECTTYPE = "PREFACE";
                preface.TITLE = questionList[j].TITLE + " - PREFACE";
                preface.PROMPT = questionList[j].PREFACE;
                if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                    evalQIndex.push(i);
                masterList[i][0] = preface;
                i++;
                masterList[i] = new Object;
            }
            if (parseInt(questionList[j].ASKINGSTYLELIST) == 1) {
                console.log(questionList[j].TITLE, questionList[j].ANSWERREASONEXPRESSIONID)

                if (prompt == "" || prompt == questionList[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm, "")) {
                    // console.log("adding question")
                    prompt = questionList[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm, "");
                    ego_question_list[parseInt(questionList[j].ORDERING) + 1] = questionList[j];
                }
            } else {

                if (questionList[j].ANSWERREASONEXPRESSIONID > 0)
                    evalQIndex.push(i);
                masterList[i][questionList[j].ID] = questionList[j];
                i++;
                masterList[i] = new Object;
            }
        }
    }
    conclusion = new Object;
    conclusion.TITLE = "CONCLUSION";
    conclusion.ANSWERTYPE = "CONCLUSION";
    conclusion.PROMPT = study.CONCLUSION;
    conclusion.array_id = 0;
    masterList[i][0] = conclusion;
    //}
}

function evalQuestions() {
    for (i in evalQIndex) {
        if (evalQIndex[i] < currentPage)
            continue;
        for (j in masterList[evalQIndex[i]]) {
            evalQList[masterList[evalQIndex[i]][j].array_id] = evalExpression(masterList[evalQIndex[i]][j].ANSWERREASONEXPRESSIONID, masterList[evalQIndex[i]][j].ALTERID1, masterList[evalQIndex[i]][j].ALTERID2);
        }
    }
}

function qFromList(pageNumber) {
    var i = 0;
    var questions = {};
    if (pageNumber == 0) {
        currentPage = i;
        return masterList[0];
    }
    for (var k in masterList) {
        questions = {};
        var proceed = false;
        if (!!~jQuery.inArray(parseInt(k), evalQIndex)) {
            for (j in masterList[k]) {
                if (evalQList[masterList[k][j].array_id] == true) {
                    proceed = true;
                    questions[j] = masterList[k][j];
                } else {
                    if (typeof answers[masterList[k][j].array_id] == "undefined" || parseInt(answers[masterList[k][j].array_id].VALUE) != parseInt(study.VALUELOGICALSKIP)) {
                        console.log("saving skip of " + masterList[k][j].TITLE, answers[masterList[k][j].array_id]);
                        saveSkip(interviewId, masterList[k][j].ID, masterList[k][j].ALTERID1, masterList[k][j].ALTERID2, masterList[k][j].array_id);
                    }
                }
            }
        } else {
            proceed = true;
            questions = masterList[k];
        }
        if (pageNumber == i && proceed == true) {
            //console.log(questions);
            currentPage = i;
            return questions;
        }
        if (proceed == true)
            i++;
    }
}

function interpretTags(string, alterId1, alterId2) {
    console.log("interpretting " + string);
    if (string == null)
        return string;
    // parse out and replace variables
    vars = string.match(/<VAR (.+?) \/>/g);
    for (k in vars) {
        var thisVar = vars[k].match(/<VAR (.+?) \/>/)[1];
        var question = getQuestion(thisVar);
        if (!question)
            continue;

        var array_id = question.ID;
        if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER' && question.STUDYID == study.ID)
            array_id += "-" + alterId1;
        else if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER' && question.STUDYID != study.ID)
            array_id += "-" + alterId2;
        else if (typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
            array_id += 'and' + alterId2;

        var lastAnswer = "";
        var lastAnswerOps = [];
        if (typeof answers[array_id] != 'undefined') {
            if (question.ANSWERTYPE == "MULTIPLE_SELECTION") {
                for (o in options[question.ID]) {
                    if(options[question.ID][o].OTHERSPECIFY == true){
                        var specify = answers[array_id].OTHERSPECIFYTEXT.split(";;");
                        for (s in specify) {
                            var pair = specify[s].split(":");
                            if(pair[0] == options[question.ID][o].ID)
                               lastAnswerOps.push(options[question.ID][o].NAME + " ("+htmldecode(pair[1])+")");
                        }
                    }else{
                        if (options[question.ID][o].ID.toString() == answers[array_id].VALUE.toString() || $.inArray(options[question.ID][o].ID.toString(), answers[array_id].VALUE.split(",")) != -1)
                            lastAnswerOps.push(options[question.ID][o].NAME);
                    }
                }
                console.log("last answer ops:", answers[array_id].VALUE, question.ID, lastAnswerOps);
                lastAnswer = lastAnswerOps.join("<br>")
            } else {
                lastAnswer = answers[array_id].VALUE;
            }
            string = string.replace('<VAR ' + thisVar + ' />', lastAnswer);
        } else {
            string = string.replace('<VAR ' + thisVar + ' />', '');
        }
    }

    // performs calculations on questions
    calcs = string.match(/<CALC (.+?) \/>/g);
    for (j in calcs) {
        calc = calcs[j].match(/<CALC (.+?) \/>/)[1];
        vars = calc.match(/(\w+)/g);
        for (k in vars) {
            var thisVar = vars[k].match(/<VAR (.+?) \/>/)[1];
            if (vars[k].match(/<VAR (.+?) \/>/)) {
                var question = getQuestion(thisVar);
                if (!question)
                    continue;

                var array_id = question.ID;
                if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
                    array_id += "-" + alterId1;
                else if (typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
                    array_id += 'and' + alterId2;

                var lastAnswer = "0";
                if (typeof answers[array_id] != 'undefined') {
                    if (question.ANSWERTYPE == "MULTIPLE_SELECTION") {
                        for (o in options[question.ID]) {
                            if ($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
                                lastAnswer = options[question.ID][o].NAME;
                        }
                    } else {
                        lastAnswer = answers[array_id].VALUE;
                    }
                    logic = calc.replace(thisVar, lastAnswer);
                } else {
                    logic = calc.replace(thisVar, '0');
                }
            }
        }
        try {
            calculation = eval(calc);
        } catch (err) {
            calculation = "";
        }
        string = string.replace("<CALC " + calc + " />", calculation);
    }

    // counts numbers of times question is answered with string
    var counts = string.match(/<COUNT (.+?) \/>/g);
    for (k in counts) {
        var count = counts[k].match(/<COUNT (.+?) \/>/)[1];
        var parts = count.split(' ');
        var qTitle = parts[0];
        var answer = parts[1];
        answer = answer.replace('"', '');

        var question = getQuestion(qTitle);
        if (!question)
            continue;

        var array_id = question.ID;
        if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
            array_id += "-" + alterId1;
        else if (typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
            array_id += 'and' + alterId2;

        var lastAnswer = "";
        var lastAnswerOps = [];
        if (typeof answers[array_id] != 'undefined') {
            if (question.ANSWERTYPE == "MULTIPLE_SELECTION") {
                for (o in options[question.ID]) {
                    if ($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
                        lastAnswerOps.push(options[question.ID][o].NAME);
                }
                lastAnswer = lastAnswerOps.join("<br>")
            } else {
                lastAnswer = answers[array_id].VALUE;
            }

            string = string.replace('<COUNT ' + count + ' />', lastAnswer ? 1 : 0);
        } else {
            string = string.replace('<COUNT ' + count + ' />', 0);
        }
    }

    var dates = string.match(/<DATE (.+?) \/>/g);
    var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    for (k in dates) {
        var date = dates[k].match(/<DATE (.+?) \/>/)[1]
        var parts = date.split(" ");
        var qTitle = parts[0];
        var amount = parseInt(parts[1]);
        var period = parts[2];
        if (qTitle.toLowerCase() == "now") {
            var dateVal = new Date

        } else {
            var question = getQuestion(qTitle);
            if (!question)
                continue;
            var array_id = question.ID;
            if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
                array_id += "-" + alterId1;
            else if (typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
                array_id += 'and' + alterId2;
            lastAnswer = answers[array_id].VALUE;
            var dateVal = new Date(lastAnswer);
        }
        if (period.match(/DAY/i)) {
            dateVal.setDate(dateVal.getDate() + amount);
        } else if (period.match(/MONTH/i)) {
            dateVal.setMonth(dateVal.getMonth() + amount);
        } else if (period.match(/YEAR/i)) {
            dateVal.setYear(dateVal.getFullYear() + amount);
        }
        var newDate = monthNames[dateVal.getMonth()] + " " + dateVal.getDate() + ", " + dateVal.getFullYear()

        /*
              var timeArray = [];
              var bitVals = {
                'BIT_YEAR': 1,
                'BIT_MONTH': 2,
                'BIT_WEEK': 4,
                'BIT_DAY': 8,
                'BIT_HOUR': 16,
                'BIT_MINUTE': 32,
              };
              for (var k in bitVals) {
                if (question.TIMEUNITS & bitVals[k]) {
                  timeArray.push(k);
                }
              }

              
              var newDate = ""
              if (in_array("BIT_MONTH", $timeArray))
                  newDate  = monthNames[dateVal.getMonth()]
              if (in_array("BIT_DAY", $timeArray))
                  newDate += " " + dateval.getDate()
              if (in_array("BIT_YEAR", $timeArray))
                  newDate += ", " + dateval.getFullYear();
              if (in_array("BIT_HOUR", $timeArray)){
                var ampm = "AM"
                var hours = dateval.getHours()
                if(hours > 12){
                  hours = 12 - hours
                  ampm = "PM"
                }
                + " " + amount + " " + period
                if(hours == 0)
                  hours = 12
                var minutes = dateval.getMinutes()
                if(minutes < 10)
                  minutes = "0" + minutes;
              }
              newDate += " " + hours + ":" + minutes + " " + ampm
            }
            */
        string = string.replace('<DATE ' + date + ' />', newDate)
    }

    // same as count, but limited to specific alter / alter pair questions
    containers = string.match(/<CONTAINS (.+?) \/>/g);
    for (k in containers) {
        var contains = containers[k].match(/<CONTAINS (.+?) \/>/)[1];
        var parts = contains.split(/\s/);
        //var qTitle = parts[0];
        //var answer = parts[1];
        var qTitle = contains.slice(0, contains.indexOf(' '));
        var answer = contains.slice(contains.indexOf(' ') + 1);
        answer = answer.replace(/"/g, '');
        var question = getQuestion(qTitle);
        if (!question)
            continue;
        var array_id = question.ID;
        if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
            array_id += "-" + alterId1;
        else if (typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
            array_id += 'and' + alterId2;
        var lastAnswer = "";
        if (typeof answers[array_id] != 'undefined') {
            if (question.ANSWERTYPE == "MULTIPLE_SELECTION") {
                for (o in options[question.ID]) {
                    console.log(options[question.ID][o].NAME, options[question.ID][o].ID.toString(), answers[array_id].VALUE.split(","))
                    if ($.inArray(options[question.ID][o].ID.toString(), answers[array_id].VALUE.split(",")) != -1)
                        lastAnswer = options[question.ID][o].NAME;
                }
            } else {
                lastAnswer = answers[array_id].VALUE;
            }
            string = string.replace("<CONTAINS " + contains + " />", lastAnswer == answer ? 1 : 0);
            console.log(answer + ":" + lastAnswer);
        } else {
            string = string.replace("<CONTAINS " + contains + " />", 0);
        }
    }

    // parse out and show logics
    showlogics = string.match(/<IF (.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\" \/>/g);
    for (k in showlogics) {
        showlogic = showlogics[k];
        exp = showlogic.match(/\<IF (.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\"/);
        if (exp.length > 1) {
            for (i = 1; i < 3; i++) {
                if (i == 2 || !isNaN(parseInt(exp[i])))
                    continue;
                if (exp[i].match("/>")) {
                    console.log("match exp")
                    exp[i] = interpretTags(exp[i]);
                } else {

                    var qTitle = exp[i];
                    var question = getQuestion(qTitle);
                    if (!question)
                        continue;

                    var array_id = question.ID;
                    if (typeof alterId1 != 'undefined' && question.SUBJECTTYPE == 'ALTER')
                        array_id += "-" + alterId1;
                    else if (typeof alterId2 != 'undefined' && question.SUBJECTTYPE == 'ALTER_PAIR')
                        array_id += 'and' + alterId2;

                    var lastAnswer = "";
                    var lastAnswerOps = [];

                    if (typeof answers[array_id] != 'undefined') {
                        if (question.ANSWERTYPE == "MULTIPLE_SELECTION") {
                            for (o in options[question.ID]) {
                                if ($.inArray(options[question.ID][o].ID, answers[array_id].VALUE.split(",")) != -1)
                                    lastAnswerOps.push(options[question.ID][o].NAME);
                            }
                            lastAnswer = lastAnswerOps.join("<br>");
                        } else {
                            lastAnswer = answers[array_id].VALUE;
                        }
                    } else {
                        return false;
                    }
                    exp[i] = lastAnswer;
                }
            }
            logic = exp[1] + ' ' + exp[2] + ' ' + exp[3];
            console.log("logic", exp, logic);
            show = eval(logic);
            if (show) {
                string = string.replace(showlogic, exp[4]);
            } else {
                string = string.replace(showlogic, "");
            }
        }
    }
    return string;
}

function getQuestion(title) {
    if (title.match(/:/)) {
        if (typeof questionTitles[title.split(":")[0]] != "undefined" && typeof questions[questionTitles[title.split(":")[0]][title.split(":")[1]]] != "undefined")
            return questions[questionTitles[title.split(":")[0]][title.split(":")[1]]];
    } else {
        if (typeof questionTitles[study.NAME] != "undefined" && typeof questions[questionTitles[study.NAME][title]] != "undefined")
            return questions[questionTitles[study.NAME][title]];
    }
    return false;
}

function navFromList(pageNumber, scope) {
    var i = 0;
    var pages = [];
    this.checkPage = function(iPage, pageNumber, text) {
        if (iPage == pageNumber) {
            $("#questionTitle").html(text);
            text = "<b>" + text + "</b>";
        }
        if (iPage - 1 == pageNumber && text == "CONCLUSION")
            scope.conclusion = true;
        return text;
    };
    for (k in masterList) {
        var proceed = false;
        if (jQuery.inArray(parseInt(k), evalQIndex) != -1) {
            for (j in masterList[k]) {
                if (evalQList[masterList[k][j].array_id] == true) {
                    proceed = true;
                    pages[i] = this.checkPage(i, pageNumber, masterList[k][j].TITLE);
                    continue;
                }
            }
        } else {
            for (j in masterList[k]) {
                proceed = true;
                pages[i] = this.checkPage(i, pageNumber, masterList[k][j].TITLE);
                continue;
            }
        }
        if (proceed == true)
            i++;
    }
    scope.nav = pages;
}

function columnWidths() {
    return;
    var tWidth;
    var cWidths = [];
    tWidth = $("#realHeader").width();
    $("#realHeader").children().each(function(index) {
        cWidths[index] = $(this).width();
    });
    $("#floatHeader").width(tWidth);
    $("#floatHeader").css({
        "background-color": $("#content").css("background-color")
    });
    $("#realHeader").parent().css({
        "margin-top": "-" + $("#floater").height() + "px"
    });
    $("#floater").children().each(function(index) {
        $(this).width(cWidths[index]);
    });
}

function fixHeader() {
    console.log("fixing header");
    columnWidths();
    // Set this variable with the height of your sidebar + header
    var offsetLeft = $("#realHeader").offset().left
    var offsetPixels = $(".navbar").height();
    $("#content").css({
        "background-attachment": "fixed"
    });
    if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        /*
        $(window).scroll(function(event) {
          $("#floatHeader").css({
            "position": "fixed",
            "z-index": "111",
            "top": offsetPixels + "px",
            "left": offsetLeft - $(window).scrollLeft() + "px",
            "padding-top": parseInt($("#content").css("padding-top")) + "px"
          });
          $("#answerForm").css({
            "margin-top": $("#floatHeader").height() + "px"
          });
        });
        $(window).scroll();
        */
    } else {
        /*
        $(window).on('touchmove', function(event) {
          $("#floatHeader").css({
            "position": "fixed",
            "top": offsetPixels + "px",
            "left": offsetLeft - $(window).scrollLeft() + "px",
            "padding-top": parseInt($("#content").css("padding-top")) + "px"
          });
          $("#answerForm").css({
            "margin-top": $("#floatHeader").height() + "px"
          });
        });
        */
    }
    var resizeTimer;

    $(window).on('resize', function(e) {

        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            /*
                $(window).unbind('scroll');
                $("#content").css({
                  "background-attachment": "fixed"
                });
                $("#floatHeader").css({
                  "position": "fixed",
                  "left": $("#realHeader").offset().left - $(window).scrollLeft() + "px",
                });
                fixHeader();
            */
            // $("#qTable").floatThead({top:$("#topbar").height()})
        }, 250);

    });
    /*
      $(window).resize(function() {
        $(window).unbind('scroll');
        var offsetLeft = $("#realHeader").offset().left
        $("#content").css({
          "background-attachment": "fixed"
        });
        $("#floatHeader").css({
          "position": "fixed",
          "left": offsetLeft - $(window).scrollLeft() + "px",
        });
        fixHeader();
      });*/
}

function unfixHeader() {
    $("#content").css({
        "background-attachment": "initial"
    });
    $("#floatHeader").css({
        "padding-top": "0",
        "top": "0",
        "position": "static"
    });
    $("#answerForm").css({
        "margin-top": "0"
    });
    $(window).unbind('scroll');
    $(window).unbind('touchmove');
    $(window).unbind('resize');
}

function htmldecode(str) {
    str = str.replace(/amp;/g, "");
    var txt = document.createElement('textarea');
    txt.innerHTML = str.trim();
    return $(txt).text();
}
