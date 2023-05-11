<?php

use yii\helpers\Html;
use common\widgets\Alert;

?>
<?= $this->render('/layouts/nav', ['study' => $study]); ?>

<div id="authoring-app" class="mt-md-5">
    <div class="row py-3">
        <div class="col-12 form-row">
            <div class="col-4">
                <b-form-select v-model="filterType" class="mb-3" id="filter-type" @change='changeFilterType'>
                    <b-form-select-option value="">No Filter</b-form-select-option>
                    <b-form-select-option value="expression">Filter by Expression Name</b-form-select-option>
                    <b-form-select-option value="expType">Filter by Expression Type</b-form-select-option>
                    <b-form-select-option value="question">Filter by Question Title</b-form-select-option>
                </b-form-select>
            </div>
            <div class="col-8 form-row" v-if="filterType == 'expression'">
                <div class="col-6">
                    <input class="form-control" v-model="filterName" v-on:keyup="expFilterKey" type=text />
                </div>
                <div class="col-6">
                    <button class="btn btn-primary" type="button" @click="filterExpByName()">Filter</button>
                </div>
            </div>
            <div class="col-8 form-row" v-if="filterType == 'expType'">
                <div class="col-6">
                    <b-form-select v-model="filterExpType" class="mb-3" id="filter-exp-type">
                        <b-form-select-option value="Simple">Simple</b-form-select-option>
                        <b-form-select-option value="Counting">Counting</b-form-select-option>
                        <b-form-select-option value="Comparison">Comparison</b-form-select-option>
                        <b-form-select-option value="Compound">Compound</b-form-select-option>
                        <b-form-select-option value="Name Generator">Name Generator</b-form-select-option>
                    </b-form-select>
                </div>
                <div class="col-6">
                    <button class="btn btn-primary" type="button" @click="filterExpByType()">Filter</button>
                </div>
            </div>
            <div class="col-8 form-row" v-if="filterType == 'question'">
                <div class="col-6">
                    <input class="form-control" v-model="filterQ" v-on:keyup="qFilterKey" type=text />
                </div>
                <div class="col-6">
                    <button class="btn btn-primary" type="button" @click="filterExpByQ()">Filter</button>
                </div>
            </div>
        </div>
        <div class="col-8 order-2" id="sticky-sidebar">
            <div class="sticky-top">
                <div class="nav flex-column ml-2 mr-2">
                    <?= Alert::widget() ?>
                    <router-view v-bind:expressions="expressions"></router-view>
                </div>
            </div>
        </div>
        <div class="col-4 mb-3">
            <ul class="list-group">
                <li :class="$route.params.id == expression.id ? 'bg-dark list-group-item' : 'list-group-item'" v-for="(expression, k) in expressionList" :key="expression.id">
                    <router-link :to="'/' + expression.id">{{expression.name ? expression.name : "New Expression"}}</router-link>
                </li>
            </ul>
        </div>


    </div>
</div>
<?= Html::beginForm(['/authoring/ajaxdelete/' . $study['id']], 'post', ['id' => 'deleteExpression', "class" => "d-none"]) ?>
<input type="hidden" id="deleteExpressionId" name="expressionId">
<?= Html::endForm() ?>
<script type="text/x-template" id="expressionEditor">
    <form v-if="id" method="post" action="/authoring/expressions/<?php echo $study['id']; ?>">

