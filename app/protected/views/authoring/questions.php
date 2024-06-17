<?php
use yii\helpers\Html;

?>
<?= $this->render('/layouts/nav', ['study'=> $study]); ?>
<div id="authoring-app" class="mt-md-5">
    <?php if (Yii::$app->controller->action->id == "ego_id"): ?>
    <div class="col-md-12 mb-3">
        <label for="Study_egoIdPrompt" class="col-sm-12 col-form-label">Ego ID Prompt<b-button id="saveEgoId" class="float-right btn btn-success btn-sm col-1" @click="saveStudy">save</b-button></label>
        <summer-note :model.sync="study.egoIdPrompt" ref="Study_egoIdPrompt" name="Study[egoIdPrompt]" vid="Study_egoIdPrompt"></summer-note>
    </div>
    <div v-sortable.div="{ onUpdate: reorderQuestion, chosenClass: 'is-selected'}" style="height:45vh;overflow-y:auto">
        <question-editor v-for="(question, k) in questions" v-bind:question="question" :key="question.id" />
    </div>
    <?php else: ?>
    <div v-sortable.div="{ onUpdate: reorderQuestion, chosenClass: 'is-selected'}" style="height:75vh;overflow-y:auto">
        <question-editor v-for="(question, k) in questions" v-bind:question="question" :key="question.id" />
    </div>
    <?php endif; ?>
    <question-editor v-bind:question="new_question" :key="new_question.id" />
