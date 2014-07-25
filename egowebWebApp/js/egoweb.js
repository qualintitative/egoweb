$(function(){
	$('body').on('hidden.bs.modal', '#myModal', function () {
	    $(this).removeData('bs.modal');
	});
})



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
		audio = new Audio(uri);
		audio.play();
	}
}

function uploadAudio(file, id, studyId, type, update){
	data = new FormData();
	data.append("userfile", file.files[0]);
	data.append("id", id);
	data.append("studyId", studyId);
	data.append("type", type);
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