<input type="hidden" name='_csrf-protected' value= '<?php echo Yii::$app->request->getCsrfToken(); ?>' />
<input type="hidden" v-model="expressions[id].studyId"  name="Expression[studyId]" id="Expression_studyId">
<input type="hidden" v-model="expressions[id].id"  name="Expression[id]" id="Expression_id">
    <b-form-select v-if="id == 0" v-model="selected" class="mb-3" @change="changeType" id="form-type">
        <b-form-select-option value="Simple">Simple</b-form-select-option>
        <b-form-select-option value="Counting">Counting</b-form-select-option>
        <b-form-select-option value="Comparison">Comparison</b-form-select-option>
        <b-form-select-option value="Compound">Compound</b-form-select-option>
        <b-form-select-option value="Name Generator">Name Generator</b-form-select-option>
    </b-form-select>
    
    <div class="row form-group">
        <label for="Expression_name" class="col-md-2 col-form-label">Name</label>
        <div class="col-md-10">
            <input type="text" v-model="expressions[id].name" class="form-control" name="Expression[name]" id="Expression_name">
        </div>
    </div>

    <input type="hidden" v-model="expressions[id].type" class="form-control" name="Expression[type]" id="Expression_type">


    <div v-if="selected == 'Simple'">

        <div class="row form-group">
            <label for="Expression_questionId" class="col-md-2 col-form-label">About</label>
            <div class="col-md-10">
                <b-form-select v-model="expressions[id].questionId"
                    value-field="id"
                    id="Expression_questionId"
                    name="Expression[questionId]"
                    text-field="title"
                    :options="questions"
                    @change="changeEQ($event)"
                    >
                    <template #first>
                        <b-form-select-option value="null">-- Select a question --</b-form-select-option>
                    </template>
                </b-form-select>
            </div>
        </div>


        <div v-if="expressions[id].questionId > 0">
                
            <div v-if="expressions[id].type == 'Selection'">
                <div class="row form-group">
                    <label for="Expression_name" class="col-md-2 col-form-label">TRUE if</label>
                    <div class="col-md-3">
                        <b-form-select v-model="expressions[id].operator" name="Expression[operator]">
                        <b-form-select-option value="Some" selected>Some</b-form-select-option>
                        <b-form-select-option value="All">All</b-form-select-option>
                        <b-form-select-option value="None">None</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-md-7">of the following options are selected</div>
                </div>
                <input type="hidden" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">
                <b-form-checkbox-group
                v-model="expressions[id].selectedOptions"
                :options="questions[expressions[id].questionId].optionsList"
                class="mb-3"
                value-field="id"
                text-field="name"
                disabled-field="notEnabled"
                stacked
                @change="checkVal($event,id)"
                ></b-form-checkbox-group>
            </div>

            <div v-if="expressions[id].type == 'Number'">
            <div class="row form-group">

                <label class="col-sm-5">Expression is true for an answer is</label>
                <b-form-select v-model="expressions[id].operator"  name="Expression[operator]" class="col-sm-3 mb-3">
                <b-form-select-option value="Greater">Greater Than</b-form-select-option>
                <b-form-select-option value="GreaterOrEqual">Greater Or Equal To</b-form-select-option>
                <b-form-select-option value="Equals" selected>Equals</b-form-select-option>
                <b-form-select-option value="LessOrEqual">Less Or Equal To</b-form-select-option>
                <b-form-select-option value="Less">Less Than</b-form-select-option>
                </b-form-select>
               
                    <div class="col-md-3">
                        <input type="text" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">
                    </div>
                </div>
            </div>
            <div v-if="expressions[id].type == 'Text'">
                <div class="row form-group">
                    <label for="Expression_questionId" class="col-md-3 col-form-label"><b>TRUE</b> if answer</label>
                    <div class="col-md-4">
                        <b-form-select v-model="expressions[id].operator"  name="Expression[operator]">
                            <b-form-select-option value="Contains" selected>CONTAINS</b-form-select-option>
                            <b-form-select-option value="Equals">EQUALS</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3">
                    <b-form-select v-model="expressions[id].resultForUnanswered" name="Expression[resultForUnanswered]" class="mb-3">
                    <b-form-select-option value="0" selected>False</b-form-select-option>
                    <b-form-select-option value="1">True</b-form-select-option>
                    </b-form-select>
                </div>
                <label for="Expression_name" class="col-md-9 col-form-label">if the question is unanswered.</label>
            </div>
        </div>
    </div>

    <div v-if="expressions[id].type == 'Compound'">
    <input type="hidden" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">
        <div class="row form-group">
            <label for="Expression_name" class="col-md-2 col-form-label">TRUE if</label>
            <div class="col-md-3">
                <b-form-select v-model="expressions[id].operator"  name="Expression[operator]">
                <b-form-select-option value="Some" selected>Some</b-form-select-option>
                <b-form-select-option value="All">All</b-form-select-option>
                <b-form-select-option value="None">None</b-form-select-option>
                </b-form-select>
            </div>
            <div class="col-md-7">of the following options are selected</div>
        </div>
        <b-form-checkbox-group
        v-model="expressions[id].selectedOptions"
        :options="normalExpressions"
        class="mb-3"
        value-field="id"
        text-field="name"
        disabled-field="notEnabled"
        @change="checkVal($event,id)"
        stacked
        ></b-form-checkbox-group>
        <div class="row form-group">
            <div class="col-md-3">
                <b-form-select v-model="expressions[id].resultForUnanswered" class="mb-3" name="Expression[resultForUnanswered]">
                <b-form-select-option value="0" selected>False</b-form-select-option>
                <b-form-select-option value="1">True</b-form-select-option>
                </b-form-select>
            </div>
            <label for="Expression_name" class="col-md-9 col-form-label">if the question is unanswered.</label>
        </div>
    </div>

    <div v-if="expressions[id].type == 'Counting'">
        <div class="row form-group">
            <label for="Expression_name" class="col-md-2 col-form-label">VALUE is</label>
            <div class="col-md-2">
                <input id="times" v-model="expressions[id].multiplier" class="form-control" @change="buildVal($event, id)">
            </div>
            <label class="col-sm-2">times the</label>
            <b-form-select v-model="expressions[id].operator"  name="Expression[operator]" class="col-sm-2">
            <b-form-select-option value="Sum" selected>Sum</b-form-select-option>
            <b-form-select-option value="Count">Count</b-form-select-option>
            </b-form-select>
            <div class="col-md-3">of the selected </div>
        </div>
 
        <input type="hidden" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">

        <b>COUNTING</b> expressions
        <b-form-checkbox-group
        v-model="expressions[id].selectedExpressions"
        :options="expressions[id].countExpressions"
        class="mb-3"
        value-field="id"
        text-field="name"
        disabled-field="notEnabled"
        stacked
        @change="buildVal($event,id)"
        ></b-form-checkbox-group>
        <b>NUMERIC</b> questions
        <b-form-checkbox-group
        v-model="expressions[id].selectedQuestions"
        :options="countQuestions"
        class="mb-3"
        value-field="id"
        text-field="title"
        disabled-field="notEnabled"
        stacked
        @change="buildVal($event,id)"
        ></b-form-checkbox-group>
    </div>

    <div v-if="expressions[id].type == 'Comparison'">
    <input type="hidden" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">

        <div class="row form-group">
            <label class="col-md-2 col-form-label">TRUE if</label>
            <div class="col-md-3">
                <input id="compare" v-model="expressions[id].compare" class="form-control" @change="compVal($event, id)" placeholder="Number">
                </div>

                <label class="col-sm-1">is</label>
                <b-form-select v-model="expressions[id].operator"  name="Expression[operator]" class="col-sm-3">
                <b-form-select-option value="Greater">Greater Than</b-form-select-option>
                <b-form-select-option value="GreaterOrEqual">Greater Or Equal To</b-form-select-option>
                <b-form-select-option value="Equals" selected>Equals</b-form-select-option>
                <b-form-select-option value="LessOrEqual">Less Or Equal To</b-form-select-option>
                <b-form-select-option value="Less">Less Than</b-form-select-option>
                </b-form-select>
        </div>
        <b-form-select 
        v-model="expressions[id].selectedExpressions"
        :options="expressions[id].countExpressions"
        class="mb-3"
        value-field="id"
        id="expressionId"
        text-field="name"
        @change="compVal($event,id)"
        >
        <template #first>
            <b-form-select-option value="" disabled>-- Please select an expression --</b-form-select-option>
        </template>
        </b-form-select>
    </div>

    <div v-if="expressions[id].type == 'Name Generator'">
    <input type="hidden" v-model="expressions[id].value" class="form-control" name="Expression[value]" id="Expression_value">

    <div class="row form-group">
            <label for="Expression_name" class="col-md-2 col-form-label">TRUE if</label>
            <div class="col-md-3">
                <b-form-select v-model="expressions[id].operator"  name="Expression[operator]">
                <b-form-select-option value="Some" selected>Some</b-form-select-option>
                <b-form-select-option value="All">All</b-form-select-option>
                <b-form-select-option value="None">None</b-form-select-option>
                </b-form-select>
            </div>
            <div class="col-md-7">of the following options are selected</div>
        </div>
    <b-form-checkbox-group
        v-model="expressions[id].selectedQuestions"
        :options="nameGenQuestions"
        class="mb-3"
        value-field="id"
        text-field="title"
        disabled-field="notEnabled"
        stacked
        @change="checkVal($event,id)"
        ></b-form-checkbox-group>
    </div>

    <div v-if="id != 0" class="btn-group col row mt-3">
        <button class="btn btn-success">Save</button>
        <b-button class="btn btn-danger" @click="deleteExpression(id)">Delete</b-button>
    </div>
    <div v-if="id == 0" class="btn-group col-4 row mt-3">
        <button class="btn btn-primary">Create</button>
    </div>
