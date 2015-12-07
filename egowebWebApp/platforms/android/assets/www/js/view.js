function timeBits(timeUnits)
{
    timeArray =[];
    bitVals = {
    	'BIT_YEAR' :1,
    	'BIT_MONTH' : 2,
    	'BIT_WEEK': 4,
    	'BIT_DAY' :8,
    	'BIT_HOUR' :16,
    	'BIT_MINUTE': 32,
    };
    for ( k in bitVals){
    	if(timeUnits & bitVals[k]){
    		timeArray.push(k);
    	}
    }
    return timeArray;
}

function view(id, interviewId, page)
{
	delete offset;
	if(page != null)
		currentPage = page;

	if(typeof interviewId != "undefined"){
		questions = buildQuestions(id, currentPage, interviewId);
	}else{
		questions = buildQuestions(id, currentPage);
		interviewId = '';
	}

	$('.orangebutton').show();
	$('#navigation').hide()

	counter = 0;
	$('#ERROR').html('');
	$('#ERROR').hide();

	questionOrder = [];
	if (Object.keys(questions).length >= 1){
		for (var l in questions) {
			if(typeof offset == "undefined")
				var offset = questions[l].ORDERING;
			if(questions[l].SUBJECTTYPE == "EGO_ID")
				questionOrder[questions[l].ORDERING] = questions[l].ID;
			else
				questionOrder[questions[l].ORDERING - offset] = questions[l].ID;
		}
	}

	for (var l in questions) {

		if(questions[l].SUBJECTTYPE == "EGO_ID")
			var k = questionOrder[counter];
		else if (questions[l].SUBJECTTYPE == "EGO" && parseInt(questions[l].ASKINGSTYLELIST) && Object.keys(questions).length > 1)
			var k = questionOrder[counter];
		else
			var k = l;

		if(counter == 0){
			if(questions[k].SUBJECTTYPE == "EGO_ID")
				$('.questionText').html(study.EGOIDPROMPT);
			else
				$('.questionText').html(interpretTags(questions[k].PROMPT, interviewId));

			audioFileExists("egowebaudio/" + id + "/" + questions[k].SUBJECTTYPE + "/" + questions[k].ID + ".mp3", $('.questionText'));
			if(questions[k].ANSWERTYPE == "ALTER_PROMPT"){
				audioFileExists("egowebaudio/" + id + "/STUDY/ALTERPROMPT.mp3", $('.questionText'));
				$(".questionText").css('width',"480px");
			}else{
				$(".questionText").removeAttr("style");
			}
			$('.questionText').show();
			$('.question form').html('');
		}

		if(k != 0){
			if(questions[k].SUBJECTTYPE == "ALTER"){
				array_id = questions[k].ID + "-" + questions[k].ALTERID1;
				if(interviewId)
					model[array_id] = db.queryRowObject("SELECT * FROM answer where questionId = " + questions[k].ID + " AND interviewId = " + interviewId + " AND alterId1 = " + questions[k].ALTERID1);
			}else if(questions[k].SUBJECTTYPE == "ALTER_PAIR"){
				array_id = questions[k].ID + "-" + questions[k].ALTERID1 + "and" + questions[k].ALTERID2;
				if(interviewId)
					model[array_id] = db.queryRowObject("SELECT * FROM answer where questionId = " + questions[k].ID + " AND interviewId = " + interviewId + " AND alterId1 = " + questions[k].ALTERID1 + " AND alterId2 = " + questions[k].ALTERID2);
			}else{
				array_id = questions[k].ID;
				if(interviewId)
					model[array_id] = db.queryRowObject("SELECT * FROM answer where questionId = " + questions[k].ID + " AND interviewId = " + interviewId);
			}
		}else{
			array_id = 0;
		}
		if(questions[k].ANSWERTYPE == "ALTER_PROMPT"){
			if(countAlters() < study.MAXALTERS)
				$('#ALTER_PROMPT').show();
			$('#alterListBox').show();
			displayAlters();
			$('#previous_alters').show();
			previousAlters();
		}else{
			$('#ALTER_PROMPT').hide();
			$('#alterListBox').hide();
			$('#previous_alters').hide();

		}

		if(!model[array_id])
			model[array_id] = new Answer;

		if(model[array_id].VALUE == study.VALUENOTYETANSWERED)
			model[array_id].VALUE = "";

		if(errorModel.getError(array_id)){
			$('#ERROR').html(errorModel.getError(array_id));
			$('#ERROR').show();
		}

		var answerInput = $('#ANSWERPRE').children().clone();
		answerInput.each(function(index){
			key = $(this).attr('class').slice(0,-5);
			$(this).attr('name', 'answer[' + array_id + ']['+ key +']');
			$(this).attr('id', 'Answer_' + array_id + '_'+ key);
			if(key == 'ID')
				$(this).val(model[array_id].ID);
			if(key == 'ALTERID1')
				$(this).val(questions[k].ALTERID1);
			if(key == 'ALTERID2')
				$(this).val(questions[k].ALTERID2);
			if(key == 'QUESTIONID')
				$(this).val(questions[k].ID);
			if(key == 'INTERVIEWID')
				$(this).val(interviewId);
		});
		$('.question form').append(answerInput);

		var formInput = $('#'+questions[k].ANSWERTYPE).children().clone();
		var newForm =$('#EMPTY').clone();

		if(questions[k].SUBJECTTYPE == "EGO_ID"){
		    var orangeText = $('#ORANGETEXT').clone();
		    orangeText.html(questions[k].PROMPT);
		    newForm.append(orangeText);
		    newForm.append('<br clear=all>');
		}

		skipList = new Object;
		if(parseInt(questions[k].DONTKNOWBUTTON))
		    skipList['DONT_KNOW'] = "Don't Know";
		if(parseInt(questions[k].REFUSEBUTTON))
		    skipList['REFUSE'] = "Refuse";

		formInput.each(function(index){
			columnWidth = 180;
			multi = 'multiRow';
			if($(this).attr('id') == "VALUE"){
				$(this).attr('name', 'answer[' + array_id + '][VALUE]');
				$(this).attr('id', 'Answer_' + array_id + '_value');
				$(this).attr('onchange', 'unSelectSkips("' + array_id + '")')
				$(this).val(model[array_id].VALUE);
				if(parseInt(questions[k].ASKINGSTYLELIST) && (questions[k].ANSWERTYPE == "TEXTUAL" || questions[k].ANSWERTYPE == "NUMERICAL")){
					columnWidth = 480 / (2 + Object.keys(skipList).length);
					if(columnWidth > 180)
						columnWidth = 180;
					if(counter == 0){
						//newForm.append('<div class="multiRow" style="width:300px">&nbsp;</div>');
						for (s in skipList){
							newForm.append('<div class="multiRow" style="width:'+columnWidth+'px">'+skipList[s] +'</div>');
						}
					}
					if(questions[k].SUBJECTTYPE == "ALTER")
						name =  getAlterName(questions[k].ALTERID1);
					else if(questions[k].SUBJECTTYPE == "ALTER_PAIR")
						name = getAlterName(questions[k].ALTERID2);
					else
						name = questions[k].CITATION;
						multi = 'multiRow';
					newForm.append('<div class="'+multi+'" style="width:20%; text-align:left">' + name + '</div>');
					newForm.append($(this));
					$(this).wrap('<div class="'+multi+'" style="width:20%; text-align:left"></div>');
				}else{
					newForm.append($(this));
				}
			}else if($(this).attr('id') == "MULTISELECT"){
				options = db.queryObjects("SELECT * FROM questionOption WHERE questionId = " + questions[k].ID  + " ORDER BY ORDERING").data;
				if(typeof model[array_id].VALUE != 'undefined')
					values = model[array_id].VALUE.split(',');
				else
					values = [];
				if(parseInt(questions[k].ASKINGSTYLELIST)){
					columnWidth = 80 / (Object.keys(options).length + Object.keys(skipList).length);
					if(columnWidth > 80)
						columnWidth = 80;
					if(counter == 0){
						newForm.append('<div class="multiRow" style="width:20%">&nbsp;</div>');
						for (o in options){
							newForm.append('<div class="multiRow" style="width:'+columnWidth+'%">'+options[o].NAME +'</div>');
						}
						for (s in skipList){
							newForm.append('<div class="multiRow" style="width:'+columnWidth+'%">'+skipList[s] +'</div>');
						}
						newForm.append('<br clear=all>');
					}
					multi = 'multiRow';
					if(typeof color == 'undefined' || color == ' colorB')
						color = ' colorA';
					else
						color = ' colorB';
					multi += color;
					if(questions[k].SUBJECTTYPE == "ALTER")
						name =  getAlterName(questions[k].ALTERID1);
					else if(questions[k].SUBJECTTYPE == "ALTER_PAIR")
						name = getAlterName(questions[k].ALTERID2);
					else
						name = questions[k].CITATION;
					newForm.append('<div class="'+multi+'" style="width:20%; text-align:left">' + name + '</div>');
				}
				var oi = 0;
				for (o in options){
					$(this).val(options[o].ID);
					$(this).attr('class', 'multiselect-' + array_id);

					$(this).attr('id', 'multiselect-' + array_id + "_" + oi);

					if(questions[k].MAXCHECKABLEBOXES == null)
						questions[k].MAXCHECKABLEBOXES = 1;
					$(this).attr('onclick', 'multiSelect("' + array_id + '", $(this),' + questions[k].MAXCHECKABLEBOXES + ')');
					if(values == String(options[o].ID) || values.indexOf(String(options[o].ID)) !=  -1)
						$(this).prop('checked', true);
					else
						$(this).prop('checked', false);
					if(parseInt(questions[k].ASKINGSTYLELIST)){
						display = '';
						multi = 'multiRow';
						multi += color;
					}else{
						display = "<label class='multiselect-" + array_id + "' for='multiselect-" + array_id + "_" + oi + "'>" + options[o].NAME +"</label>";
						multi = '';
					}
					newElement = $('#EMPTY').clone();
					newElement.toggleClass(multi);
					if(parseInt(questions[k].ASKINGSTYLELIST)){
						newElement.css('width',columnWidth + '%');
					}
					newElement.append($(this).clone());
					newElement.append(display);
					audioFileExists("egowebaudio/" + id + "/OPTION/" + options[o].ID + ".mp3", newElement);
					newForm.append(newElement);
					oi++;
				}

			}else if($(this).attr('id') == "DATEFIELDS"){
				var timeFields = timeBits(questions[k].TIMEUNITS);
				$(this).children().each(function(index){
					var date = model[array_id].VALUE.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
					var time = model[array_id].VALUE.match(/(\d{1,2}):(\d{1,2}) (AM|PM)/);

					if($(this).attr('id') == "YEAR" && typeof date != "undefined" && date && date.length > 3)
						$(this).val(date[3]);
					if($(this).attr('id') == "MONTH" && typeof date != "undefined" && date && date.length > 3)
						$('option[value="' + date[1] + '"]', this).prop("selected", true);
					if($(this).attr('id') == "DAY" && typeof date != "undefined" && date && date.length > 3)
						$(this).val(date[2]);
					if($(this).attr('id') == "HOUR" && typeof time != "undefined" && time && time.length > 2)
						$(this).val(time[1]);
					if($(this).attr('id') == "MINUTE" && typeof time != "undefined" && time && time.length > 2)
						$(this).val(time[2]);

					if(timeFields.indexOf('BIT_' + $(this).attr('id')) == -1){
						if(timeFields.indexOf('BIT_HOUR') != -1){
							if($(this).attr('id') == "AMPM"){
								$(this).attr('name', array_id + "AMPM");
								$(this).toggleClass('time-' + array_id);
								$(this).attr('onchange', 'dateValue("' + array_id + '")');
								return;
							}
							if($(this).attr('for') == "AMPM")
								return;
						}
						$(this).remove();
						return;
					}

					$(this).toggleClass('time-' + array_id);
					$(this).attr('onchange', 'dateValue("' + array_id + '")');
				});
				if(parseInt(questions[k].ASKINGSTYLELIST)){
				    multi = 'multiRow';
				}else{
				    multi = '';
				}
				newElement = $('#EMPTY').clone();
				newElement.toggleClass(multi);
				newElement.append("(HH:MM)");
				newElement.append($(this));
				newForm.append(newElement);
			}else if($(this).attr('id') == "TIMEFIELDS"){
				timeFields = timeBits(questions[k].TIMEUNITS);
				$(this).children().each(function(index){
					$(this).toggleClass('time-' + array_id);
					if($(this).attr('id') == "years" && model[array_id].VALUE.match(/(\d*)\sYEARS/))
						$(this).val(model[array_id].VALUE.match(/(\d*)\sYEARS/)[1]);
					if($(this).attr('id') == "months" && model[array_id].VALUE.match(/(\d*)\sMONTHS/))
						$(this).val(model[array_id].VALUE.match(/(\d*)\sMONTHS/)[1]);
					if($(this).attr('id') == "weeks" && model[array_id].VALUE.match(/(\d*)\sWEEKS/))
						$(this).val(model[array_id].VALUE.match(/(\d*)\sWEEKS/)[1]);
					if($(this).attr('id') == "days" && model[array_id].VALUE.match(/(\d*)\sDAYS/))
						$(this).val(model[array_id].VALUE.match(/(\d*)\sDAYS/)[1]);
					if($(this).attr('id') == "hours" && model[array_id].VALUE.match(/(\d*)\sHOURS/))
						$(this).val(model[array_id].VALUE.match(/(\d*)\sHOURS/)[1]);
					if($(this).attr('id') == "minutes" && model[array_id].VALUE.match(/(\d*)\sMINUTES/))
						$(this).val(model[array_id].VALUE.match(/(\d*)\sMINUTES/)[1]);
					$(this).attr('onchange', 'timeValue("' + array_id + '")');
				});
				newForm.append($(this));
			}
		});


		if(typeof model[array_id].OTHERSPECIFYTEXT != 'undefined'){
			otherValue = [];
			var otherPairs = model[array_id].OTHERSPECIFYTEXT.split(';;');
			for(p in otherPairs){
			    if(otherPairs[p].match(':')){
			    	kv = otherPairs[p].split(':');
			    	otherValue[kv[0]] = kv[1];
			    }
			}
		}

		if(Object.keys(skipList).length > 0){
			var skipForm = $('#SKIP').clone();
			$('input', skipForm).each(function(index){
				if(typeof skipList[$(this).attr('id')] != 'undefined'){
					var thisSkip = $(this).attr('id').slice(0);
					if(model[array_id].SKIPREASON == $(this).val())
						$(this).prop('checked', true);
					else
						$(this).prop('checked', false);
					skipContainer = $('#EMPTY').clone();
					if(parseInt(questions[k].ASKINGSTYLELIST)){
						$('.' + thisSkip + '_LABEL', skipForm).remove();
						skipContainer.css('width', columnWidth + "%");
						skipContainer.toggleClass(multi);
					}
					$(this).toggleClass(array_id + '-skipReason');
					$(this).attr('id', array_id + '-skipReason_' + thisSkip);
					$(this).attr('onclick', 'skipReason("' + array_id + '", $(this))');
					skipContainer.append($(this));

					$('.' + thisSkip + '_LABEL', skipForm).attr('for', array_id + '-skipReason_' + thisSkip);

					if(!parseInt(questions[k].ASKINGSTYLELIST))
						skipContainer.append($('.' + thisSkip + '_LABEL', skipForm));
					newForm.append(skipContainer);
				}
			});
		}

		if(questions[k].ANSWERTYPE != "ALTER_PROMPT")
			newForm.append('<br clear=all>');

		$('.question form').append(newForm);


		$('label.multiselect-' + array_id).each(function(index){
		    if($(this).html().match(/OTHER \(*SPECIFY\)*/i)){
		    	console.log($(this));
		    	display = '';
		    	val = '';
		    	if($('#' + $(this).attr('for')).prop('checked') != true)
		    		display = 'style="display:none"';
		    	else
		    		val = otherValue[$('#' + $(this).attr('for')).val()];
		    	$(this).after(
		    	'<input id="' + $('#' + $(this).attr('for')).val() + '" class="' + array_id +'_other" ' + display+ ' onchange="changeOther('+array_id+')" value="'+  val + '" style="margin:5px"/>'
		    	);
		    	$('#' + $(this).attr('for')).click(function(){
		    		toggleOther($('#' + $(this).val()));
		    	});
		    }
		});

		var answerInput = $('#ANSWERPOST').children().clone();
		answerInput.each(function(index){
			key = $(this).attr('class').slice(0,-5);
			$(this).attr('name', 'answer[' + array_id + ']['+ key +']');
			$(this).attr('id', 'Answer_' + array_id + '_'+ key);
			if(key == 'STUDYID')
				$(this).val(studyId);
			if(key == 'SKIPREASON')
				$(this).val(model[array_id].SKIPREASON);
			if(key == 'OTHERSPECIFYTEXT'){
				$(this).val(model[array_id].OTHERSPECIFYTEXT);
			}
			if(key == 'QUESTIONTYPE')
				$(this).val(questions[k].SUBJECTTYPE)
			if(key == 'ANSWERTYPE')
				$(this).val(questions[k].ANSWERTYPE)
		});
		$('.question form').append(answerInput);
		counter++;

		if(counter == Object.keys(questions).length){
			if(parseInt(questions[k].ASKINGSTYLELIST) && parseInt(questions[k].ALLBUTTON)){
				options = db.queryObjects("SELECT * FROM questionOption WHERE questionId = " + questions[k].ID).data;
				var newForm = $('#EMPTY').clone();
				columnWidth = 80 / (Object.keys(options).length + Object.keys(skipList).length);
				if(columnWidth > 80)
					columnWidth = 80;
				newForm.append('<div class="multiRow palette-sun-flower" style="width:20%; text-align:left">Set All</div>');
				for (o in options){
					checkbox = $("#MULTIPLE_SELECTION #MULTISELECT").clone();
					checkbox.val(options[o].ID);
					checkbox.attr('class', 'pageLevel multiselect');
					checkbox.attr('id', 'multiselect-' + array_id + "_pageLevel");
					if(questions[k].MAXCHECKABLEBOXES == null)
						questions[k].MAXCHECKABLEBOXES = 1;
					multi = 'multiRow palette-sun-flower';

					newElement = $('#EMPTY').clone();
					newElement.toggleClass(multi);
					if(parseInt(questions[k].ASKINGSTYLELIST)){
						newElement.css('width',columnWidth + '%');
					}
					newElement.append(checkbox.clone());
					newForm.append(newElement);
				}
				if(Object.keys(skipList).length > 0){
					var skipForm = $('#SKIP').clone();
					$('input', skipForm).each(function(index){
						if(typeof skipList[$(this).attr('id')] != 'undefined'){
							var thisSkip = $(this).attr('id').slice(0);
							skipContainer = $('#EMPTY').clone();
								$('.' + thisSkip + '_LABEL', skipForm).remove();
								skipContainer.css('width', columnWidth + "%");
								skipContainer.toggleClass(multi);
							$(this).attr('class', 'pageLevel skipReason');
							$(this).attr('id', 'skipReason_' + thisSkip);
							skipContainer.append($(this));
							newForm.append(skipContainer);
						}
					});
				}
				$('.question form').append(newForm);
				$('.pageLevel').change(function(){
					var selected = $(this);
					if($(this).is(":checked")){
						$( "input[class*='-skipReason']").prop("checked", false);
						$( "input[class*='multiselect-']").prop("checked", false);
						$( "input:checkbox[value='" + selected.val() + "']").each(function(index){
							console.log($(this));
							console.log(!$(this).hasClass("pageLevel"));
							if(!$(this).hasClass("pageLevel") && (($(this).attr('class').match(/multiselect-(.*)/) && $(this).attr('class').match(/multiselect-(.*)/).length > 1) || ($(this).attr('class').match(/(.*)-skipReason/) && $(this).attr('class').match(/(.*)-skipReason/).length > 1))){
								if($(this).attr('class').match(/multiselect-(.*)/))
									var multi = $(this).attr('class').match(/multiselect-(.*)/)[1];
								else
									var multi = $(this).attr('class').match(/(.*)-skipReason/)[1];
								var realVal = $("#Answer_" + multi + "_value");
								var values = realVal.val().split(',');
								var skipVal = $("#Answer_" + multi + "_SKIPREASON" ).val();
								if(realVal.val() == "" && $("#Answer_" + multi + "_SKIPREASON" ).val() == "NONE"){
									$(this).prop("checked", true);
									realVal.val(selected.val());
									if(selected.val() == "DONT_KNOW" || selected.val() == "REFUSE"){
										$("#Answer_" + multi + "_SKIPREASON" ).val(selected.val());
										realVal.val("");
									}
								}else{
									if(skipVal == "NONE"){
										for(var k in values){
											$(".multiselect-" +  multi + "[value='" + values[k] + "']").prop("checked", true);
										}
									}else{
										$("." +  multi + "-skipReason[value='" + skipVal + "']").prop("checked", true);
									}
								}
							}
						});
					}else{
						$( "input:checkbox[value='" + selected.val() + "']").each(function(index){
							if(($(this).attr('class').match(/multiselect-(.*)/) && $(this).attr('class').match(/multiselect-(.*)/).length > 1) || ($(this).attr('class').match(/(.*)-skipReason/) && $(this).attr('class').match(/(.*)-skipReason/).length > 1)){
								if($(this).attr('class').match(/multiselect-(.*)/)){
									var multi = $(this).attr('class').match(/multiselect-(.*)/)[1];
									var realVal = $("#Answer_" + multi + "_value");
									var values = realVal.val().split(',');
									for(var k in values){
										if(values[k] == selected.val()){
											$(this).prop("checked", false);
											realVal.val('');
										}
									}
								}else{
									var multi = $(this).attr('class').match(/(.*)-skipReason/)[1];
									var skipVal = $("#Answer_" + multi + "_SKIPREASON");
									if(selected.val() == skipVal.val()){
										$(this).prop("checked", false);
										$("#Answer_" + multi + "_SKIPREASON" ).val("NONE");
									}
								}
							}
						});
					}
				})
			}
		}
		if(questions[k].ANSWERTYPE == "CONCLUSION")
			$('#next.orangebutton').html('Finish');
		else
			$('#next.orangebutton').html('Next');

	}


	if(currentPage > 0)
		$('.graybutton').show();
	else
		$('.graybutton').hide();
	buildNav(currentPage, interviewId);
	$('body').scrollTop(0);

}

function unSelectSkips(array_id){
    $("#" + array_id + "-skipReason_DONT_KNOW").prop('checked', false);
    $("#" + array_id + "-skipReason_REFUSE").prop('checked', false);
    $('#Answer_' + array_id + "_SKIPREASON").val('NONE');
}

function audioFileExists(path, div){
	if(typeof LocalFileSystem == "undefined")
		return false;
    window.requestFileSystem(LocalFileSystem.PERSISTENT, 0, function(fileSystem){
        fileSystem.root.getFile(path, { create: false },
        function fileExists(file){
        	div.html(div.html() + '<a class="play-sound" onclick=\'playSound("' + file.toNativeURL() + '")\' href="#"><span class="fui-volume"></span></a>')
        }
        , null);
    }, null);
}