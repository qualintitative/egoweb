
<script src='/www/js/1.0.3/sigma.min.js'></script>
<script src='/www/js/1.0.3/plugins/sigma.plugins.dragNodes.js'></script>
<script src='/www/js/1.0.3/plugins/shape-library.js'></script>
<script src='/www/js/1.0.3/plugins/sigma.renderers.customShapes.min.js'></script>
<script src='/www/js/1.0.3/plugins/sigma.layout.forceAtlas2.min.js'></script>
<script src='/www/js/plugins/sigma.notes.js'></script>
<script src='/www/js/jquery-1.12.4.min.js'></script>
<script src='/www/js/egoweb.js'></script>
<script>
question = <?php echo $question; ?>;
graphs = <?php echo $graphs; ?>;
alters = <?php echo json_encode($alters); ?>;
questions = <?php echo $questions; ?>;
expressions = <?php echo $expressions; ?>;
answers = <?php echo $answers; ?>;
study = <?php echo json_encode($study); ?>;
notes = [];
initStats(question);
</script>
<div class="col-sm-12 pull-left">
	<?php echo "<h2 class='margin-top-10'>" . $study->name . " &nbsp| &nbsp" . $interview->egoid ."</h2>"; ?>
</div>
<div id="infovis" style="width:80%;height:80%;"></div>
<?php
foreach ($notes as $note) {
    if (is_numeric($note->alterId)) {
        $label = $alters[$note->alterId]['NAME'];
    } else {
        $label = str_replace("graphNote-", "", $note->alterId);
    }
    echo "<div style='width:50%;float:left;padding-right:20px;clear:both' class=''><h3>" . $label . " </h3><small>$note->notes</small></div>";
}
?>