</form>
</script>

<script>
    study = <?php echo json_encode($study, ENT_QUOTES); ?>;

    questions = <?php echo json_encode($questions, ENT_QUOTES); ?>;
    expressions = <?php echo json_encode($expressions, ENT_QUOTES); ?>;
    expressionList = <?php echo json_encode($expressionList, ENT_QUOTES); ?>;
    countQuestions = <?php echo json_encode($countQuestions, ENT_QUOTES); ?>;
    countExpressions = <?php echo json_encode($countExpressions, ENT_QUOTES); ?>;
    nameGenQuestions = <?php echo json_encode($nameGenQuestions, ENT_QUOTES); ?>;


    function dynamicPropsFn(route) {
        const now = new Date()
        return {
            name: (now.getFullYear() + parseInt(route.params.years)) + '!'
        }
    }

    ExpressionEditor = Vue.component('expression-editor', {
        template: '#expressionEditor',
        props: ['id'],
        data() {
            return {
                selected: 'Simple',
                questions: questions,
                expressions: expressions,
                countQuestions: countQuestions,
                normalExpressions: [],
                nameGenQuestions: nameGenQuestions,
            }
        },

        created() {
            if (this.id && this.id != 0) {
                if (this.expressions[this.id].type == "Compound")
                    this.selected = "Compound";
                else if (this.expressions[this.id].type == "Counting")
                    this.selected = "Counting";
                else if (this.expressions[this.id].type == "Comparison")
                    this.selected = "Comparison";
                else if (this.expressions[this.id].type == "Name Generator")
                    this.selected = "Name Generator";
                else
                    this.selected = "Simple";
            }
            expressions[0].countExpressions = countExpressions;
            for (k in this.expressions) {
                if (this.expressions[k].value) {
                    if (expressions[k].type != "Counting")
                        this.normalExpressions.push(expressions[k])
                    this.expressions[k].countExpressions = [];
                    for (x in countExpressions) {
                        if (countExpressions[x].id != expressions[k].id)
                            this.expressions[k].countExpressions.push(countExpressions[x]);
                    }
                    if (this.expressions[k].value.match(","))
                        this.expressions[k].selectedOptions = this.expressions[k].value.split(",")
                    else
                        this.expressions[k].selectedOptions = [this.expressions[k].value]
                    if (this.expressions[k].value.match(":")) {
                        parts = this.expressions[k].value.split(":")
                        if (this.expressions[k].type == "Counting") {
                            this.expressions[k].multiplier = parts[0];
                            if (parts[1].match(","))
                                this.expressions[k].selectedExpressions = parts[1].split(",")
                            else {
                                if (parts[1])
                                    this.expressions[k].selectedExpressions = [parts[1]];
                                else
                                    this.expressions[k].selectedExpressions = [];
                            }
                            if (typeof parts[2] != "undefined") {
                                if (parts[2].match(","))
                                    this.expressions[k].selectedQuestions = parts[2].split(",")
                                else {
                                    if (parts[2])
                                        this.expressions[k].selectedQuestions = [parts[2]];
                                    else
                                        this.expressions[k].selectedQuestions = [];
                                }
                            }
                        } else {
                            this.expressions[k].compare = parts[0];
                            this.expressions[k].selectedExpressions = parts[1];
                        }
                    }
                    if (this.expressions[k].type == "Name Generator") {
                        if (this.expressions[k].value.match(","))

                            this.expressions[k].selectedQuestions = this.expressions[k].value.split(",")
                        else
                            this.expressions[k].selectedQuestions = [this.expressions[k].value];
                    }
                }
            }

        },
        watch: {
            $route(to, from) {
                if (this.id && this.id != 0) {
                    if (this.expressions[this.id].type == "Compound")
                        this.selected = "Compound";
                    else if (this.expressions[this.id].type == "Counting")
                        this.selected = "Counting";
                    else if (this.expressions[this.id].type == "Comparison")
                        this.selected = "Comparison";
                    else if (this.expressions[this.id].type == "Name Generator")
                        this.selected = "Name Generator";
                    else
                        this.selected = "Simple";
                } else {
                    this.selected = "Simple";
                }
            }
        },
        methods: {
            deleteExpression(id) {
                $("#deleteExpressionId").val(id);
                $("#deleteExpression").submit();
            },
            changeEQ(val) {
                console.log("change eq " + val)
                if (typeof val == "undefined")
                    return;
                if (questions[val].answerType == "MULTIPLE_SELECTION")
                    this.expressions[this.id].type = "Selection"
                if (questions[val].answerType == "NUMERICAL" || questions[val].answerType == "RANDOM_NUMBER")
                    this.expressions[this.id].type = "Number"
                if (questions[val].answerType == "TEXTUAL" || questions[val].answerType == "TEXTUAL_PP" || questions[val].answerType == "STORED_VALUE")
                    this.expressions[this.id].type = "Text"
                console.log(this.expressions[this.id].type)
                this.$forceUpdate();
                console.log(this.expressions[this.id].type)
            },
            changeType() {
                if (this.selected != "Simple")
                    this.expressions[this.id].type = this.selected;
                else
                    this.expressions[this.id].type = null;
                this.$forceUpdate();
            },
            checkVal(val, expId) {
                this.$forceUpdate();
                this.expressions[expId].value = val.join(",")
            },
            buildVal(val, expId) {
                var exps = "";
                var qs = "";
                if (this.expressions[expId].selectedExpressions) {
                    if (this.expressions[expId].selectedExpressions.length > 1)
                        exps = this.expressions[expId].selectedExpressions.join(",");
                    else
                        exps = this.expressions[expId].selectedExpressions;
                }
                if (this.expressions[expId].selectedQuestions) {

                    if (this.expressions[expId].selectedQuestions.length > 1)
                        qs = this.expressions[expId].selectedQuestions.join(",");
                    else
                        qs = this.expressions[expId].selectedQuestions;
                }
                var newVal = this.expressions[expId].multiplier + ":" + exps + ":" + qs;
                this.$forceUpdate();
                this.expressions[expId].value = newVal;
            },
            compVal(val, expId) {
                var exps = "";
                if (this.expressions[expId].selectedExpressions) {
                    if (this.expressions[expId].selectedExpressions.length > 1)
                        exps = this.expressions[expId].selectedExpressions.join(",");
                    else
                        exps = this.expressions[expId].selectedExpressions;
                }
                var newVal = this.expressions[expId].compare + ":" + exps;
                this.$forceUpdate();
                this.expressions[expId].value = newVal;
            },
        }

    })

    const router = new VueRouter({
        routes: [{
                path: '/',
                component: ExpressionEditor
            }, // No props, no nothing
            {
                path: '/:id',
                component: ExpressionEditor,
                props: true
            }, // Pass route.params to props
        ]
    })

    new Vue({
        router,
        el: '#authoring-app',
        data() {
            return {
                expressionList: expressionList,
                expressions: expressions,
                study: study,
                filterType: '',
                filterName: '',
                filterExpType: 'Simple',
                filterQ: '',
            }
        },
        methods: {
            changeFilterType() {
                if(this.filterType == ''){
                    newExpressionList = JSON.parse(JSON.stringify(expressionList));
                    this.expressionList = newExpressionList;
                    this.$forceUpdate();
                }
            },
            filterExpByName() {
                newExpressionList = [];
                newExpressionList[0] = JSON.parse(JSON.stringify(expressionList[0]));
                for (k = 1; k < expressionList.length; k++) {
                    if (expressionList[k].name.toLowerCase().indexOf(this.filterName.toLowerCase()) != -1)
                        newExpressionList.push(JSON.parse(JSON.stringify(expressionList[k])));
                }
                this.expressionList = newExpressionList;
                this.$forceUpdate();
            },
            expFilterKey(event) {
                if(event.key == "Enter")
                    this.filterExpByName();
            },
            filterExpByQ() {
                newExpressionList = [];
                qIds = [];
                newExpressionList[0] = JSON.parse(JSON.stringify(expressionList[0]));
                for (k in questions) {
                    if (questions[k].title.toLowerCase().indexOf(this.filterQ.toLowerCase()) != -1)
                        qIds.push(parseInt(k));
                }
                console.log(qIds)
                for (k = 1; k < expressionList.length; k++) {
                    if (qIds.indexOf(expressionList[k].questionId) != -1)
                        newExpressionList.push(JSON.parse(JSON.stringify(expressionList[k])));
                }
                this.expressionList = newExpressionList;
                this.$forceUpdate();
            },
            qFilterKey(event) {
                if(event.key == "Enter")
                    this.filterExpByQ();
            },
            filterExpByType() {
                newExpressionList = [];
                newExpressionList[0] = JSON.parse(JSON.stringify(expressionList[0]));
                for (k = 1; k < expressionList.length; k++) {
                    if (expressionList[k].type == this.filterExpType)
                        newExpressionList.push(JSON.parse(JSON.stringify(expressionList[k])));
                    else if (this.filterExpType == "Simple" && ['Text', 'Number', 'Selection'].indexOf(expressionList[k].type) != -1)
                        newExpressionList.push(JSON.parse(JSON.stringify(expressionList[k])));
                }
                this.expressionList = newExpressionList;
                this.$forceUpdate();
            },
        }
    });
</script>