</div>
<script>
csrf = '<?php echo Yii::$app->request->getCsrfToken(); ?>';
answerTypes = <?php echo json_encode($answerTypes, ENT_QUOTES); ?>;
subjectTypes = <?php echo json_encode($subjectTypes, ENT_QUOTES); ?>;
new_question = <?php echo json_encode($new_question, ENT_QUOTES); ?>;
questions = <?php echo json_encode($questions, ENT_QUOTES); ?>;
all_questions = <?php echo json_encode($all_questions, ENT_QUOTES); ?>;
console.log(all_questions);
expressions = <?php echo json_encode($expressions, ENT_QUOTES); ?>;
studyNames = <?php echo json_encode($studyNames, ENT_QUOTES); ?>;
study = <?php echo json_encode($study->toArray(), ENT_QUOTES); ?>;
</script>
<script type="text/x-template" id="questionEditor">
    <b-card no-body :class="'mb-1'">
    <b-card-header header-tag="header" class="p-1" role="tab">
        <b-button block v-b-toggle="'accordion-' + question.id" variant="secondary">{{question.id ? question.title: "Create New Question" }}</b-button>
    </b-card-header>
    <b-collapse v-bind:id="'accordion-' + question.id" accordion="my-accordion" role="tabpanel" @show="closed = false" @hidden="closed = false">
        <form :id="'form-' + question.id" method="post" v-if="closed == false">
            <span class="badge badge-secondary float-right ml-1">qid:{{question.id}}</span>
            <input type="hidden" name="_csrf-protected" :value="csrf">
            <input type="hidden" name="Question[id]" v-model="question.id">
            <input type="hidden" name="Question[studyId]" v-model="question.studyId">
            <div class="row card-body">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label for="Question_title" class="col-sm-2 col-form-label">Title</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="question.title" class="form-control" name="Question[title]" :id="question.id + '_title'">
                        </div>
                    </div>
                    <div class="form-group row" v-if="subjectTypes">
                        <label for="Question_subjectType" class="col-sm-4 col-form-label">Subject Type</label>
                        <div class="col-sm-8">
                            <b-form-select v-model="question.subjectType" :options="subjectTypes" name="Question[subjectType]" :id="question.id + '_subjectType'" @change="changeSubjectType($event)"></b-form-select>
                        </div>
                    </div>

                    <input v-if="!subjectTypes" :id="(question.id ? question.id: 0 )+ '_subjectType'" type="hidden" v-model="question.subjectType" name="Question[subjectType]">

                    <div class="form-group row">
                        <label for="Question_answerType" class="col-sm-4 col-form-label">Answer Type</label>
                        <div class="col-sm-8">
                            <b-form-select :disabled="question.subjectType == 'NAME_GENERATOR' || question.subjectType == 'MERGE_ALTER'" v-model="question.answerType" :options="answerTypes" name="Question[answerType]" :id="question.id + '_answerType'"
                            @change="changeAnswerType($event)"></b-form-select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Question_answerReasonExpressionId" class="col-sm-4 col-form-label">Skip Expression</label>
                        <div class="col-sm-8">
                            <b-form-select
                            value-field="id"
                            text-field="name"
                            v-model="question.answerReasonExpressionId"
                            :options="expressions"
                            name="Question[answerReasonExpressionId]"
                            :id="question.id + '_answerReasonExpressionId'">
                                <template #first>
                                    <b-form-select-option value=""></b-form-select-option>
                                </template>
                            </b-form-select>
                        </div>
                    </div>
                    <div class="offset-md-4 col-md-8 form-group row">
                        <b-form-checkbox class="col mb-1" :id="question.id + '_dontKnowButton'" v-model="question.dontKnowButton" name="Question[dontKnowButton]" value="1" unchecked-value="0">
                            Don't Know
                        </b-form-checkbox>
                        <input type="hidden" v-if="question.dontKnowButton == 0" name="Question[dontKnowButton]" value="0">
                        <div class="col-7">
                            <input type="text" v-model="question.dontKnowText" v-if="question.dontKnowButton && question.dontKnowButton == true" class="form-control input-xs" name="Question[dontKnowText]" :id="question.id + '_dontKnowText'" maxlength="32">
                        </div>
                    </div>
                    <div class="offset-md-4 col-md-8 form-group row">
                        <b-form-checkbox class="col mb-1" :id="question.id + '_refuseButton'" v-model="question.refuseButton" name="Question[refuseButton]" value="1" unchecked-value="0">
                            Refuse
                        </b-form-checkbox>
                        <input type="hidden" v-if="question.refuseButton == 0" name="Question[refuseButton]" value="0">
                        <div class="col-7">
                            <input type="text" v-model="question.refuseText" v-if="question.refuseButton && question.refuseButton == true" class="form-control input-xs" name="Question[refuseText]" :id="question.id + '_refuseText'" maxlength="32">
                        </div>
                    </div>
                    <div class="offset-md-4 col-md-8 form-group row" v-if="question.subjectType == 'ALTER' || question.subjectType == 'ALTER_PAIR'" >
                        <b-form-checkbox class="col mb-1" :id="question.id + '_allButton'" v-model="question.allButton" name="Question[allButton]" value="1" unchecked-value="0">
                            Set All
                        </b-form-checkbox>
                        <input type="hidden" v-if="question.allButton == 0" name="Question[allButton]" value="0">
                        <div class="col-7">
                            <input type="text" v-model="question.setAllText" v-if="question.allButton && question.allButton == true" class="form-control input-xs" name="Question[setAllText]" :id="question.id + '_setAllText'"  maxlength="32">
                        </div>
                    </div>
                    <div class="offset-md-4 col-md-8 form-group row" v-if="question.subjectType != 'NAME_GENERATOR' && question.subjectType != 'EGO_ID' && question.subjectType != 'NETWORK' && question.subjectType != 'NO_RESPONSE'">
                        <b-form-checkbox v-if="question.subjectType != 'ALTER' && question.subjectType != 'ALTER_PAIR'" class="col-8 mb-1" :id="question.id + '_askingStyleList'" v-model="question.askingStyleList" name="Question[askingStyleList]" value="1" unchecked-value="0">
                            Stem and Leaf
                        </b-form-checkbox>
                        <b-form-checkbox v-if="question.subjectType == 'ALTER' || question.subjectType == 'ALTER_PAIR'" class="col-8 mb-1" :id="question.id + '_askingStyleList'" v-model="question.askingStyleList" name="Question[askingStyleList]" value="1" unchecked-value="0">
                            List Style
                        </b-form-checkbox>
                        <input type="hidden" v-if="question.askingStyleList == 0" name="Question[askingStyleList]" value="0">
                    </div>
                    <div class="form-group row">
                        <label for="Question_prompt" class="col-sm-4 col-form-label">Prompt</label>
                        <summer-note :model.sync="question.prompt" ref="Question_prompt" name="Question[prompt]" :vid="question.id + '_prompt'"></summer-note>
                    </div>

                    
                </div>
                <div class="col-md-6">

                    <div class="form-group row" v-if="question.subjectType == 'EGO_ID' || question.subjectType == 'NAME_GENERATOR'">
                        <label for="question.id + '_useAlterListField'" class="col-sm-4 col-form-label">Use Participant List Field</label>
                        <div class="col-sm-8">
                            <b-form-select v-model="question.useAlterListField"  name="Question[useAlterListField]" :id="question.id + '_useAlterListField'">
                                <b-select-option value="" selected="selected">None</b-select-option>
                                <b-select-option value="email">Email</b-select-option>
                                <b-select-option value="name">Name</b-select-option>
                            </b-form-select>
                        </div>
                    </div>

                    <div v-if="question.subjectType == 'MERGE_ALTER'">
                        <input type="hidden" v-model="question.allOptionString" :id="question.id + '_allOptionString'" name="Question[allOptionString]">
                        <div class="form-group row">
                            <label :for="question.id + '_minLiteral'" class="col-sm-4 col-form-label">First Name Syllable Difference</label>
                            <div class="col-sm-2">
                                <input :id="question.id + '_minLiteral'" class="form-control" name="Question[minLiteral]" v-model="question.minLiteral">
                            </div>
                            <label :for="question.id + '_maxLiteral'" class="col-sm-4 col-form-label">Last Name Syllable Difference</label>
                            <div class="col-sm-2">
                                <input :id="question.id + '_maxLiteral'" class="form-control" name="Question[maxLiteral]" v-model="question.maxLiteral">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Yes Label</label>
                            <div class="col-sm-2">
                                <input class="form-control" v-model="question.allOptionJson.YES_LABEL" @change="changeLabel">
                            </div>
                            <label class="col-sm-4 col-form-label">No Label</label>
                            <div class="col-sm-2">
                                <input class="form-control" v-model="question.allOptionJson.NO_LABEL" @change="changeLabel">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">New Name Label</label>
                            <div class="col-sm-2">
                                <input class="form-control" v-model="question.allOptionJson.NEW_NAME_LABEL" @change="changeLabel">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" v-if="question.askingStyleList == true && (question.subjectType == 'EGO' || question.subjectType == 'MERGE_ALTER')">
                        <label for="Question_citation" class="col-sm-4 col-form-label">Stem and Leaf</label>
                        <summer-note :model.sync="question.citation" ref="Question_citation" name="Question[citation]" :vid="question.id + '_citation'"></summer-note>
                    </div>

                    <div v-if="question.answerType == 'NUMERICAL'">
                        <div class="form-group row">
                            <div class="col-4">Min:</div>
                            <div class="col-8">
                                <div class="row">
                                    <div class="col-4">
                                        <label :for="question.id + '_minLimitType_0'" title="NLT_LITERAL">
                                        <input v-model="question.minLimitType" class="form-check-input" :id="question.id + '_minLimitType_0'" value="NLT_LITERAL" type="radio" name="Question[minLimitType]">
                                            Literal
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <input v-model="question.minLiteral" class="col-4" :id="question.id + '_minLiteral'" name="Question[minLiteral]" type="text" maxlength="4096" value="1">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <label :for="question.id + '_minLimitType_1'" title="NLT_PREVQUES">
                                        <input v-model="question.minLimitType" class="form-check-input" :id="question.id + '_minLimitType_1'" value="NLT_PREVQUES" type="radio" name="Question[minLimitType]">
                                            Previous
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <b-form-select
                                        :disabled="question.minLimitType != 'NLT_PREVQUES'"
                                        v-model="question.minPrevQues"
                                        :options="question.numQuestions"
                                        name="Question[minPrevQues]"
                                        :id="question.id + '_minPrevQues'">
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                        </b-form-select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <label :for="question.id + '_minLimitType_2'" title="NLT_NONE">
                                        <input v-model="question.minLimitType" class="form-check-input" :id="question.id + '_minLimitType_2'" value="NLT_NONE" type="radio" name="Question[minLimitType]">
                                            None 
                                        </label>
                                    </div>
                                    <div class="col-8"></div>                    
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-4">Max:</div>
                            <div class="col-8">
                                <div class="row">
                                    <div class="col-4">
                                        <label :for="question.id + '_maxLimitType_0'" title="NLT_LITERAL">
                                        <input  v-model="question.maxLimitType" class="form-check-input" :id="question.id + '_maxLimitType_0'" value="NLT_LITERAL" type="radio" name="Question[maxLimitType]">
                                            Literal
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <input v-model="question.maxLiteral" class="col-4" :id="question.id + '_maxLiteral'" name="Question[maxLiteral]" type="text">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <label :for="question.id + '_maxLimitType_1'" title="NLT_PREVQUES">
                                        <input v-model="question.maxLimitType" class="form-check-input" :id="question.id + '_maxLimitType_1'" value="NLT_PREVQUES" type="radio" name="Question[maxLimitType]">
                                            Previous
                                        </label>
                                    </div>
                                    <div class="col-8">
                                    <b-form-select
                                        :disabled="question.maxLimitType != 'NLT_PREVQUES'"
                                        v-model="question.maxPrevQues"
                                        :options="question.numQuestions"
                                        name="Question[maxPrevQues]"
                                        :id="question.id + '_maxPrevQues'">
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                        </b-form-select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <label :for="question.id + '_maxLimitType_2'" title="NLT_NONE">
                                        <input v-model="question.maxLimitType" class="form-check-input" :id="question.id + '_maxLimitType_2'" value="NLT_NONE" type="radio" name="Question[maxLimitType]">
                                            None 
                                        </label>
                                    </div>
                                    <div class="col-8"></div>                    
                                </div>
                            </div>
                        </div>
                    </div>
              

                    <div v-if="question.answerType == 'MULTIPLE_SELECTION'">
                        <div class="form-group row">
                            <label :for="question.id + '_minCheckableBoxes'" class="col-sm-4 col-form-label">Min Checkboxes</label>
                            <div class="col-sm-2">
                                <input :id="question.id + '_minCheckableBoxes'" class="form-control" name="Question[minCheckableBoxes]" v-model="question.minCheckableBoxes">
                            </div>
                            <label :for="question.id + '_maxCheckableBoxes'" class="col-sm-4 col-form-label">Max Checkboxes</label>
                            <div class="col-sm-2">
                                <input :id="question.id + '_maxCheckableBoxes'" class="form-control" name="Question[maxCheckableBoxes]" v-model="question.maxCheckableBoxes">
                            </div>
                        </div>

                             
                        <div v-if="question.subjectType == 'ALTER'">
                            <div class="form-group row ml-1">
                                <b-form-checkbox class="col-sm-4" :id="question.id + '_withListRange'" v-model="question.withListRange" name="Question[withListRange]" value="1" unchecked-value="0">
                                    Use List Limit
                                </b-form-checkbox>
                                <label v-if="question.withListRange == 1" class="col-sm-4 col-form-label">Count Response</label>
                                <input type="hidden" v-if="question.withListRange == 0" name="Question[withListRange]" value="0">
                                <div v-if="question.withListRange == 1" class="col-sm-4">
                                    <b-form-select v-model="question.listRangeString"
                                        value-field="id"
                                        id="Question_listRangeString"
                                        name="Question[listRangeString]"
                                        text-field="name"
                                        :options="question.options"
                                        >
                                        <template #first>
                                            <b-form-select-option value="" disabled>-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
                            <div v-if="question.withListRange == 1" class="form-group row">
                                <label class="offset-sm-4 col-sm-2 col-form-label" :for="question.id + '_minListRange'">Min</label>
                                <input v-model="question.minListRange" class="col-sm-1" :id="question.id + '_minListRange'" name="Question[minListRange]" type="text" maxlength="4096">
                                <label class="offset-sm-1 col-sm-2" :for="question.id + '_maxListRange'">Max</label>
                                <input v-model="question.maxListRange" class="col-sm-1" :id="question.id + '_maxListRange'" name="Question[maxListRange]" type="text" maxlength="4096">    
                            </div>
                        </div>

                        <b-table class="options" head-variant="dark" :id="question.id+'-options'" :tbody-tr-attr="setAttribute" v-sortable.tr="{ onUpdate: reorderOption, chosenClass: 'is-selected'}" :items="question.options" :fields="option_fields" striped responsive="sm">
                            <template #cell(name)="row">
                                <input class="form-control input-xs" @blur="editOption(row.item.id, this)" v-on:keyup.13="editOption(row.item.id, this)" v-model="row.item.name" />
                            </template>
                            <template #cell(value)="row">
                                    <input class="form-control input-xs" @change="editOption(row.item.id, this)" v-model="row.item.value" />
                            </template>
                            <template #cell(specify)="row">
                                <b-form-checkbox v-model="row.item.otherSpecify" :id="row.item.questionId + '-' + row.item.name + '-other'" @change="editOption(row.item.id, this)" value="1" unchecked-value="0">
                                    &nbsp;
                                </b-form-checkbox>
                            </template>
                            <template #cell(single)="row">
                                <b-form-checkbox v-model="row.item.single" :id="row.item.questionId + '-' + row.item.name + '-single'" @change="editOption(row.item.id, this)" value="1" unchecked-value="0">
                                &nbsp;
                                </b-form-checkbox>
                            </template>
                            <template #cell(details)="row">
                                <b-link href="#" @click="deleteOption(row.item.id)"><i class="fas fa-times"></i></b-link>
                            </template>
                            <template v-slot:custom-foot>
                                <tr class="text-white" >
                                    <td>
                                        <input class="form-control input-xs" v-model="newOptionName" :name="question.id + '_QuestionOption_name'" />
                                    </td>
                                    <td>
                                        <input class="form-control input-xs" v-model="newOptionValue" :name="question.id + '_QuestionOption_value'" />
                                    </td>
                                    <td>
                                        <b-form-checkbox v-model="newOptionOtherSpecify" :id="question.id + '_QuestionOption_otherSpecify'" :name="question.id + '_QuestionOption_otherSpecify'" value="1" unchecked-value="0">
                                        &nbsp;
    
                                    </b-form-checkbox>
                                    </td>
                                    <td>
                                        <b-form-checkbox v-model="newOptionSingle" :name="question.id + '_QuestionOption_single'" value="1" unchecked-value="0">
                                        &nbsp;
   
                                    </b-form-checkbox>                                   
                                    </td>
                                    <td>
                                        <b-button @click="newOption" variant="primary" size="xs">Add</b-button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                    Replace list with options from
                                    </td>
                                    <td colspan=3>
                                    <b-form-select
                                        v-model="otherQuestionId"
                                        name="multi[questionId]"
                                        :options="question.multiQuestions"
                                        >
                                        <template #first>
                                            <b-form-select-option value="null">-- Select a question --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                    </td>
                                    <td>
                                    <b-button @click="replaceOption" size="xs">Go</b-button>
                                    </td>
                                </tr>
                            </template>            
                        </b-table>
         

                    </div>
                    <div v-if="question.subjectType == 'NAME_GENERATOR'">

                        <div class="form-group row">
                            <label :for="question.id + '_minLiteral'" class="col-sm-4 col-form-label">Minimum Alters</label>
                            <div class="col-sm-2">
                                <input :id="question.id + '_minLiteral'" class="form-control" name="Question[minLiteral]" v-model="question.minLiteral">
                            </div>
                            <label :for="question.id + '_maxLiteral'" class="col-sm-4 col-form-label">Maximum Alters</label>
                            <div class="col-sm-2">
                                <input :id="question.id + '_maxLiteral'" class="form-control" name="Question[maxLiteral]" v-model="question.maxLiteral">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_restrictList'" name="Question[restrictList]" unchecked-value="0" value="1" type="checkbox" v-model="question.restrictList">
                                    Restrict Response to Participant List
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.restrictList == 0" name="Question[restrictList]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_autocompleteList'" name="Question[autocompleteList]" unchecked-value="0" value="1" type="checkbox" v-model="question.autocompleteList">
                                    Fill Autocomplete with Participant List 
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.autocompleteList == 0" name="Question[autocompleteList]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_prefillList'" name="Question[prefillList]" unchecked-value="0" value="1" type="checkbox" v-model="question.prefillList">
                                Pre-fill Alters from Participant List
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.prefillList == 0" name="Question[prefillList]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_restrictPrev'" name="Question[restrictPrev]" unchecked-value="0" value="1" type="checkbox" v-model="question.restrictPrev">
                                    Restrict Response to Previous Alters
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.restrictPrev == 0" name="Question[restrictPrev]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_autocompletePrev'" name="Question[autocompletePrev]" unchecked-value="0" value="1" type="checkbox" v-model="question.autocompletePrev">
                                    Fill Autocomplete with Previous Alters 
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.autocompletePrev == 0" name="Question[autocompletePrev]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_prefillPrev'" name="Question[prefillPrev]" unchecked-value="0" value="1" type="checkbox" v-model="question.prefillPrev">
                                    Pre-fill Alters from Previous Alters
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.prefillPrev == 0" name="Question[prefillPrev]" value="0">         
                            </div>
                            <div class="col-sm-12" v-if="question.prefillPrev != false || question.prefillList != false">
                                <b-form-checkbox :id="question.id + '_hideNameGenQ'" name="Question[hideNameGenQ]" unchecked-value="0" value="1" type="checkbox" v-model="question.hideNameGenQ">
                                Hide Name Generator Question
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.hideNameGenQ == 0" name="Question[hideNameGenQ]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_keepOnSamePage'" name="Question[keepOnSamePage]" unchecked-value="0" value="1" type="checkbox" v-model="question.keepOnSamePage">
                                Show Previous Session Alters
                                </b-form-checkbox>
                                <input type="hidden" v-if="question.keepOnSamePage == 0" name="Question[keepOnSamePage]" value="0">         
                            </div>
                            <div class="col-sm-12">
                                <b-form-checkbox :id="question.id + '_noneButton'" name="Question[noneButton]" unchecked-value="0" value="1" type="checkbox" v-model="question.noneButton">
                                Allow alters already listed in other name generators
                                </b-form-checkbox>       
                                <input type="hidden" v-if="question.noneButton == 0" name="Question[noneButton]" value="0">         
                            </div>
                        </div>
                        <b-table head-variant="dark" class="prompts" :id="question.id+'-alterPrompts'"  :items="question.alterPrompts" :fields="prompt_fields" striped responsive="sm">
                            <template #cell(afterAltersEntered)="row">
                                <input class="form-control input-xs" @change="editPrompt(row.item.id)" v-model="row.item.afterAltersEntered" />
                            </template>
                            <template #cell(display)="row">
                                <input class="form-control input-xs" @change="editPrompt(row.item.id)" v-model="row.item.display" />
                            </template>
                            <template #cell(details)="row">
                                <b-button size="xs" @click="deletePrompt(row.item.id)" class="mr-2 btn-danger">
                                delete
                                </b-button>
                            </template>
                            <template v-slot:custom-foot>
                                <tr class="text-white" >
                                    <td class="col-md-2">
                                        <input class="form-control input-xs" v-model="newPromptAfter" name="AlterPrompt[afterAltersEntered]" />
                                    </td>
                                    <td class="col-md-2">
                                        <input class="form-control input-xs" v-model="newPromptDisplay" name="AlterPrompt[display]" />
                                    </td>
                                    <td>
                                        <b-button @click="newPrompt" variant="primary" size="xs">Add</b-button>
                                    </td>
                                </tr>
                                <tr class="text-black" >
                                    <td></td>
                                    <td>
                                        <input  :id="'prompt_' + question.id" type="file" />
                                    </td>
                                    <td>
                                        <b-button @click="uploadPrompt" variant="primary" size="xs">Upload</b-button>
                                    </td>
                                </tr>
                            </template>   
                        </b-table>
                    </div>

                    <div v-if="question.answerType == 'DATE' || question.answerType == 'TIME_SPAN'">
                        <input type="hidden" v-model="question.timeUnits" name="Question[timeUnits]" />
                        <div class="row">
                            <b-form-checkbox class="col" :id="question.id + '-time-YEAR'" value="1" unchecked-value="0" @change="timeBits"  v-model="question.timeBits.BIT_YEAR">
                                Years
                            </b-form-checkbox>
                            <b-form-checkbox class="col" :id="question.id + '-time-MONTH'" value="2" unchecked-value="0" @change="timeBits"  v-model="question.timeBits.BIT_MONTH">
                                Months
                            </b-form-checkbox>
                            <b-form-checkbox v-if="question.answerType == 'TIME_SPAN'" class="col" :id="question.id + '-time-WEEK'" value="4" unchecked-value="0" @change="timeBits"  v-model="question.timeBits.BIT_WEEK">
                                Weeks
                            </b-form-checkbox>
                        </div>
                        <div class="row">
                            <b-form-checkbox class="col" :id="question.id + '-time-DAY'" value="8" unchecked-value="0" @change="timeBits"  v-model="question.timeBits.BIT_DAY">
                                Days
                            </b-form-checkbox>
                            <b-form-checkbox class="col" :id="question.id + '-time-HOUR'" value="16" unchecked-value="0" @change="timeBits"  v-model="question.timeBits.BIT_HOUR">
                                Hours
                            </b-form-checkbox>
                            <b-form-checkbox class="col" :id="question.id + '-time-MINUTE'" value="32" unchecked-value="0" @change="timeBits" v-model="question.timeBits.BIT_MINUTE">
                                Minutes
                            </b-form-checkbox>
                        </div>
                    </div>

                    <?= $this->render('/authoring/network'); ?>

                    <div v-if="question.subjectType == 'MULTI_GRAPH'">
                        <input type="hidden" v-model="question.networkGraphs" name="Question[networkGraphs]">
                        <div v-for="(graph, g) in question.nGraphs">
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Graph #{{g+1}} Title</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control input-xs" v-model="graph.title" @change="resetGraphs()">
                                </div>
                                <div class="col-sm-2">
                                    <b-button class="btn btn-danger" @click="deleteGraph(g)">Delete</b-button>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Network</label>
                                <div class="col-sm-8">
                                    <b-form-select
                                    v-model="graph.questionId"
                                    :options="question.nQuestions"
                                    @change="resetGraphs()">
                                    <template #first>
                                            <b-form-select-option value="">-- None --</b-form-select-option>
                                        </template>
                                </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Legend</label>
                                <div class="col-sm-7">
                                    <input :id="g + '_' + question.id" type="file" @change="storeImage">
                                </div>
                                <div class="col-sm-1">
                                    <b-link href="#" @click="deleteLabel(g)"><i class="fas fa-times"></i></b-link>
                                </div>
                                <div class="col-sm-12" v-if="graph.questionLabel != ''">
                                    <img :src="graph.questionLabel" :style="'float:right; height:' +  graph.size + 'vh'">
                                </div>
                            </div>
                            <div class="form-group row" v-if="graph.questionLabel != ''">
                                <label class="col-sm-4 col-form-label">Legend Size ({{graph.size}})</label>
                                <div class="col-sm-8">
                                    <input class="form-control" type='range' min=10 max=30 @change="resetGraphs()" v-model="graph.size">
                                </div>
                            </div>
                        </div>
                        <b-button v-if="question.nGraphs.length < 6" @click="addGraph" variant="primary" size="xs">Add Graph</b-button>
                    </div>
                    <div v-if="question.preface != null && question.preface != ''">
                        <label for="Question_preface" class="col-form-label">Preface (Deprecated.  Please copy into a new NO_RESPONSE question)</label>
                        <textarea name="Question[preface]">{{question.preface}}</textarea>
                    </div>

                    <div v-if="question.id" class="btn-group col row mt-3">
                        <b-button class="btn btn-success" @click="saveQuestion">Save</b-button>
                        <b-button class="btn btn-warning" @click="duplicateQuestion">Duplicate</b-button>
                        <b-button class="btn btn-danger" @click="deleteQuestion">Delete</b-button>
                    </div>
                    <div v-if="!question.id" class="btn-group col-4 row mt-3">
                        <button class="btn btn-primary">Create</button>
                    </div>
                </div>
                
            </div>

        </form>
    </b-collapse>
