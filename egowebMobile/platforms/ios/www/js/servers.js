$(function(){
	db.catalog.setPersistenceScope(db.SCOPE_LOCAL);

	setTimeout(function(){

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
function verifyServer(address, data){
	console.log(data);
	var server = db.queryRowObject("SELECT * FROM server WHERE address = '" + address + "'");
	$('#status').html("Authenticating server...");
	var url = 'http://' + address + '/mobile/authenticate';
	$.ajax({
		url: url,
		type: 'POST',
		data:data,
		crossDomain: true,
		success: function(data){
			if(!isNaN(data)){
				console.log(server);
				userId = data;
				$('#' + server.ID + '_serverForm').hide();
				getStudyList(server);
				$('#addServerButton').show();
			}else{
				$('#status').html($('#status').html() + 'validation failed');
			}
		},
		error: function(data) {
			$('#status').html($('#status').html() + 'error');
		}
	});
}

function addServer(address){
	check = db.queryValue("SELECT address FROM server WHERE address = '" + address + "'");
	if(check != address){
	var url = 'http://' + address + '/mobile/check';
		$.ajax({
			url: url,
			type: 'GET',
			crossDomain: true,
			success: function(data){
				if(data == "success"){
					newId = db.queryValue("SELECT id FROM server ORDER BY id DESC");
					if(!newId)
						newId = 0;
					server = [parseInt(newId) + 1,address];
					db.catalog.getTable('server').insertRow(server);
					db.commit();
					$('#status').html('successfully added server');
					$("#page").html($("#serverList").html());
					listServers($("#list"));
				}else{
					$('#status').html($('#status').html() + 'no response from server');
				}
			},
			error: function(data) {
				$('#status').html($('#status').html() + 'error connecting to server');
			}
		});

	}else{
		$('#status').html('server already exists');
	}
}
function cancelAdd(){
	$("#page").html($("#serverList").html());
	listServers($("#list"));
	$('#addServerButton').show();
}

function listServers(div){
	console.log(div);
	servers = db.queryObjects("SELECT * FROM server").data;
	console.log(servers);
	div.html('');
	for (k in servers){
		console.log( servers[k].ADDRESS);
		div.append("<hr>");
		div.append('<div id="' +servers[k].ID + '" style="width:100%; clear:both; text-align:left"><h1>' + servers[k].ADDRESS + '</h1></div>');
		div.append('<form id = "' +servers[k].ID + '_serverForm">' +
		'<div class="multiRow" style="width:100px;text-align:left">Username</div><input type=text name="LoginForm[username]" /><br style="clear:both">' +
		'<div class="multiRow" style="width:100px;text-align:left">Password</div><input type=password name="LoginForm[password]" /><br style="clear:both">'+
		'<button onclick=\'verifyServer("'+ servers[k].ADDRESS + '", $("#' +servers[k].ID + '_serverForm").serialize()); return false\'>Connect</button></form>');
	}
}