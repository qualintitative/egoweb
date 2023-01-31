<?php
use app\helpers\Statistics;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerAssetBundle(\yii\web\JqueryAsset::className(), \yii\web\View::POS_HEAD);
use app\assets\VisualizeAsset;

VisualizeAsset::register($this);
$expressionId = 0;
?>
    <script>
        params = [];
        expressionId = <?= $expressionId; ?>;
        interviewId = <?= $interviewId ?>;
        function getAdjacencies(newExpressionId){
            url = rootUrl + "/data/visualize?expressionId=" + newExpressionId + "&interviewId=" + interviewId;
            document.location = url;
        }
    </script>
<?php

echo "<h3 class='col-sm-12'>Visualize &nbsp| &nbsp".Html::a($study->name, Url::to("/data/".$studyId)) . "<small>" . " &nbsp| &nbsp" . $interview->egoId."</small></h3>";
?>


<?php if ($interviewId): ?>
<div class="row">
<div id="visualize-app" class="col-sm-6">
        <?= $this->render('/authoring/network'); ?>
        <div class="form-group">
            <b-button class="btn btn-info" @click="refreshGraph">Refresh</b-button>
        </div>
    </div>

    <div id="visualizePlugin" class="col-sm-6">
    <div id="infovis"></div>
    </div>


</div>
<?php endif; ?>
<?php


if ($interviewId && $expressionId) {
    echo "<br clear=all>";
    $stats = new Statistics;
    $stats->initComponents($interviewId, $expressionId);
}

