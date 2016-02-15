function initNotes(){
    console.log("initializing notes..");
    graphNotes = 0;
    s.bind('clickNode',function(e){
        getNote(e.data.node);
    });

    var dragListener = new sigma.plugins.dragNodes(s, s.renderers[0]);
    dragListener.bind('drop', function(event) {
        if(event.data.node){
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

                type:'arrow'
            });
            saveNodes();
        }
        var node = s.graph.nodes('graphNote-' + graphNotes);

        node.x = x / 10 ;
        node.y = y / 10 ;
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
