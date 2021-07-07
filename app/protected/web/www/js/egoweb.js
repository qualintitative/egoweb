controllers = ["/interview", "/data", "/importExport", "/admin", "/dyad", "/authoring"];
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