</b-card>
</script>

<script>
Vue.directive('sortable', {
    twoWay: true,
    deep: true,
    bind: function(el, binding, vnode) {
        var options = {
            ...binding.value,
            draggable: Object.keys(binding.modifiers)[0]
        };
        if (Object.keys(binding.modifiers)[0] == "tr")
            el._sortable = Sortable.create(el.querySelector("tbody"), options);
        else
            el._sortable = Sortable.create(el, options);
        el._sortable.option("onChoose", function(e) {
            el._sortable.oldOrder = el._sortable.toArray();
        });
        el._sortable.option("onUpdate", function(e) {
            if (typeof options.onUpdate != "undefined")
                options.onUpdate(e);
            if(vnode.children == undefined){
                el._sortable.sort(el._sortable.oldOrder)
            }
        });

    },
    update: function(value) {}
});

SummerNote = Vue.component('summer-note', {
    template: '<textarea ref="summernote" :id="vid" :name="name" :v-model="vmodel"></textarea>',
    props: ['name', 'model', 'vid', 'vmodel'],
    computed: {
        summernote() {
            return $(this.$refs.summernote);
        }
    },
    data() {
        return {
            isCodeview: false
        }
    },
    mounted() {
        var self = this;
        $(this.$refs.summernote).summernote({
            disableDragAndDrop: true,
            height: 100,
            toolbar: noteBar,
            callbacks: {
                onInit: function() {
                    $(this).summernote("code", parseEgowebTags(self.model, self.vid));
                },
                onKeyup: function(e) {
                    var text = rebuildEgowebTags($(this).summernote('code').replace(/ draggable=\"false\"/g, ''),self.vid);
                    if ($(this).summernote('isEmpty')) {
                        text = text.replace('<p><br></p>','')
                    }
                    $("#" + self.vid).val(text);
                    self.$emit("update:model", text);
                    parseEgowebTags(text, self.vid);
                },
                onChangeCodeview: function(e) {
                    var text = $(this).summernote('code');
                    console.log("change codeview:",self.vid, $(this).summernote('code'), text);
                    $("#" + self.vid).val(text);
                    self.$emit("update:model", text);
                },
                onPaste: function(e) {
                    var thisNote = $(this);
                    var updatePastedText = function(someNote) {
                        var original = someNote.code();
                        var cleaned = CleanPastedHTML(original);
                        someNote.code('').html(cleaned);
                    };
                    setTimeout(function() {
                        updatePastedText(thisNote);
                    }, 10);
                }
            }
        });
        $(this.$refs.summernote).on('summernote.codeview.toggled', function(e) {
            self.isCodeview = !self.isCodeview;
            if(self.isCodeview){
                $(this).summernote("code", rebuildEgowebTags(self.model,self.vid));
            }else{
                $(this).summernote("code", parseEgowebTags(self.model,self.vid));
            }
        });
    },
    methods: {
        getVal() {
            var data = $(this.$refs.summernote).summernote('code');
            return data;
        },
        run(code, value) {
            if (value == undefined) {
                $(this.$refs.summernote).summernote(code)
            } else {
                $(this.$refs.summernote).summernote(code, value)
            }
        }
    }
});

QestionEditor = Vue.component('question-editor', {
    template: '#questionEditor',
    props: ['question'],
    data() {
        return {
            file: null,
            closed: true,
            option_fields: [{
                key: "name",
                label: "Option Name",
                tdClass: 'col-md-6'
            }, 'value', 'specify', 'single', {
                key: "details",
                label: ""
            }],
            prompt_fields: [{
                    key: "afterAltersEntered",
                    label: "After #",
                    tdClass: 'col-md-2'
                },
                {
                    key: "display",
                    label: "alters entered, display",
                    tdClass: 'col-md-6'
                }, {
                    key: "details",
                    label: ""
                }
            ],
            answerTypes: answerTypes,
            subjectTypes: subjectTypes,
            expressions: expressions,
            newOptionName: "",
            newOptionValue: 0,
            newOptionOtherSpecify: 0,
            newOptionSingle: 0,
            newPromptDisplay: "",
            newPromptAfter: 0,
            otherQuestionId:"",
            csrf: csrf,
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
    computed: {
    },
    mounted() {
    },
    methods: {
        setAttribute(item, type) {
            return {
                'data-id': item.id,
            }
        },
        timeBits(){
            this.question.timeUnits = 0;
            for(k in this.question.timeBits){
                this.question.timeUnits = this.question.timeUnits | this.question.timeBits[k];
            }
            this.$forceUpdate();
        },
        changeLabel(){
            this.question.allOptionString = JSON.stringify(this.question.allOptionJson);
            this.$forceUpdate();
        },
        forceUpdate() {
            this.question.networkParams = JSON.stringify(this.question.nParams)
            this.$forceUpdate();
        },
        resetParams(param) {
            var newOptions = [];
            var defaultOption, egoOption;
            if(param == "nodeDisplay"){
                this.question.nParams[param].options = newOptions;
                this.question.networkParams = JSON.stringify(this.question.nParams)
                console.log(param, this.question.nParams[param].options);
                this.$forceUpdate();
                return;
            }
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
                else if(param == "nodeShape")
                    var newOptions = [egoOption, defaultOption];
                else
                    var newOptions = [defaultOption];
            }

            typeName = param.toLowerCase().replace("ego","").replace("edge","").replace("node","");

            for(k in options){
                var data = {};
                data["id"] = options[k].id;
                //console.log(options[k])

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
            console.log(param, this.question.nParams[param].options);
            this.$forceUpdate();
        },
        storeImage(event) {
            console.log(event)
            console.log(event.target.id.split("_"))
            g = event.target.id.split("_")[0];
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.addEventListener("load", () => {
                this.question.nGraphs[g].questionLabel = reader.result;
                this.resetGraphs();
            }, false);
            if (file) {
                reader.readAsDataURL(file);
            }
        },
        resetGraphs() {
            this.question.networkGraphs = JSON.stringify(this.question.nGraphs)
            console.log(this.question.networkGraphs);
            this.$forceUpdate();
        },
        deleteLabel(g) {
            this.question.nGraphs[g].questionLabel = "";
            this.question.networkGraphs = JSON.stringify(this.question.nGraphs)
            this.$forceUpdate();
        },
        addGraph() {
            this.question.nGraphs.push(defaultGraph);
            this.question.networkGraphs = JSON.stringify(this.question.nGraphs)
            this.$forceUpdate();
        },
        deleteGraph(g) {
            this.question.nGraphs.splice(g, 1);
            this.question.networkGraphs = JSON.stringify(this.question.nGraphs)
            this.$forceUpdate();
        },
        reorderOption(event) {
            var new_options = $.extend(true, [], this.question.options);
            new_options.splice(event.newIndex, 0, new_options.splice(event.oldIndex, 1)[0])
            for (o in new_options) {
                if(new_options[o].ordering != o){
                    console.log(new_options[o].name, o, new_options[o].ordering)
                    new_options[o].ordering = o;
                }
            }
            //this.question.options = new_options;
            $("#QuestionOption_questionId").val(this.question.id);
            $("#optionsJson").val(JSON.stringify(new_options))
            self = this;
            (function(self) {
                $.post('/authoring/ajaxreorder/' + self.question.studyId, $("#questionOption").serialize(),
                    function(data) {
                        self.question.options = JSON.parse(data);
                    });
            })(self);
        },
        newOption(e) {
            $("#QuestionOption_questionId").val(this.question.id);
            $("#QuestionOption_name").val(this.newOptionName);
            $("#QuestionOption_value").val(this.newOptionValue);
            $("#QuestionOption_otherSpecify").val(this.newOptionOtherSpecify);
            $("#QuestionOption_single").val(this.newOptionSingle);
            $("#QuestionOption_id").val("");
            $("#optionsJson").val(JSON.stringify(this.question.options))
            self = this;
            (function(self) {
                $.post('/authoring/ajaxupdate/' + self.question.studyId, $("#questionOption").serialize(),
                    function(data) {
                        self.newOptionName = "";
                        self.newOptionValue = "";
                        self.question.options = JSON.parse(data);
                    });
            })(self);
        },
        replaceOption(e) {
            if(!this.otherQuestionId)
                return false;
            $("#QuestionOption_questionId").val(this.question.id);
            $("#QuestionOption_value").val(this.otherQuestionId);
            $("#QuestionOption_id").val("replaceOther");
            $("#optionsJson").val(JSON.stringify(this.question.options))
            self = this;
            (function(self) {
                $.post('/authoring/ajaxupdate/' + self.question.studyId, $("#questionOption").serialize(),
                    function(data) {
                        self.newOptionName = "";
                        self.newOptionValue = "";
                        self.question.options = JSON.parse(data);
                    });
            })(self);
        },
        editOption(optionId, button) {
            for (o in this.question.options) {
                if (this.question.options[o].id == optionId) {
                    $("#QuestionOption_questionId").val(this.question.id);
                    $("#QuestionOption_name").val(this.question.options[o].name);
                    $("#QuestionOption_value").val(this.question.options[o].value);
                    $("#QuestionOption_otherSpecify").val(this.question.options[o].otherSpecify);
                    $("#QuestionOption_single").val(this.question.options[o].single);
                    $("#QuestionOption_id").val(optionId);
                    $("#optionsJson").val(JSON.stringify(this.question.options));
                    self = this;
                    (function(self) {
                        $.post('/authoring/ajaxupdate/' + self.question.studyId, $("#questionOption")
                            .serialize(),
                            function(data) {
                                //  self.question.options = JSON.parse(data);
                            });
                    })(self);
                }
            }
        },
        deleteOption(optionId) {
            $("#QuestionOption_questionId").val(this.question.id);
            $("#QuestionOption_id").val(optionId);
            $("#optionsJson").val("");
            self = this;
            (function(self) {
                $.post('/authoring/ajaxdelete/' + self.question.studyId, $("#questionOption").serialize(),
                    function(data) {
                        self.question.options = JSON.parse(data);
                    });
            })(self);
        },
        newPrompt(e) {
            $("#AlterPrompt_questionId").val(this.question.id);
            $("#AlterPrompt_afterAltersEntered").val(this.newPromptAfter);
            $("#AlterPrompt_display").val(this.newPromptDisplay);
            $("#AlterPrompt_id").val("");
            $("#promptsJson").val(JSON.stringify(this.question.alterPrompts))
            self = this;
            (function(self) {
                $.post('/authoring/ajaxupdate/' + self.question.studyId, $("#alterPrompt").serialize(),
                    function(data) {
                        self.question.alterPrompts = JSON.parse(data);
                    });
            })(self);
        },
        uploadPrompt(e) {
            //$("#import_userfile")[0].files = [this.file]
            console.log($("#prompt_" + this.question.id)[0].files);
            $("#import_userfile")[0].files = $("#prompt_" + this.question.id)[0].files;
            $("#import_questionId").val(this.question.id);
            if(!$("#import_userfile")[0].value){
                alert("No file selected")
                return;
            }
            self = this;
            (function(self) {
                var form_data = new FormData($("#importPrompt")[0]);
                console.log(form_data);
                $.ajax({
                    url:'/authoring/importprompts/' + self.question.studyId, 
                    data:form_data,
                    processData: false,
                    contentType: false,
                    method:"POST",
                    success:function(data) {
                        $("#import_userfile")[0].value = null;
                        self.question.alterPrompts = JSON.parse(data);
                    }
                });
            })(self);
        },
        editPrompt(promptId) {
            for (p in this.question.alterPrompts) {
                if (this.question.alterPrompts[p].id == promptId) {
                    $("#AlterPrompt_afterAltersEntered").val(this.question.alterPrompts[p].afterAltersEntered);
                    $("#AlterPrompt_display").val(this.question.alterPrompts[p].display);
                    $("#AlterPrompt_questionId").val(this.question.id);
                    $("#AlterPrompt_studyId").val(this.question.studyId);
                    $("#AlterPrompt_id").val(promptId);
                    $("#promptsJson").val(JSON.stringify(this.question.alterPrompts));
                    self = this;
                    (function(self) {
                        $.post('/authoring/ajaxupdate/' + self.question.studyId, $("#alterPrompt")
                            .serialize(),
                            function(data) {});
                    })(self);
                }
            }
        },
        deletePrompt(promptId) {
            $("#AlterPrompt_questionId").val(this.question.id);
            $("#AlterPrompt_id").val(promptId);
            $("#promptsJson").val("");
            self = this;
            (function(self) {
                $.post('/authoring/ajaxdelete/' + self.question.studyId, $("#alterPrompt").serialize(),
                    function(data) {
                        self.question.alterPrompts = JSON.parse(data);
                    });
            })(self);
        },
        changeSubjectType(val) {
            if(val == "NAME_GENERATOR" || val == "MERGE_ALTER"){
                this.question.answerType = "NO_RESPONSE";
                this.$forceUpdate();
            }else if(val == "ALTER" || val == "ALTER_PAIR"){
                this.question.answerType = "MULTIPLE_SELECTION";
                this.$forceUpdate();
            }else if(val == "NETWORK"){
                //this.question.answerType = "TEXTUAL_PP";
                this.question.networkParams = JSON.stringify(this.question.nParams);
                this.$forceUpdate();
            }
        },
        changeAnswerType(val) {
            if(val == "NO_RESPONSE"){
                //this.question.subjectType = "EGO";
                this.$forceUpdate();
            }
        },
        saveQuestion() {
            $("#" + this.question.id + "_prompt").val(rebuildEgowebTags($("#" + this.question.id + "_prompt").val(), this.question.id + "_prompt"));
            $("#" + this.question.id + "_citation").val(rebuildEgowebTags($("#" + this.question.id + "_citation").val(), this.question.id + "_citation"));
           $("#form-" + this.question.id).submit();
        },
        duplicateQuestion() {
            $("#duplicateQuestionId").val(this.question.id);
            $("#duplicateQuestion").submit();
        },
        deleteQuestion() {
            $("#deleteQuestionId").val(this.question.id);
            $("#deleteQuestion").submit();
        },
    }
});

authoring = new Vue({
    el: '#authoring-app',
    components: {
        QestionEditor: QestionEditor,
        SummerNote: SummerNote,
    },
    data() {
        return {
            new_question: new_question,
            questions: questions,
            all_questions: all_questions,
            study: study,
            origPrompt:study.egoIdPrompt,
        }
    },
    created() {
        console.log(questions, all_questions)

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
        var nQuestions = [];
        var bitVals = {
                'BIT_YEAR': 1,
                'BIT_MONTH': 2,
                'BIT_WEEK': 4,
                'BIT_DAY': 8,
                'BIT_HOUR': 16,
                'BIT_MINUTE': 32,
            };
        alterQs.push({text:"Degree Centrality", value:"degree"})
        alterQOptions["degree"] = [{id:"degree",name:""}]
        alterQs.push({text:"Betweenness Centrality", value:"betweenness"})
        alterQOptions["betweenness"] = [{id:"betweenness",name:""}]
        alterQs.push({text:"Eigenvector Centrality", value:"eigenvector"})
        alterQOptions["eigenvector"] = [{id:"eigenvector",name:""}]
        this.new_question.timeBits = {};
        this.new_question.dontKnowText = "Don't Know";
        this.new_question.refuseText = "Refuse";
        for (var t in bitVals) {
            this.new_question.timeBits[t] = this.new_question.timeUnits & bitVals[t];
        }
        for(k in this.questions){
            if(!this.questions[k].dontKnowText)
                this.questions[k].dontKnowText = "Don't Know";
            if(!this.questions[k].refuseText)
                this.questions[k].refuseText = "Refuse";
            this.questions[k].numQuestions = numQuestions.slice();
            if(this.questions[k].answerType == "NUMERICAL"){
                numQuestions.push({text:this.questions[k].title,value:this.questions[k].id});
            }
            if (this.questions[k].answerType == "MULTIPLE_SELECTION") {
                multiQuestions.push({text:this.questions[k].title,value:this.questions[k].id});
            }
   
            this.questions[k].timeBits = {};
            for (var t in bitVals) {
                this.questions[k].timeBits[t] = this.questions[k].timeUnits & bitVals[t];
            }
            if (questions[k].subjectType == "NETWORK")
                nQuestions.push({text:this.questions[k].title,value:this.questions[k].id});
        }

        for(k in this.all_questions){
            let questionTitle = this.all_questions[k].title;
            if(this.study.multiSessionEgoId > 0)
                questionTitle = studyNames[this.all_questions[k].studyId] + ":" + this.all_questions[k].title;
            if (this.all_questions[k].subjectType == "ALTER"){
                alterQs.push({text:questionTitle, value:this.all_questions[k].id})
                alterQOptions[all_questions[k].id] =  all_questions[k].optionsList;
                alterShapeQs.push({text:questionTitle, value:this.all_questions[k].id})
                alterShapeQOptions[all_questions[k].id] =  all_questions[k].optionsList;
                alterQIds.push(parseInt(this.all_questions[k].id))
            }
            if (this.all_questions[k].subjectType == "ALTER_PAIR") {
                alterPairQs.push({text:questionTitle, value:this.all_questions[k].id})
                alterPairQOptions[all_questions[k].id] =  all_questions[k].optionsList;
                alterPairQIds.push(parseInt(this.all_questions[k].id));
            }


        }
        console.log("nQuestions", nQuestions);
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
            if(expressions[k].type == "Compound"){
                alterExps.push(expressions[k]);
                alterPairExps.push(expressions[k]);
                alterQs.push({text:expressions[k].name, value:"expression_" + expressions[k].id})
                alterQOptions["expression_" + expressions[k].id] = [{id:1,name:"True"},{id:0,name:"False"}]
                alterShapeQs.push({text:expressions[k].name, value:"expression_" + expressions[k].id})
                alterShapeQOptions["expression_" + expressions[k].id] = [{id:1,name:"True"},{id:0,name:"False"}]
                alterPairQs.push({text:expressions[k].name, value:"expression_" + expressions[k].id})
                alterPairQOptions["expression_" + expressions[k].id] = [{id:1,name:"True"},{id:0,name:"False"}]
            }
        }
        for(k in this.questions){
            this.questions[k].alterQs = alterQs;
            this.questions[k].alterPairQs = alterPairQs;
            this.questions[k].alterShapeQs = alterShapeQs;
            this.questions[k].alterExps = alterExps;
            this.questions[k].alterPairExps = alterPairExps;
            this.questions[k].alterQOptions = alterQOptions;
            this.questions[k].alterPairQOptions = alterPairQOptions;
            this.questions[k].alterShapeQOptions = alterShapeQOptions;
            this.questions[k].multiQuestions = [];
            this.questions[k].nQuestions = nQuestions;

            for(m in multiQuestions){
                if(multiQuestions[m].value != this.questions[k].id)
                    this.questions[k].multiQuestions.push(multiQuestions[m])
            }
            if(this.questions[k].allOptionString)
                this.questions[k].allOptionJson = JSON.parse(this.questions[k].allOptionString);
            else {
                this.questions[k].allOptionJson = {"YES_LABEL":"Yes", "NO_LABEL":"No", "NEW_NAME_LABEL":""};
                this.questions[k].allOptionString = JSON.stringify(this.questions[k].allOptionJson);
            }
            var defaultParams = {
                egoLabel:"You",
                nodeDisplay: false,
                nodeColor:{questionId:'', options:[{id:-1, color:"#000"}, {id:'default', color:"#000"}]},
                nodeSize:{questionId:'', options:[{id:-1, size:2}, {id:'default', size:2}]},
                nodeShape:{questionId:'', options:[{id:-1, shape:'circle'},{id:'default', shape:'circle'}]},
                edgeColor:{questionId:'', options:[{id:'default', color:"#000"}]},
                edgeSize:{questionId:'', options:[{id:'default', size:1}]},
                egoEdgeColor:{questionId:'', options:[{id:'default', color:"#000"}]},
                egoEdgeSize:{questionId:'', options:[{id:'default', size:1}]},
            }
            var defaultGraphs = [
                {title:'', questionId:'', questionLabel:'', size:20},
                {title:'', questionId:'', questionLabel:'', size:20},
                {title:'', questionId:'', questionLabel:'', size:20}
            ];
            defaultGraph = {title:'', questionId:'', questionLabel:'', size:20};
            if(this.questions[k].subjectType == "MULTI_GRAPH"){
                if(this.questions[k].networkGraphs == "" || this.questions[k].networkGraphs == null || this.questions[k].networkGraphs == "null"){
                    this.questions[k].nGraphs = [defaultGraph];
                    this.questions[k].networkGraphs = JSON.stringify(this.questions[k].nGraphs);
                }else{
                    this.questions[k].nGraphs = JSON.parse(this.questions[k].networkGraphs);
                    for(g in this.questions[k].nGraphs){
                        for(o in defaultGraph){
                            if(typeof this.questions[k].nGraphs[g][o] == "undefined"){
                                this.questions[k].nGraphs[g][o] = defaultGraph[o];
                            }
                        }
                    }
                }
            }
            if(this.questions[k].subjectType == "NETWORK"){
                if(this.questions[k].networkParams == "" || this.questions[k].networkParams == null || this.questions[k].networkParams == "null"){
                    this.questions[k].nParams = defaultParams;
                    this.questions[k].networkParams = JSON.stringify(this.questions[k].nParams);
                }else{
                    this.questions[k].nParams = JSON.parse(this.questions[k].networkParams);
                    for(p in defaultParams){
                        if(p == "nodeDisplay")
                            continue;
                        if(typeof this.questions[k].nParams[p] == "undefined"){
                            this.questions[k].nParams[p] = defaultParams[p];
                        }else{
                            if(p != "egoLabel"){
                                var egoOption = defaultParams[p].options[0];
                                var defaultOption = defaultParams[p].options[1];
                                var newOptions = [];
                            }
                            if(typeof this.questions[k].nParams[p].questionId == "undefined")
                                this.questions[k].nParams[p].questionId = "";
                            if(p == "nodeColor" || p == "nodeSize" || p == "nodeShape"){
                                let oIds = {};
                                for(var i = 0; i < this.questions[k].nParams[p].options.length; i++){
                                    if(this.questions[k].nParams[p].options[i].id == 0)
                                        this.questions[k].nParams[p].options[i].id = "default";
                                    if(this.questions[k].nParams[p].options[i].id == "default"){
                                        defaultOption = this.questions[k].nParams[p].options[i];
                                    }else if(this.questions[k].nParams[p].options[i].id == -1){
                                        egoOption = this.questions[k].nParams[p].options[i];
                                    }else{
                                        console.log(!(this.questions[k].nParams[p].options[i].id.toString() in oIds))
                                        if (!(this.questions[k].nParams[p].options[i].id.toString() in oIds)) {
                                            oIds[this.questions[k].nParams[p].options[i].id.toString()] = this.questions[k].nParams[p].options[i];
                                        }
                                    }
                                }
                                if(!isNaN(this.questions[k].nParams[p].questionId) && typeof alterQOptions[this.questions[k].nParams[p].questionId] != "undefined")
                                    console.log(alterQOptions[this.questions[k].nParams[p].questionId].length + 2 - this.questions[k].nParams[p].options.length )
                                for(q in alterQOptions[this.questions[k].nParams[p].questionId]){
                                    console.log(oIds,alterQOptions[this.questions[k].nParams[p].questionId][q].id)
                                    if(!(alterQOptions[this.questions[k].nParams[p].questionId][q].id in oIds)){
                                        let newDefaultOption = $.extend(true, {}, defaultOption);
                                        newDefaultOption.id = alterQOptions[this.questions[k].nParams[p].questionId][q].id;
                                        console.log(newDefaultOption)
                                        newOptions.push(newDefaultOption);
                                    }else{
                                        newOptions.push(oIds[alterQOptions[this.questions[k].nParams[p].questionId][q].id]);
                                    }
                                }
                                newOptions.unshift(defaultOption);
                                newOptions.unshift(egoOption);
                                this.questions[k].nParams[p].options = newOptions;
                          //      if(typeof alterQOptions[this.questions[k].nParams[p].questionId] != "undefined")
                           //       console.log(p + " changed",this.questions[k].nParams[p].options.length, alterQOptions[this.questions[k].nParams[p].questionId].length, this.questions[k].nParams[p].options, alterQOptions[this.questions[k].nParams[p].questionId])
                            }else if(p == "edgeColor" || p == "edgeSize"){
                                let oIds = {};
                                let defaultOption = defaultParams[p].options[0];
                                console.log("edge options", this.questions[k].nParams[p].options)
                                for(var i = 0; i < this.questions[k].nParams[p].options.length; i++){
                                    if(this.questions[k].nParams[p].options[i].id == 0 && isNaN(p))
                                        this.questions[k].nParams[p].options[i].id = "default"
                                    if(this.questions[k].nParams[p].options[i].id == "default"){
                                        defaultOption = this.questions[k].nParams[p].options[i];
                                    }else{
                                        if (!(this.questions[k].nParams[p].options[i].id.toString() in oIds)) {
                                            oIds[this.questions[k].nParams[p].options[i].id.toString()] = this.questions[k].nParams[p].options[i];
                                        }
                                    }
                                }
                                if(typeof alterPairQOptions[this.questions[k].nParams[p].questionId] != "undefined"){
                                    for(q in alterPairQOptions[this.questions[k].nParams[p].questionId]){
                                        //console.log(alterPairQOptions[this.questions[k].nParams[p].questionId][q].id)
                                        if(!(alterPairQOptions[this.questions[k].nParams[p].questionId][q].id in oIds)){
                                            var newDefaultOption = $.extend(true, {}, defaultOption);
                                            newDefaultOption.id = parseInt(alterPairQOptions[this.questions[k].nParams[p].questionId][q].id);
                                            newOptions.push(newDefaultOption);
                                        }else{
                                            newOptions.push(oIds[alterPairQOptions[this.questions[k].nParams[p].questionId][q].id]);
                                        }
                                    }
                                }
                                newOptions.unshift(defaultOption);
                                console.log("fix 2", newOptions)

                                this.questions[k].nParams[p].options = newOptions;
                            }else if (p == "egoEdgeColor" || p == "egoEdgeSize"){
                                let oIds = {};
                                for(var i = 0; i < this.questions[k].nParams[p].options.length; i++){
                                    if(this.questions[k].nParams[p].options[i].id == 0)
                                        this.questions[k].nParams[p].options[i].id = "default";
                                    if(this.questions[k].nParams[p].options[i].id == "default"){
                                        defaultOption = this.questions[k].nParams[p].options[i];
                                    }else{
                                        if (!(this.questions[k].nParams[p].options[i].id.toString() in oIds)) {
                                            oIds[this.questions[k].nParams[p].options[i].id.toString()] = this.questions[k].nParams[p].options[i];
                                        }
                                    }
                                }
                                if(!isNaN(this.questions[k].nParams[p].questionId) && typeof alterShapeQOptions[this.questions[k].nParams[p].questionId] != "undefined")
                                    console.log(alterShapeQOptions[this.questions[k].nParams[p].questionId].length + 2 - this.questions[k].nParams[p].options.length )
                                if(!isNaN(this.questions[k].nParams[p].questionId) && typeof alterShapeQOptions[this.questions[k].nParams[p].questionId] != "undefined"){

                                    for(q in alterShapeQOptions[this.questions[k].nParams[p].questionId]){
                                        console.log(oIds,alterShapeQOptions[this.questions[k].nParams[p].questionId][q].id)
                                        if(!(alterQOptions[this.questions[k].nParams[p].questionId][q].id in oIds)){
                                            let newDefaultOption = $.extend(true, {}, defaultOption);
                                            newDefaultOption.id = alterShapeQOptions[this.questions[k].nParams[p].questionId][q].id;
                                            console.log(newDefaultOption)
                                            newOptions.push(newDefaultOption);
                                        }else{
                                            newOptions.push(oIds[alterShapeQOptions[this.questions[k].nParams[p].questionId][q].id]);
                                        }
                                    }
                                }
                              //  newOptions.unshift(defaultOption);
                                this.questions[k].nParams[p].options = newOptions;
                                console.log(p, this.questions[k].nParams[p])
                            }
                        }
                    }

                }
                new_question.nParams = defaultParams;
            }
        }
    },
    mounted() {
        var self = this;
    },
    methods: {
        saveStudy() {
            //self = this;
            (function(self) {
                return $.post('/authoring/ajaxupdate/' + self.study.id, {
                    Study: {
                        "id":self.study.id,
                        "egoIdPrompt":$("#Study_egoIdPrompt").val()
                    }
                }, function(data) {
                    self.origPrompt = self.study.egoIdPrompt;
                });
            })(this);
        },
        reorderQuestion(event) {
            var new_questions = $.extend(true, [], this.questions);
            //console.log(event.newIndex, event.oldIndex);
            new_questions.splice(event.newIndex, 0, new_questions.splice(event.oldIndex, 1)[0])
            qList = [];
            for (q in new_questions) {
                if(new_questions[q].ordering != q){
                    //console.log(new_questions[q].title, new_questions[q].ordering, q)
                    new_questions[q].ordering = q;
                }
                qList.push({id:new_questions[q].id})
            }
            this.questions = new_questions;
            self = this;
            (function(self) {
                $.post('/authoring/ajaxreorder/' + self.study.id, {
                    questions: {
                        ...qList
                    }
                }, function(data) {});
            })(self);
        }
    }
})

$("form").on("keypress", function(event) {
    var keyPressed = event.keyCode || event.which;
    if (keyPressed === 13) {
        event.preventDefault();
        return false;
    }
});
</script>
<?= Html::beginForm(['/authoring/importprompts/'.$study->id], 'post', [ 'id'=>'importPrompt', "class"=>"d-none", 'enctype' => 'multipart/form-data']) ?>
<input type="file" id="import_userfile" name="userfile" />
<input type="hidden" id="import_questionId" name="questionId" />
<?= Html::endForm() ?>
<?= Html::beginForm(['/authoring/'.$study->id], 'post', [ 'id'=>'alterPrompt', "class"=>"d-none"]) ?>
<input type="hidden" id="AlterPrompt_id" name="AlterPrompt[id]">
<input type="hidden" id="AlterPrompt_studyId" name="AlterPrompt[studyId]" value="<?php echo $study->id; ?>">
<input type="hidden" id="AlterPrompt_afterAltersEntered" name="AlterPrompt[afterAltersEntered]">
<input type="hidden" id="AlterPrompt_display" name="AlterPrompt[display]">
<input type="hidden" id="AlterPrompt_questionId" name="AlterPrompt[questionId]">
<input type="hidden" id="promptsJson" name="prompts" />
<?= Html::endForm() ?>
<?= Html::beginForm(['/authoring/'.$study->id], 'post', [ 'id'=>'questionOption', "class"=>"d-none"]) ?>
<input type="hidden" id="QuestionOption_id" name="QuestionOption[id]">
<input type="hidden" id="QuestionOption_name" name="QuestionOption[name]">
<input type="hidden" id="QuestionOption_value" name="QuestionOption[value]">
<input type="hidden" id="QuestionOption_otherSpecify" name="QuestionOption[otherSpecify]">
<input type="hidden" id="QuestionOption_single" name="QuestionOption[single]">
<input type="hidden" id="QuestionOption_studyId" name="QuestionOption[studyId]" value="<?php echo $study->id; ?>">
<input type="hidden" id="QuestionOption_questionId" name="QuestionOption[questionId]">
<input type="hidden" id="optionsJson" name="options" />
<?= Html::endForm() ?>
<?= Html::beginForm(['/authoring/ajaxdelete/'.$study->id], 'post', [ 'id'=>'deleteQuestion', "class"=>"d-none"]) ?>
<input type="hidden" id="deleteQuestionId" name="Question[id]">
<?= Html::endForm() ?>
<?= Html::beginForm(['/authoring/duplicatequestion/'.$study->id], 'post', [ 'id'=>'duplicateQuestion', "class"=>"d-none"]) ?>
<input type="hidden" id="duplicateQuestionId" name="questionId">
<?= Html::endForm() ?>
