function getNote(node){
	var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + node.id;
	$.get(url, function(data){
		$("#left-container").html(data);

	});
}

function saveNote(){
	var noteContent = $("#Note_notes").val();
	$.post("/data/savenote", $("#note-form").serialize(), function(nodeId){
		var node = s.graph.nodes(nodeId);
		if(node && !node.id.match(/graphNote/) && !node.label.match("�"))
			node.label = node.label + " �";
		s.refresh();
		var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + nodeId;
		$.get(url, function(data){
			notes[nodeId] = noteContent;
			$("#left-container").html(data);
		});
	});
}

function deleteNote(){
	$.post("/data/deletenote", $("#note-form").serialize(), function(data){
		if(!isNaN(data)){
			var node = s.graph.nodes(data);
			node.label = node.label.replace(" �","");
			s.refresh();
			$("#left-container").html("");
		}else{
			delete notes[data];
			s.graph.dropNode(data);
			saveNodes();
			$("#left-container").html("");
			graphNotes = 0;
			for(k in notes){
				if(k.match(/graphNote/)){
					noteId = parseInt(k.match(/graphNote-(\d+)/)[1]);
					if(noteId > graphNotes)
						graphNotes = noteId;
				}
			}
			s.refresh();
		}
	});
}

function initNotes(s){
	graphNotes = 0;
	s.bind('clickNode',function(e){
		getNote(e.data.node);
	});
	var _dragListener = new sigma.events.drag(s.renderers[0]);
	_dragListener.bind('drop', function(e) {
	    if(e.data.node){
		    saveNodes();
	    }
	});
	s.bind('doubleClickStage', function(e) {
		x = e.data.captor.x;
		y = e.data.captor.y;
		if(graphNotes == 0 || typeof notes['graphNote-' + graphNotes] != "undefined"){
			graphNotes++;
			s.graph.addNode({
				id: 'graphNote-' + graphNotes,
				label: graphNotes.toString(),
				size: 4,
				x: x/10,
				y: y/10,
				dX: 0,
				dY: 0,
				type:'arrow'
			});
			saveNodes();
		}
		var node = s.graph.nodes('graphNote-' + graphNotes);
		node.x = x / 10;
		node.y = y / 10;
		s.refresh();
	});

	for(k in notes){
		if(k.match(/graphNote/)){
			noteId = k.match(/graphNote-(\d+)/)[1];
			s.graph.addNode({
				id: 'graphNote-' + noteId,
				label: noteId.toString(),
				size: 4,
				x: Math.floor((Math.random() * 100) + 1),
				y: Math.floor((Math.random() * 100) + 1),
				dX: 0,
				dY: 0,
				type:'arrow'
			});
			graphNotes = parseInt(noteId);
		}
	}
}
