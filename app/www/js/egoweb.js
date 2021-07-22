controllers = ["/interview", "/data", "/import-export", "/admin", "/dyad", "/authoring"];
for (let c = 0; c < controllers.length; c++) {
    if (document.location.href.match(controllers[c]))
        rootUrl = document.location.href.split(controllers[c])[0];
}

noteBar = [
    ['style', ['bold', 'italic', 'underline', 'clear']],
    ['fontname', ['fontname']],
    ['fontsize', ['fontsize']],
    ['color', ['color']],
    ['para', ['paragraph']],
    ['misc', ['hr', 'link', 'picture', 'codeview']],
];

eTags = [];
jQuery.browser = {};
(function() {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        jQuery.browser.msie = true;
        jQuery.browser.version = RegExp.$1;
    }

})();
$(function() {
    $('body').on('hidden.bs.modal', '#myModal', function() {
        $(this).removeData('bs.modal');
    });
    $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
        }
        var $subMenu = $(this).next(".dropdown-menu");
        $subMenu.toggleClass('show');
        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
            $('.dropdown-submenu .show').removeClass("show");
        });
        return false;
    });

})


function updatePrompt(url) {
    $.get(url, function(data) {
        $('#edit-alterPrompt').html(data);
    });
    return false;
}

function deletePrompt(url) {
    $.get(url, function(data) {
        $('#edit-alterPrompt').html(data);
    });
    return false;
}

function createUUID() {
    var s = [];
    var hexDigits = "0123456789abcdef";
    for (var i = 0; i < 36; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }
    s[14] = "4"; // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "-";

    var uuid = s.join("");
    return uuid;
}

var audio = new Audio();

function playSound(uri) {
    if (audio.paused) {
        console.log(uri);
        audio = new Audio(uri);
        audio.play();
    }
}

function uploadAudio(file, id, studyId, type, update) {
    var csrf = $("input[name='YII_CSRF_TOKEN']").val();
    data = new FormData();
    data.append("userfile", file.files[0]);
    data.append("id", id);
    data.append("studyId", studyId);
    data.append("type", type);
    data.append("YII_CSRF_TOKEN", csrf);
    $.ajax({
        data: data,
        type: "POST",
        url: "/authoring/uploadaudio?" + createUUID(),
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            update.html(data);
            setTimeout(function() {
                $('#myModal').modal('hide');
                $('.close').click()
            }, 0);
        }
    });
}

function deleteAudio(id, studyId, type, update) {
    data = new FormData();
    data.append("id", id);
    data.append("studyId", studyId);
    data.append("type", type);
    $.ajax({
        data: data,
        type: "POST",
        url: "/authoring/deleteaudio?" + createUUID(),
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            setTimeout(function() {
                $('#myModal').modal('hide');
                $('.close').click();
                update.html(data);

            }, 0);
        }
    });
}

function loadAudio(uri) {
    var audio = new Audio();
    audio.src = uri;
    return audio;
}

function uploadImage(file, editor, welEditable) {
    data = new FormData();
    data.append("file", file);
    data.append("YII_CSRF_TOKEN", $("[name*='YII_CSRF_TOKEN']").val());
    $.ajax({
        data: data,
        type: "POST",
        url: "/authoring/image",
        cache: false,
        contentType: false,
        processData: false,
        success: function(url) {
            editor.insertImage(welEditable, url);
        }
    });
}

function CleanPastedHTML(input) {
    // 1. remove line breaks / Mso classes
    var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g;
    var output = input.replace(stringStripper, ' ');
    // 2. strip Word generated HTML comments
    var commentSripper = new RegExp('<!--(.*?)-->', 'g');
    var output = output.replace(commentSripper, '');
    var tagStripper = new RegExp('<(/)*(meta|link|span|\\?xml:|st1:|o:|font)(.*?)>', 'gi');
    // 3. remove tags leave content if any
    output = output.replace(tagStripper, '');
    // 4. Remove everything in between and including tags '<style(.)style(.)>'
    var badTags = ['style', 'script', 'applet', 'embed', 'noframes', 'noscript'];

    for (var i = 0; i < badTags.length; i++) {
        tagStripper = new RegExp('<' + badTags[i] + '.*?' + badTags[i] + '(.*?)>', 'gi');
        output = output.replace(tagStripper, '');
    }
    // 5. remove attributes ' style="..."'
    var badAttributes = ['style', 'start'];
    for (var i = 0; i < badAttributes.length; i++) {
        var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"', 'gi');
        output = output.replace(attributeStripper, '');
    }
    return output;
}


