noteBar = [
    ['style', ['bold', 'italic', 'underline', 'clear']],
    ['fontname', ['fontname']],
    ['fontsize', ['fontsize']],
    ['color', ['color']],
    ['para', ['paragraph']],
    ['misc', ['hr','link','picture', 'codeview']],
];

jQuery.browser = {};
(function () {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        jQuery.browser.msie = true;
        jQuery.browser.version = RegExp.$1;
    }
})();
$(function(){
	$('body').on('hidden.bs.modal', '#myModal', function () {
	    $(this).removeData('bs.modal');
	});

})


function updatePrompt(url) {
    $.get(url,function(data){
         $('#edit-alterPrompt').html(data);
     });
    return false;
}

function deletePrompt(url) {
    $.get(url,function(data){
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
    s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "-";

    var uuid = s.join("");
    return uuid;
}

var audio = new Audio();
function playSound(uri){
	if(audio.paused){
        console.log(uri);
		audio = new Audio(uri);
		audio.play();
	}
}

function uploadAudio(file, id, studyId, type, update){
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
			setTimeout(function(){$('#myModal').modal('hide');$('.close').click()},0);
		}
	});
}

function deleteAudio(id, studyId, type, update){
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
			setTimeout(function(){
			$('#myModal').modal('hide');
			$('.close').click();
						update.html(data);

			},0);
		}
	});
}

function loadAudio(uri)
{
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
  var commentSripper = new RegExp('<!--(.*?)-->','g');
  var output = output.replace(commentSripper, '');
  var tagStripper = new RegExp('<(/)*(meta|link|span|\\?xml:|st1:|o:|font)(.*?)>','gi');
  // 3. remove tags leave content if any
  output = output.replace(tagStripper, '');
  // 4. Remove everything in between and including tags '<style(.)style(.)>'
  var badTags = ['style', 'script','applet','embed','noframes','noscript'];

  for (var i=0; i< badTags.length; i++) {
    tagStripper = new RegExp('<'+badTags[i]+'.*?'+badTags[i]+'(.*?)>', 'gi');
    output = output.replace(tagStripper, '');
  }
  // 5. remove attributes ' style="..."'
  var badAttributes = ['style', 'start'];
  for (var i=0; i< badAttributes.length; i++) {
    var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"','gi');
    output = output.replace(attributeStripper, '');
  }
  return output;
}