?>
<script>
new_question = <?php echo json_encode($new_question, ENT_QUOTES); ?>;
questions = <?php echo json_encode($questions, ENT_QUOTES); ?>;
expressions = <?php echo json_encode($expressions, ENT_QUOTES); ?>;
study = <?php echo json_encode($study->toArray(), ENT_QUOTES); ?>;
answers = <?php echo $answers ?>;
alters = <?php echo $alters ?>;
graphs = []; //<?php echo $graphs; ?>;
allNotes = <?php echo $allNotes; ?>;
notes = [];
for(k in study){
    study[k.toUpperCase()] = study[k];
}
for(n in expressions){
    for(k in expressions[n]){
        expressions[n][k.toUpperCase()] = expressions[n][k];
    }
}
for(n in questions){
    for(k in questions[n]){
        if(k != "options")
            questions[n][k.toUpperCase()] = questions[n][k];
    }
}
new Vue({
    el: '#visualize-app',
    data() {
        return {
            questions: questions,
            question: new_question,
            study: study,
            colors: [
                {value: '#000', text: 'black'},
                {value:'#ccc', text: 'gray'},
                {value:'#07f', text: 'blue'},
                {value:'#0c0', text: 'green'},
                {value:'#f80', text: 'orange'},
                {value: '#fd0', text: 'yellow'},
                {value: '#f00', text: 'red'},
                {value: '#c0f', text: 'purple'}
            ],
            nodeSizes: [
                {value:"1",text: '1'},
                {value:"2",text: '2'},
                {value:"3",text: '3'},
                {value:"4",text: '4'},
                {value:"5",text: '5'},
                {value:"6",text: '6'},
                {value:"7",text: '7'},
                {value:"8",text: '8'},
                {value:"9",text: '9'},
                {value:"10",text: '10'}
            ],
            nodeShapes: [
                {value:'circle', text: 'circle'},
                {value:'star', text: 'star'},
                {value:'diamond', text: 'diamond'},
                {value:'cross', text: 'cross'},
                {value:'equilateral', text: 'triangle'},
                {value:'square', text: 'square'},
            ],
            edgeSizes: [
                {value:"0.5",text: '0.5'},
                {value:"2",text: '2'},
                {value:"4",text: '4'},
                {value:"8",text: '8'},
            ],
            gradients: [
                {value:"red",text: 'red'},
                {value:"green",text: 'green'},
                {value:"blue",text: 'blue'},
                {value:"black",text: 'black'},
            ],
        }
    },
    created() {
        var numQuestions = [];
        var multiQuestions = [];
        var alterQs = [];
        var alterShapeQs = [];
        var alterQIds = [];
        var alterPairQs = [];
        var alterPairQIds = [];
        var alterExps = [];
        var alterPairExps = [];
        var alterQOptions = {};
        var alterPairQOptions = {};
        var alterShapeQOptions = {};
        alterQs.push({text:"Degree Centrality", value:"degree"})
        alterQOptions["degree"] = [{id:"degree",name:""}]
        alterQs.push({text:"Betweenness Centrality", value:"betweenness"})
        alterQOptions["betweenness"] = [{id:"betweenness",name:""}]
        alterQs.push({text:"Eigenvector Centrality", value:"eigenvector"})
        alterQOptions["eigenvector"] = [{id:"eigenvector",name:""}]

        for(k in this.questions){
            this.questions[k].numQuestions = numQuestions.slice();
            if(this.questions[k].answerType == "NUMERICAL"){
                console.log(this.questions[k])
                numQuestions.push({text:this.questions[k].title,value:this.questions[k].id});
            }
            if (this.questions[k].answerType == "MULTIPLE_SELECTION") {
                multiQuestions.push({text:this.questions[k].title,value:this.questions[k].id});
            }

            if (this.questions[k].subjectType == "ALTER"){
                alterQs.push({text:this.questions[k].title, value:this.questions[k].id})
                alterQOptions[questions[k].id] =  questions[k].options;
                alterShapeQs.push({text:this.questions[k].title, value:this.questions[k].id})
                alterShapeQOptions[questions[k].id] =  questions[k].options;
                alterQIds.push(parseInt(this.questions[k].id))
            }
            if (this.questions[k].subjectType == "ALTER_PAIR") {
                alterPairQs.push({text:this.questions[k].title, value:this.questions[k].id})
                alterPairQOptions[questions[k].id] =  questions[k].options;
                alterPairQIds.push(parseInt(this.questions[k].id));
            }   
        }
        for(k in expressions){
            if(alterQIds.indexOf(parseInt(expressions[k].questionId)) != -1){
                alterExps.push(expressions[k])
                alterQs.push({text:expressions[k].name, value:"expression_" + expressions[k].id})
                alterShapeQs.push({text:expressions[k].name, value:"expression_" + expressions[k].id})
                alterQOptions["expression_" + expressions[k].id] = [{id:1,name:"True"},{id:0,name:"False"}]
                alterShapeQOptions["expression_" + expressions[k].id] = [{id:1,name:"True"},{id:0,name:"False"}]
            }
            if(alterPairQIds.indexOf(parseInt(expressions[k].questionId)) != -1)
                alterPairExps.push(expressions[k])
        }
            this.question.alterQs = alterQs;
            this.question.alterPairQs = alterPairQs;
            this.question.alterShapeQs = alterShapeQs;
            this.question.alterExps = alterExps;
            this.question.alterPairExps = alterPairExps;
            this.question.alterQOptions = alterQOptions;
            this.question.alterPairQOptions = alterPairQOptions;
            this.question.alterShapeQOptions = alterShapeQOptions;
            this.question.multiQuestions = [];
            for(m in multiQuestions){
                if(multiQuestions[m].value != this.question.id)
                    this.question.multiQuestions.push(multiQuestions[m])
            }
    
        var defaultParams = {
            nodeColor:{questionId:'', options:[{id:-1, color:"#000"}, {id:'default', color:"#000"}]},
            nodeSize:{questionId:'', options:[{id:-1, size:2}, {id:'default', size:2}]},
            nodeShape:{questionId:'', options:[{id:-1, shape:'circle'},{id:'default', shape:'circle'}]},
            edgeColor:{questionId:'', options:[{id:'default', color:"#000"}]},
            edgeSize:{questionId:'', options:[{id:'default', size:1}]},
            egoEdgeColor:{questionId:'', options:[]},
            egoEdgeSize:{questionId:'', options:[]},
        }
        if(this.question.networkParams == "" || this.question.networkParams == null){
            this.question.nParams = defaultParams
        }else{
            this.question.nParams = JSON.parse(this.question.networkParams);
            for(p in defaultParams){
                var egoOption = efaultParams[p].options[0];
                var defaultOption = defaultParams[p].options[1];
                var newOptions = [];
                if(typeof this.question.nParams[p] == "undefined"){
                    this.question.nParams[p] = defaultParams[p];
                }else{
                    if(p == "nodeColor" || p == "nodeSize" || p == "nodeShape"){
                        for(var i = 0; i < this.question.nParams[p].options.length; i++){
                            if(this.question.nParams[p].options[i].id == "default")
                                defaultOption = this.question.nParams[p].options[i];
                            else if(this.question.nParams[p].options[i].id == -1)
                                egoOption = this.question.nParams[p].options[i];
                            else
                                newOptions.push(this.question.nParams[p].options[i]);
                        }
                        newOptions.unshift(defaultOption);
                        newOptions.unshift(egoOption);
                        this.question.nParams[p].options = newOptions;
                    }
                }
            }
        }
    },
    mounted() {
        var self = this;
    },
    methods: {
        forceUpdate() {
            this.question.networkParams = JSON.stringify(this.question.nParams);
            for(k in this.question){
                console.log(k);
                this.question[k.toUpperCase()] = this.question[k];
            }
            this.$forceUpdate();
        },
        refreshGraph() {
            if(typeof s != "undefined" && s.length > 0){
                sIndex = s.length - 1;
                s[sIndex].stopForceAtlas2();
                s = [];
                $("#infovis").empty();
            }
            initStats(this.question);
        },
        resetParams(param) {
            console.log(param)
            var newOptions = [];
            var defaultOption, egoOption;
            for(var i = 0; i < this.question.nParams[param].options.length; i++){
                if(this.question.nParams[param].options[i].id == "default")
                    defaultOption = this.question.nParams[param].options[i];
                if(this.question.nParams[param].options[i].id == -1)
                    egoOption = this.question.nParams[param].options[i];
            }
            if(param == "nodeColor" || param == "nodeSize"){
                var newOptions = [egoOption, defaultOption];
                var options = this.question.alterQOptions[this.question.nParams[param].questionId];
            }else{ 
                if(param == "nodeShape" || param == "egoEdgeColor" || param == "egoEdgeSize")
                    var options = this.question.alterShapeQOptions[this.question.nParams[param].questionId];
                else 
                    var options = this.question.alterPairQOptions[this.question.nParams[param].questionId];
                if(param == "egoEdgeColor" || param == "egoEdgeSize")
                    var newOptions = [];
                else
                    var newOptions = [defaultOption];
            }

            typeName = param.toLowerCase().replace("ego","").replace("edge","").replace("node","");

            for(k in options){
                var data = {};
                data["id"] = options[k].id;
                console.log(options[k])

                if(param == "egoEdgeColor")
                    data[typeName] = "#000";
                else if(param == "egoEdgeSize")
                    data[typeName] = "2";
                else
                    data[typeName] = newOptions[0][typeName];

                console.log(data)
                newOptions.push(data);
            }
            this.question.nParams[param].options = newOptions;
            this.question.networkParams = JSON.stringify(this.question.nParams)
            for(k in this.question){
                this.question[k.toUpperCase()] = this.question[k];
            }
            console.log(this.question.nParams[param].options);
            this.$forceUpdate();
        },

    }
})
</script>