function timeBits(timeUnits, span) {
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


function parseEgowebTags(e, id) {
    var imageDir = "/www/images/";
    if (!e)
        return "";
    eTags[id] = {};
    var varMatch = e.match(/<VAR[^\/]+[^>]+\/>/gm);
    if (varMatch) {
        eTags[id].varSnips = [];
        for (k in varMatch)
            eTags[id].varSnips.push(varMatch[k]);
        for (i = 0; i < eTags[id].varSnips.length; i++) {
            e = e.replace(eTags[id].varSnips[i], "<img id='var_" + i + "' src='" + imageDir + "var.png'>");
        }
    }
    var calcMatch = e.match(/<CALC[^\/]+[^>]+\/>/gm);
    if (calcMatch) {
        eTags[id].calcSnips = [];
        for (k in calcMatch)
            eTags[id].calcSnips.push(calcMatch[k]);
        for (i = 0; i < eTags[id].calcSnips.length; i++) {
            e = e.replace(eTags[id].calcSnips[i], "<img id='calc_" + i + "' src='" + imageDir + "calc.png'>");
        }
    }
    var countMatch = e.match(/<COUNT[^\/]+[^>]+\/>/gm);
    if (countMatch) {
        eTags[id].countSnips = [];
        for (k in countMatch)
            eTags[id].countSnips.push(countMatch[k]);
        for (i = 0; i < eTags[id].countSnips.length; i++) {
            e = e.replace(eTags[id].countSnips[i], "<img id='count_" + i + "' src='" + imageDir + "count.png'>");
        }
    }
    var containsMatch = e.match(/<CONTAINS[^\/]+[^>]+\/>/gm);
    if (containsMatch) {
        eTags[id].containsSnips = [];
        for (k in containsMatch)
            eTags[id].containsSnips.push(containsMatch[k]);
        for (i = 0; i < eTags[id].containsSnips.length; i++) {
            e = e.replace(eTags[id].containsSnips[i], "<img id='contains_" + i + "' src='" + imageDir + "contains.png'>");
        }
    }
    var ifMatch = e.match(/<IF((.|\n)*)\/>/gm);
    if (ifMatch) {
        eTags[id].ifSnips = [];
        for (k in ifMatch)
            eTags[id].ifSnips.push(ifMatch[k]);
        for (i = 0; i < eTags[id].ifSnips.length; i++) {
            e = e.replace(eTags[id].ifSnips[i], "<img id='if_" + i + "' src='" + imageDir + "if.png'>");
        }
    }
    var dateMatch = e.match(/<DATE((.|\n)*)\/>/gm);
    if (dateMatch) {
        eTags[id].dateSnips = [];
        for (k in dateMatch)
            eTags[id].dateSnips.push(dateMatch[k]);
        for (i = 0; i < eTags[id].dateSnips.length; i++) {
            e = e.replace(eTags[id].dateSnips[i], "<img id='date_" + i + "' src='" + imageDir + "date.png'>");
        }
    }
    return e;
}

function rebuildEgowebTags(withCode, id) {
    console.log("rebuilding " + id, eTags[id])
    var imageDir = "/www/images/";
    if (typeof eTags[id] == "undefined")
        return withCode;
    if (typeof eTags[id].ifSnips != "undefined") {
        var ifSnips = eTags[id].ifSnips;
        for (i = 0; i < ifSnips.length; i++) {
            withCode = withCode.replace("<img id=\"if_" + i + "\" src=\"" + imageDir + "if.png\">", ifSnips[i]);
        }
    }
    if (typeof eTags[id].calcSnips != "undefined") {
        var calcSnips = eTags[id].calcSnips;
        for (i = 0; i < calcSnips.length; i++) {
            if (withCode.match("<img id=\"calc_" + i + "\" src=\"" + imageDir + "calc.png\">"))
                withCode = withCode.replace("<img id=\"calc_" + i + "\" src=\"" + imageDir + "calc.png\">", calcSnips[i]);
            if (withCode.match("<img id='calc_" + i + "' src='" + imageDir + "calc.png'>"))
                withCode = withCode.replace("<img id='calc_" + i + "' src='" + imageDir + "calc.png'>", calcSnips[i]);
        }
    }

    if (typeof eTags[id].varSnips != "undefined") {
        var varSnips = eTags[id].varSnips;

        for (i = 0; i < varSnips.length; i++) {
            if (withCode.match("<img id=\"var_" + i + "\" src=\"" + imageDir + "var.png\">"))
                withCode = withCode.replace("<img id=\"var_" + i + "\" src=\"" + imageDir + "var.png\">", varSnips[i]);
            if (withCode.match("<img id='var_" + i + "' src='" + imageDir + "var.png'>"))
                withCode = withCode.replace("<img id='var_" + i + "' src='" + imageDir + "var.png'>", varSnips[i]);
        }
    }

    if (typeof eTags[id].countSnips != "undefined") {
        var countSnips = eTags[id].countSnips;
        for (i = 0; i < countSnips.length; i++) {
            if (withCode.match("<img id=\"count_" + i + "\" src=\"" + imageDir + "count.png\">"))
                withCode = withCode.replace("<img id=\"count_" + i + "\" src=\"" + imageDir + "count.png\">", countSnips[i]);
            if (withCode.match("<img id='count_" + i + "' src='" + imageDir + "count.png'>"))
                withCode = withCode.replace("<img id='count_" + i + "' src='" + imageDir + "count.png'>", countSnips[i]);
        }
    }

    if (typeof eTags[id].containsSnips != "undefined") {
        var containsSnips = eTags[id].containsSnips;
        for (i = 0; i < containsSnips.length; i++) {
            if (withCode.match("<img id=\"contains_" + i + "\" src=\"" + imageDir + "contains.png\">"))
                withCode = withCode.replace("<img id=\"contains_" + i + "\" src=\"" + imageDir + "contains.png\">", containsSnips[i]);
            if (withCode.match("<img id='contains_" + i + "' src='" + imageDir + "contains.png'>"))
                withCode = withCode.replace("<img id='contains_" + i + "' src='" + imageDir + "contains.png'>", containsSnips[i]);
        }
    }

    if (typeof eTags[id].dateSnips != "undefined") {
        var dateSnips = eTags[id].dateSnips;
        for (i = 0; i < dateSnips.length; i++) {
            if (withCode.match("<img id=\"date_" + i + "\" src=\"" + imageDir + "date.png\">"))
                withCode = withCode.replace("<img id=\"date_" + i + "\" src=\"" + imageDir + "date.png\">", dateSnips[i]);
            if (withCode.match("<img id='date_" + i + "' src='" + imageDir + "date.png'>"))
                withCode = withCode.replace("<img id='date_" + i + "' src='" + imageDir + "date.png'>", dateSnips[i]);
        }
    }

    withCode = withCode.replace(/&nbsp;/g, ' ')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>');
    delete eTags[id];
    return withCode;
}

function evalExpression(id, alterId1, alterId2) {
    var array_id;
    if (!id || id == 0)
        return true;
    if (typeof expressions[id] == "undefined")
        return true;

    questionId = expressions[id].QUESTIONID;
    subjectType = "";
    if (questionId && questions[questionId])
        subjectType = questions[questionId].SUBJECTTYPE;

    comparers = {
        'Greater': '>',
        'GreaterOrEqual': '>=',
        'Equals': '==',
        'LessOrEqual': '<=',
        'Less': '<'
    };

    if (questionId)
        array_id = questionId;
    if (typeof alterId1 != 'undefined' && subjectType == 'ALTER')
        array_id += "-" + alterId1;
    else if (typeof alterId2 != 'undefined' && subjectType == 'ALTER_PAIR')
        array_id += "-" + alterId1 + 'and' + alterId2;

    if (typeof answers[array_id] != "undefined")
        answer = answers[array_id].VALUE;
    else
        answer = "";

    if (expressions[id].TYPE == "Text") {
        if (!answer)
            return expressions[id].RESULTFORUNANSWERED;
        if (expressions[id].OPERATOR == "Contains") {
            if (answer.indexOf(expressions[id].VALUE) != -1) {
                console.log(expressions[id].NAME + ":true");
                return true;
            }
        } else if (expressions[id].OPERATOR == "Equals") {
            if (answer == expressions[id].VALUE) {
                console.log(expressions[id].NAME + ":true");
                return true;
            }
        }
    }
    if (expressions[id].TYPE == "Number") {
        if (!answer)
            return expressions[id].RESULTFORUNANSWERED;
        logic = answer + " " + comparers[expressions[id].OPERATOR] + " " + expressions[id].VALUE;
        result = eval(logic);
        console.log(expressions[id].NAME + ":" + result);
        return result;
    }
    if (expressions[id].TYPE == "Selection") {
        if (!answer)
            return expressions[id].RESULTFORUNANSWERED;
        if(answer.toString().match(","))
            selectedOptions = answer.split(',');
        else
            selectedOptions = [answer];
        var options = expressions[id].VALUE.split(',');
        trues = 0;
        for (var k in selectedOptions) {
            if (expressions[id].OPERATOR == "Some" && options.indexOf(selectedOptions[k]) != -1) {
                console.log(expressions[id].NAME + ":true");
                return true;
            }
            if (expressions[id].OPERATOR == "None" && options.indexOf(selectedOptions[k]) != -1) {
                console.log(expressions[id].NAME + ":false");
                return false;
            }
            if (options.indexOf(selectedOptions[k]) != -1)
                trues++;
        }
        if (expressions[id].OPERATOR == "None" || (expressions[id].OPERATOR == "All" && trues >= options.length)) {
            console.log(expressions[id].NAME + ":true");
            return true;
        }
    }
    if (expressions[id].TYPE == "Counting") {
        countingSplit = expressions[id].VALUE.split(':');
        var times = parseInt(countingSplit[0]);
        var expressionIds = countingSplit[1];
        var questionIds = countingSplit[2];

        var count = 0;
        if (expressionIds != "") {
            expressionIds = expressionIds.split(',');
            for (var k in expressionIds) {
                count = count + evalExpression(expressionIds[k], alterId1, alterId2);
            }
        }
        if (questionIds != "") {
            questionIds = questionIds.split(',');
            for (var k in questionIds) {
                count = count + countQuestion(questionIds[k], expressions[id].OPERATOR);
            }
        }
        console.log(expressions[id].NAME + ":" + (times * count));
        return (times * count);
    }
    if (expressions[id].TYPE == "Comparison") {
        compSplit = expressions[id].VALUE.split(':');
        value = parseInt(compSplit[0]);
        var expressionId = parseInt(compSplit[1]);
        result = evalExpression(expressionId, alterId1, alterId2);
        logic = result + " " + comparers[expressions[id].OPERATOR] + " " + value;
        result = eval(logic);
        console.log(expressions[id].NAME + ":" + result);
        return result;
    }
    if (expressions[id].TYPE == "Compound") {
        var subexpressions = expressions[id].VALUE.split(',');
        var trues = 0;
        for (var k in subexpressions) {
            // prevent infinite loops!
            if (parseInt(subexpressions[k]) == id)
                continue;
            var isTrue = evalExpression(parseInt(subexpressions[k]), alterId1, alterId2);
            if (expressions[id].OPERATOR == "Some" && isTrue == true) {
                console.log(expressions[id].NAME + ":true");
                return true;
            }
            if (isTrue == true)
                trues++;
            console.log(expressions[id].NAME + ":subexpression:" + k + ":" + isTrue);
        }
        if (expressions[id].OPERATOR == "None" && trues == 0) {
            console.log(expressions[id].NAME + ":true");
            return true;
        } else if (expressions[id].OPERATOR == "All" && trues == subexpressions.length) {
            console.log(expressions[id].NAME + ":true");
            return true;
        }
    }
    if (expressions[id].TYPE == "Name Generator") {
        var oneTrue = false;
        var twoTrue = true;
        if (expressions[id].VALUE.match(","))
            var genList = expressions[id].VALUE.split(",");
        else
            var genList = [expressions[id].VALUE];
        aList = []
        if (alters[alterId1] != undefined) {
            if (alters[alterId1].NAMEGENQIDS.match(","))
                var aList = alters[alterId1].NAMEGENQIDS.split(",");
            else
                var aList = [alters[alterId1].NAMEGENQIDS];
        }
        for (n in aList) {
            if (genList.indexOf(aList[n]) > -1)
                oneTrue = true;
        }
        if (typeof alterId2 != 'undefined' && alters[alterId2] != undefined) {
            aList = [];
            twoTrue = false;
            if (alters[alterId2].NAMEGENQIDS.match(","))
                aList = alters[alterId2].NAMEGENQIDS.split(",");
            else
                aList = [alters[alterId2].NAMEGENQIDS];
            aList2 = aList;
            for (n in aList) {
                if (genList.indexOf(aList[n]) > -1)
                    twoTrue = true;
            }
        }
        console.log("name gen exp: ", oneTrue, twoTrue);
        return (oneTrue && twoTrue);
    }
    console.log(expressions[id].NAME + ":false");
    return false;

}

function countExpression(id) {
    if (evalExpression(id) == true)
        return 1;
    else
        return 0;
}

function countQuestion(questionId, operator, alterId1, alterId2) {
    if (questionId)
        array_id = questionId;
    if (typeof alterId1 != 'undefined' && subjectType == 'ALTER')
        array_id += "-" + alterId1;
    else if (typeof alterId2 != 'undefined' && subjectType == 'ALTER_PAIR')
        array_id += 'and' + alterId2;
    if (typeof answers[array_id] != "undefined")
        answer = answers[array_id].VALUE;
    else
        answer = "";

    if (!answer) {
        return 0;
    } else {
        if (operator == "Sum")
            return parseInt(answer);
        else
            return 1;
    }
}

function initStats(question) {
    shortPaths = new Object;
    connections = [];
    nodes = [];
    edges = [];
    var n = [];
    var expressionId = question.NETWORKRELATIONSHIPEXPRID;
    var starExpressionId = parseInt(question.USELFEXPRESSION);

    if (!question.NETWORKPARAMS)
        question.NETWORKPARAMS = "[]";
    this.params = JSON.parse(question.NETWORKPARAMS);
    if (this.params == null)
        this.params = [];
    alterNames = new Object;
    betweennesses = [];
    if (alters.length == 0)
        return false;

    var alters2 = $.extend(true, {}, alters);

    if (typeof expressions[expressionId] != "undefined")
        var expression = expressions[expressionId];
    if (typeof expressions[starExpressionId] != "undefined")
        var starExpression = expressions[starExpressionId];
    console.log(expressions, starExpression)
    if (expression == undefined && starExpression == undefined)
        return

    //if(expression == undefined && expression.QUESTIONID)
    //var question = questions[expression.QUESTIONID];

    for (a in alters) {
        betweennesses[alters[a].ID] = 0;
        var keys = Object.keys(alters2);
        delete alters2[keys[0]];
        alterNames[alters[a].ID] = alters[a].NAME;
        for (b in alters2) {
            if (alters[a].ID == alters2[b].ID)
                continue;
            if (evalExpression(expressionId, alters[a].ID, alters2[b].ID) == true) {
                if ($.inArray(alters[a].ID, n) == -1)
                    n.push(alters[a].ID);
                if ($.inArray(alters2[b].ID, n) == -1)
                    n.push(alters2[b].ID);
                if (typeof connections[alters[a].ID] == "undefined")
                    connections[alters[a].ID] = [];
                if (typeof connections[alters2[b].ID] == "undefined")
                    connections[alters2[b].ID] = [];
                connections[alters[a].ID].push(alters2[b].ID);
                connections[alters2[b].ID].push(alters[a].ID);
            }
        }
    }

    this.getDistance = function(visited, node2) {
        var node1 = visited[visited.length - 1];

        if ($.inArray(node2, connections[node1]) != -1) {
            var trail = visited.slice(0);
            trail.push(node2);
            if (typeof shortPaths[visited[0] + "-" + node2] == "undefined") {

                shortPaths[visited[0] + "-" + node2] = [];
                shortPaths[visited[0] + "-" + node2].push(trail);
                if (typeof shortPaths[node2 + "-" + visited[0]] == "undefined")
                    shortPaths[node2 + "-" + visited[0]] = [];
                shortPaths[node2 + "-" + visited[0]].push(trail);
            } else {
                if (trail.length < shortPaths[visited[0] + "-" + node2][0].length) {
                    shortPaths[visited[0] + "-" + node2] = [];
                    shortPaths[node2 + "-" + visited[0]] = [];
                }
                if (shortPaths[visited[0] + "-" + node2].length == 0 || trail.length == shortPaths[visited[0] + "-" + node2][0].length) {
                    shortPaths[visited[0] + "-" + node2].push(trail);
                    shortPaths[node2 + "-" + visited[0]].push(trail);
                }
            }
        } else {
            for (k in connections[node1]) {

                var endNode = connections[node1][k];

                if ($.inArray(endNode, visited) == -1) {

                    var trail = visited.slice(0);
                    trail.push(endNode);
                    if (typeof shortPaths[visited[0] + "-" + endNode] != "undefined") {

                        if (trail.length < shortPaths[visited[0] + "-" + endNode][0].length) {

                            shortPaths[visited[0] + "-" + endNode] = [];
                            shortPaths[endNode + "-" + visited[0]] = [];
                        }
                        if (shortPaths[visited[0] + "-" + endNode].length == 0 || trail.length == shortPaths[visited[0] + "-" + endNode][0].length) {

                            shortPaths[visited[0] + "-" + endNode].push(trail);
                            shortPaths[endNode + "-" + visited[0]].push(trail);
                        } else {
                            continue;
                        }
                    } else {
                        shortPaths[visited[0] + "-" + endNode] = [];
                        shortPaths[visited[0] + "-" + endNode].push(trail);
                        if (typeof shortPaths[endNode + "-" + visited[0]] == "undefined")
                            shortPaths[endNode + "-" + visited[0]] = [];
                        shortPaths[endNode + "-" + visited[0]].push(trail);
                    }
                    this.getDistance(trail, node2);
                }
            }
        }
    }



    for (k in alters) {
        if (typeof connections[alters[k].ID] == "undefined") {
            //this.isolates[] = $alter.id;
            //this.nodes[] = $alter.id;
            n.push(alters[k].ID);
            connections[alters[k].ID] = [];
        }
    }

    var n2 = n.slice(0);
    for (a in n) {
        n2.shift();
        for (b in n2) {
            this.getDistance([n[a]], n2[b]);
        }
    }


    for (k in shortPaths) {
        var between = [];

        for (p in shortPaths[k]) {

            var path = shortPaths[k][p].slice(0);
            path.pop();
            path.shift();

            for (n in path) {
                if (typeof between[path[n]] == "undefined")
                    between[path[n]] = 1;
                else
                    between[path[n]] = between[path[n]] + 1;
            }
        }
        for (b in between) {
            betweennesses[b] = betweennesses[b] + (between[b] / shortPaths[k].length);
        }
    }


    closenesses = [];
    var alters2 = $.extend(true, {}, alters);
    for (a in alters) {
        var total = 0;
        var reachable = 0;
        for (b in alters2) {
            if (typeof shortPaths[alters[a].ID + "-" + alters2[b].ID] != "undefined") {
                distance = shortPaths[alters[a].ID + "-" + alters2[b].ID][0].length - 1;
                total = total + distance;
                reachable++;
            }
        }
        if (reachable < 1) {
            closenesses[alters[a].ID] = 0.0;
        } else {
            average = total / reachable;
            closenesses[alters[a].ID] = reachable / (average * (Object.keys(alters2).length - 1));
        }
    }

    this.nextEigenvectorGuess = function(guess) {
        var results = [];
        for (g in guess) {
            var result = 0.0;
            if (typeof connections[g] != "undefined") {
                for (c in connections[g]) {
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
        for (g in vec) {
            magnitudeSquared = magnitudeSquared + Math.pow(vec[g], 2);
        }
        var magnitude = Math.sqrt(magnitudeSquared);
        var factor = 1 / (magnitude < this.tinyNum ? this.tinyNum : magnitude);
        var normalized = [];
        for (g in vec) {
            normalized[g] = vec[g] * factor;
        }
        return normalized;
    }

    this.change = function(vec1, vec2) {
        var total = 0.0;
        for (g in vec1) {
            total = total + Math.abs(vec1[g] - vec2[g]);
        }
        return total;
    }

    var tries = (n.length + 5) * (n.length + 5);
    var guess = closenesses;
    while (tries >= 0) {
        var nextGuess = this.nextEigenvectorGuess(guess);
        if (this.change(guess, nextGuess) < this.tinyNum || tries == 0) {
            eigenvectors = nextGuess;
        }
        guess = nextGuess;
        tries--;
    }

    var all = [];
    for (k in betweennesses) {
        all.push(betweennesses[k]);
    }
    maxBetweenness = Math.max.apply(Math, all);
    minBetweenness = Math.min.apply(Math, all);

    var all = [];
    for (k in eigenvectors) {
        all.push(eigenvectors[k]);
    }
    maxEigenvector = Math.max.apply(Math, all);
    minEigenvector = Math.min.apply(Math, all);

    var all = [];
    for (k in connections) {
        all.push(connections[k].length);
    }
    maxDegree = Math.max.apply(Math, all);
    minDegree = Math.min.apply(Math, all);

    this.edgeColors = {
        '#000': 'black',
        '#ccc': 'gray',
        '#07f': 'blue',
        '#0c0': 'green',
        '#f80': 'orange',
        '#fd0': 'yellow',
        '#f00': 'red',
        '#c0f': 'purple',
    };
    this.edgeSizes = {
        "0.5": '0.5',
        "2": '2',
        "4": '4',
        "8": '8',
    };
    this.nodeColors = {
        '#000': 'black',
        '#ccc': 'gray',
        '#07f': 'blue',
        '#0c0': 'green',
        '#f80': 'orange',
        '#fd0': 'yellow',
        '#f00': 'red',
        '#c0f': 'purple',
    };
    this.nodeShapes = {
        'circle': 'circle',
        'star': 'star',
        'diamond': 'diamond',
        'cross': 'cross',
        'equilateral': 'triangle',
        'square': 'square',
    };
    this.nodeSizes = {
        2: '1',
        4: '2',
        6: '3',
        8: '4',
        10: '5',
        12: '6',
        14: '7',
        16: '8',
        18: '9',
        20: '10',
    };
    this.gradient = {
        "red": {
            0: "#F5D6D6",
            1: "#ECBEBE",
            2: "#E2A6A6",
            3: "#D98E8E",
            4: "#CF7777",
            5: "#C65F5F",
            6: "#BC4747",
            7: "#B32F2F",
            8: "#A91717",
            9: "#A00000"
        },
        "blue": {
            0: "#E3E5FF",
            1: "#C9D2FF",
            2: "#B0BFFF",
            3: "#97ACFF",
            4: "#7E99FF",
            5: "#6487FF",
            6: "#4B74FF",
            7: "#3261FF",
            8: "#194EFF",
            9: "#003CFF"
        },
        "green": {
            0: "#C7FFDD",
            1: "#B2F4C7",
            2: "#9EEAB2",
            3: "#8AE09D",
            4: "#76D688",
            5: "#62CB72",
            6: "#4EC15D",
            7: "#3AB748",
            8: "#26AD33",
            9: "#12A31E"
        },
        "black": {
            0: "#EEEEEE",
            1: "#D3D3D3",
            2: "#B9B9B9",
            3: "#9E9E9E",
            4: "#848484",
            5: "#696969",
            6: "#4F4F4F",
            7: "#343434",
            8: "#1A1A1A",
            9: "#000000"
        }
    };

    this.getNodeColor = function(nodeId) {
        var defaultNodeColor = "#07f";
        console.log(this.params['nodeColor'])
        if (typeof this.params['nodeColor'] != "undefined") {
            if (typeof this.params['nodeColor']['questionId'] != "undefined" && $.inArray(this.params['nodeColor']['questionId'], ["degree", "betweenness", "eigenvector"]) != -1) {
                if (this.params['nodeColor']['questionId'] == "degree") {
                    max = maxDegree;
                    min = minDegree;
                    value = connections[nodeId].length;
                }
                if (this.params['nodeColor']['questionId'] == "betweenness") {
                    max = maxBetweenness;
                    min = minBetweenness;
                    value = betweennesses[nodeId];
                }
                if (this.params['nodeColor']['questionId'] == "eigenvector") {
                    max = maxEigenvector;
                    min = minEigenvector;
                    value = eigenvectors[nodeId];
                }
                range = max - min;
                if (range == 0) {
                    range = max;
                    min = 0;
                }
                value = Math.round(((value - min) / (range)) * 9);
                var gc_color = "red";
                for (p in this.params['nodeColor']['options']) {
                    if (this.params['nodeColor']['options'][p]['id'] == this.params['nodeColor']['questionId'])
                        gc_color = this.params['nodeColor']['options'][p]['color'];
                }
                return this.gradient[gc_color][value];
            } else if (typeof this.params['nodeColor']['questionId'] != "undefined" && this.params['nodeColor']['questionId'].search("expression") != -1) {
                var qId = this.params['nodeColor']['questionId'].split("_");
                if (evalExpression(qId[1], nodeId) == true) {
                    for (p in this.params['nodeColor']['options']) {
                        if (this.params['nodeColor']['options'][p]['id'] == 1)
                            return this.params['nodeColor']['options'][p]['color'];
                    }
                } else {
                    for (p in this.params['nodeColor']['options']) {
                        if (this.params['nodeColor']['options'][p]['id'] == 0)
                            return this.params['nodeColor']['options'][p]['color'];
                    }
                }
            } else {
                if (typeof this.params['nodeColor']['questionId'] != "undefined" && typeof answers[this.params['nodeColor']['questionId'] + "-" + nodeId] != "undefined")
                    var answer = answers[this.params['nodeColor']['questionId'] + "-" + nodeId].VALUE.split(",");
                else
                    var answer = "";
                for (p in this.params['nodeColor']['options']) {
                    if (this.params['nodeColor']['options'][p]['id'] == -1 && nodeId == -1)
                        return this.params['nodeColor']['options'][p]['color'];
                    if (this.params['nodeColor']['options'][p]['id'] == "default" && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                        defaultNodeColor = this.params['nodeColor']['options'][p]['color'];
                    if (nodeId != -1 && (this.params['nodeColor']['options'][p]['id'] == answer || $.inArray(this.params['nodeColor']['options'][p]['id'], answer) != -1))
                        return this.params['nodeColor']['options'][p]['color'];
                }
            }
        }
        return defaultNodeColor;
    }

    this, getNodeSize = function(nodeId) {
        var defaultNodeSize = 4;
        console.log(this.params)
        if (nodeId != -1 && typeof this.params['nodeSize'] != "undefined") {
            if (typeof this.params['nodeSize']['questionId'] != "undefined" && this.params['nodeSize']['questionId'] == "degree") {
                max = maxDegree;
                min = minDegree;
                value = connections[nodeId].length;
                range = max - min;
                if (range == 0) {
                    range = max;
                    min = 0;
                }
                value = Math.round(((value - min) / (range)) * 9) + 1;
                return value * 2;
            }
            if (nodeId != -1 && typeof this.params['nodeSize']['questionId'] != "undefined" && this.params['nodeSize']['questionId'] == "betweenness") {
                max = maxBetweenness;
                min = minBetweenness;
                value = betweennesses[nodeId];
                range = max - min;
                if (range == 0) {
                    range = max;
                    min = 0;
                }
                value = Math.round(((value - min) / (range)) * 9) + 1;
                return value * 2;
            }
            if (nodeId != -1 && typeof this.params['nodeSize']['questionId'] != "undefined" && this.params['nodeSize']['questionId'] == "eigenvector") {
                max = maxEigenvector;
                min = minEigenvector;
                value = eigenvectors[nodeId];
                range = max - min;
                if (range == 0) {
                    range = max;
                    min = 0;
                }
                value = Math.round(((value - min) / (range)) * 9) + 1;
                return value * 2;
            }
            if (typeof this.params['nodeSize']['questionId'] != "undefined" && typeof answers[this.params['nodeSize']['questionId'] + "-" + nodeId] != "undefined")
                var answer = answers[this.params['nodeSize']['questionId'] + "-" + nodeId].VALUE.split(",");
            else
                var answer = "";
            for (p in this.params['nodeSize']['options']) {
                if (this.params['nodeSize']['options'][p]['id'] == -1 && nodeId == -1)
                    defaultNodeSize = this.params['nodeSize']['options'][p]['size'];
                if (this.params['nodeSize']['options'][p]['id'] == "default" && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                    defaultNodeSize = this.params['nodeSize']['options'][p]['size'];
                if (nodeId != -1 && (this.params['nodeSize']['options'][p]['id'] == answer || $.inArray(this.params['nodeSize']['options'][p]['id'], answer) != -1))
                    defaultNodeSize = this.params['nodeSize']['options'][p]['size'];
            }
        }
        return defaultNodeSize;
    }

    this.getNodeShape = function(nodeId) {
        var defaultNodeShape = "chircle";
        if (typeof this.params['nodeShape'] != "undefined") {
            if (typeof this.params['nodeShape']['questionId'] != "undefined" && typeof answers[this.params['nodeShape']['questionId'] + "-" + nodeId] != "undefined")
                var answer = answers[this.params['nodeShape']['questionId'] + "-" + nodeId].VALUE.split(",");
            else
                var answer = "";
            for (p in this.params['nodeShape']['options']) {
                if (this.params['nodeShape']['options'][p]['id'] == 0 && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                    defaultNodeShape = this.params['nodeShape']['options'][p]['shape'];
                if (this.params['nodeShape']['options'][p]['id'] == -1 && nodeId == -1)
                    defaultNodeShape = this.params['nodeShape']['options'][p]['shape'];
                if (nodeId != -1 && (this.params['nodeShape']['options'][p]['id'] == answer || $.inArray(this.params['nodeShape']['options'][p]['id'], answer) != -1))
                    return this.params['nodeShape']['options'][p]['shape'];
            }
        }
        return defaultNodeShape;
    }

    this.getEdgeColor = function(nodeId1, nodeId2) {
        var defaultEdgeColor = "#ccc";
        if (typeof this.params['edgeColor'] != "undefined") {
            if (typeof this.params['edgeColor']['questionId'] != "undefined" && typeof answers[this.params['edgeColor']['questionId'] + "-" + nodeId1 + "and" + nodeId2] != "undefined")
                var answer = answers[this.params['edgeColor']['questionId'] + "-" + nodeId1 + "and" + nodeId2].VALUE.split(",");
            else
                var answer = "";
            for (p in this.params['edgeColor']['options']) {
                if (this.params['edgeColor']['options'][p]['id'] == 0 && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                    defaultEdgeColor = this.params['edgeColor']['options'][p]['color'];
                if (this.params['edgeColor']['options'][p]['id'] == answer || $.inArray(this.params['edgeColor']['options'][p]['id'], answer) != -1)
                    return this.params['edgeColor']['options'][p]['color'];
            }
        }
        return defaultEdgeColor;
    }

    this.getEgoEdgeColor = function(nodeId1) {
        var defaultEdgeColor = "#ccc";
        if (typeof this.params['egoEdgeColor'] != "undefined") {
            if (typeof this.params['egoEdgeColor']['questionId'] != "undefined" && typeof answers[this.params['egoEdgeColor']['questionId'] + "-" + nodeId1] != "undefined")
                var answer = answers[this.params['egoEdgeColor']['questionId'] + "-" + nodeId1].VALUE.split(",");
            else
                var answer = "";
            for (p in this.params['egoEdgeColor']['options']) {
                if (this.params['egoEdgeColor']['options'][p]['id'] == "default" && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                    defaultEdgeColor = this.params['egoEdgeColor']['options'][p]['color'];
                if (this.params['egoEdgeColor']['options'][p]['id'] == answer || $.inArray(this.params['egoEdgeColor']['options'][p]['id'], answer) != -1)
                    return this.params['egoEdgeColor']['options'][p]['color'];
            }
        }
        return defaultEdgeColor;
    }

    this.getEdgeSize = function(nodeId1, nodeId2) {
        var defaultEdgeSize = 1;
        if (typeof this.params['edgeSize'] != "undefined") {
            if (typeof this.params['edgeSize']['questionId'] != "undefined" && typeof answers[this.params['edgeSize']['questionId'] + "-" + nodeId1 + "and" + nodeId2] != "undefined")
                var answer = answers[this.params['edgeSize']['questionId'] + "-" + nodeId1 + "and" + nodeId2].VALUE.split(",");
            else
                var answer = "";
            for (p in this.params['edgeSize']['options']) {
                if (this.params['edgeSize']['options'][p]['id'] == "default" && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                    defaultEdgeSize = this.params['edgeSize']['options'][p]['size'];
                if (this.params['edgeSize']['options'][p]['id'] == answer || $.inArray(this.params['edgeSize']['options'][p]['id'], answer) != -1)
                    return this.params['edgeSize']['options'][p]['size'];
            }
        }
        return defaultEdgeSize;
    }

    this.getEgoEdgeSize = function(nodeId1, nodeId2) {
        var defaultEdgeSize = 1;
        if (typeof this.params['egoEdgeSize'] != "undefined") {
            if (typeof this.params['egoEdgeSize']['questionId'] != "undefined" && typeof answers[this.params['egoEdgeSize']['questionId'] + "-" + nodeId1] != "undefined")
                var answer = answers[this.params['egoEdgeSize']['questionId'] + "-" + nodeId1].VALUE.split(",");
            else
                var answer = "";
            for (p in this.params['egoEdgeSize']['options']) {
                if (this.params['egoEdgeSize']['options'][p]['id'] == 0 && (answer == "" || parseInt(answer) == parseInt(study.VALUELOGICALSKIP) || parseInt(answer) == parseInt(study.VALUEREFUSAL) || parseInt(answer) == parseInt(study.VALUEDONTKNOW)))
                    defaultEdgeSize = this.params['egoEdgeSize']['options'][p]['size'];
                if (this.params['egoEdgeSize']['options'][p]['id'] == answer || $.inArray(this.params['egoEdgeSize']['options'][p]['id'], answer) != -1)
                    return this.params['egoEdgeSize']['options'][p]['size'];
            }
        }
        return defaultEdgeSize;
    }

    var alters2 = $.extend(true, {}, alters);
    if (starExpression != undefined) {
        nodes.push({
            'id': '-1',
            'label': this.params['egoLabel'],
            'x': Math.random(),
            'y': Math.random(),
            "type": this.getNodeShape(-1),
            "color": this.getNodeColor(-1),
            "size": this.getNodeSize(-1),
        })
    }
    for (a in alters) {
        nodes.push({
            'id': alters[a].ID.toString(),
            'label': alters[a].NAME + (typeof notes[alters[a].ID] != "undefined" ? " �" : ""),
            'x': Math.random(),
            'y': Math.random(),
            "type": this.getNodeShape(alters[a].ID),
            "color": this.getNodeColor(alters[a].ID),
            "size": this.getNodeSize(alters[a].ID),
        });
        if (starExpression != undefined) {
            if (evalExpression(starExpressionId, alters[a].ID, alters2[b].ID) == true) {
                edges.push({
                    "id": "-1_" + alters[a].ID,
                    "source": alters[a].ID.toString(),
                    "target": '-1',
                    "color": this.getEgoEdgeColor(alters[a].ID),
                    "size": this.getEgoEdgeSize(alters[a].ID),
                });
            }
        }
        var keys = Object.keys(alters2);
        delete alters2[keys[0]];
        if (expression != undefined) {
            for (b in alters2) {
                if (evalExpression(expressionId, alters[a].ID, alters2[b].ID) == true) {
                    edges.push({
                        "id": alters[a].ID + "_" + alters2[b].ID,
                        "source": alters2[b].ID.toString(),
                        "target": alters[a].ID.toString(),
                        "color": this.getEdgeColor(alters[a].ID, alters2[b].ID),
                        "size": this.getEdgeSize(alters[a].ID, alters2[b].ID),
                    });
                }
            }
        }
    }


    g = {
        nodes: nodes,
        edges: edges,
    };

    sizes = [];
    for (y in g.nodes) {
        sizes.push(g.nodes[y].size)
    }
    max_node_size = Math.max.apply(Math, sizes);

    sizes = [];
    for (y in g.edges) {
        sizes.push(g.edges[y].size)
    }
    max_edge_size = Math.max.apply(Math, sizes);

    setTimeout(function() {
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
        if (typeof graphs[expressionId] != "undefined") {
            savedNodes = JSON.parse(graphs[expressionId].NODES);
            for (var k in savedNodes) {
                var node = s.graph.nodes(k.toString());
                if (node) {
                    node.x = savedNodes[k].x;
                    node.y = savedNodes[k].y;
                }
            }
        } else {
            s.startForceAtlas2({
                "worker": false,
                "outboundAttractionDistribution": true,
                "speed": 2000,
                "gravity": 0.2,
                "jitterTolerance": 0,
                "strongGravityMode": true,
                "barnesHutOptimize": false,
                "totalSwinging": 0,
                "totalEffectiveTraction": 0,
                "complexIntervals": 500,
                "simpleIntervals": 1000
            });
            setTimeout(function(){
                s.stopForceAtlas2();
                if(typeof saveNodes != "undefined")
                    saveNodes();
                $('#fullscreenButton').prop('disabled', false);
            }, 5000);
        }
        s.refresh();
        initNotes(s);
    }, 1);
}

function fullscreen() {
    elem = document.getElementById("infovis");
    if (typeof elem.requestFullscreen != "undefined") {
        elem.requestFullscreen();
    } else if (typeof elem.msRequestFullscreen != "undefined") {
        elem.msRequestFullscreen();
    } else if (typeof elem.mozRequestFullScreen != "undefined") {
        elem.mozRequestFullScreen();
    } else if (typeof elem.webkitRequestFullscreen != "undefined") {
        elem.webkitRequestFullscreen();
    }
    graphWidth = $("#infovis").width()
    $("#infovis").height(screen.height);
    $("#infovis").width(screen.width);

    setTimeout(function() {
        document.addEventListener('webkitfullscreenchange', exitHandler, false);
        document.addEventListener('mozfullscreenchange', exitHandler, false);
        document.addEventListener('fullscreenchange', exitHandler, false);
        document.addEventListener('MSFullscreenChange', exitHandler, false);

    }, 500);

}

function toggleLabels() {
    var labelT = s.renderers[0].settings("labelThreshold");
    if (labelT == 1)
        s.renderers[0].settings({
            labelThreshold: 100
        });
    else
        s.renderers[0].settings({
            labelThreshold: 1
        });
    s.refresh();
}

function exitHandler() {
    if (document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement !== null) {
        $("#infovis").height(360);
        $("#infovis").width(graphWidth)
        $("#infovis canvas").height(360);
        $("#infovis canvas").width(graphWidth);
        $("#infovis canvas").attr("height", 360);
        $("#infovis canvas").attr("width", graphWidth);
        //window.dispatchEvent(new Event('resize'));
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));

            //s.cameras[0].goTo({ x: 0, y: 0, angle: 0, ratio: 1 });
            //s.refresh();
        }, 100);
        document.removeEventListener('webkitfullscreenchange', exitHandler, false);
        document.removeEventListener('mozfullscreenchange', exitHandler, false);
        document.removeEventListener('fullscreenchange', exitHandler, false);
        document.removeEventListener('MSFullscreenChange', exitHandler, false);
    }
}