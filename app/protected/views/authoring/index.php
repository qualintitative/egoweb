<?php
use yii\helpers\Html;

?>
            <?= $this->render('/layouts/nav', ['study'=> $study]); ?>

<div id="authoring-app">
<?= Html::beginForm(['/authoring/'.$study['id']], 'post', [ 'id'=>'analysis']) ?>
<div class="row form-group">
    <div class="col-md-6">
        <div class="row form-group">
            <label for="Question_title" class="col-sm-4 col-form-label">Title</label>
            <div class="col-sm-8">
                <input type="text" v-model="study.name" class="form-control" name="Study[name]" id="Study_name">
            </div>
        </div>
    </div>
    <div class="col-md-6">
    <span class="badge badge-light mt-2">last modified: {{study.modified}}</span>
        <?php if($interviews > 0): ?>
        <a class="btn btn-sm btn-info float-right" href="/data/<?php echo $study['id']; ?>">Data Processing (<?php echo $interviews; ?>)</a>
        <?php endif; ?>
    </div>
</div>
<div class="form-group row">
    <div class="col-md-6">
        <label for="Study_introduction" class="col-sm-4 col-form-label">Introduction</label>
        <summer-note :model.sync="study.introduction" ref="Study_introduction" name="Study[introduction]"
        vid="Study_introduction"></summer-note>
    </div>
    <div class="col-md-6">
        <label for="Study_conclusion" class="col-sm-4 col-form-label">Conclusion</label>
        <summer-note :model.sync="study.conclusion" ref="Study_conclusion" name="Study[conclusion]" vid="Study_conclusion">
        </summer-note>
    </div>
</div>
<div class="form-group row mb-3">
    <div class="col-md-6">
        <label for="Study_header" class="col-sm-4 col-form-label">Header</label>
        <summer-note :model.sync="study.header" ref="Study_header" name="Study[header]" vid="Study_header">
        </summer-note>
    </div>
    <div class="col-md-6">
        <label for="Study_footer" class="col-sm-4 col-form-label">Footer</label>
        <summer-note :model.sync="study.footer" ref="Study_footer" name="Study[footer]" vid="Study_footer">
        </summer-note>
    </div>
</div>
<div class="form-group row">
    <div class="col-md-6 mb-3">
    <div class="form-group">
            <label for="Study_style" class="col-sm-4 col-form-label">CSS Style</label>
            <textarea class="form-control" rows=4 v-model="study.style" name="Study[style]" id="Study_style"></textarea>
        </div>
        <div class="form-group">
            <label for="Study_javascript" class="col-sm-4 col-form-label">Javascript</label>
            <textarea class="form-control" rows=4 v-model="study.javascript" name="Study[javascript]" id="Study_javascript"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-8">Refusal Value</label>
            <input class="col-2" v-model="study.valueRefusal" placeholder="edit me" name="Study[valueRefusal]" id="Study_valueRefusal">
        </div>
        <div class="form-group row">
            <label class="col-8">Don't Know Value</label>
            <input class="col-2" v-model="study.valueDontKnow" placeholder="edit me" name="Study[valueDontKnow]" id="Study_valueDontKnow">
        </div>
        <div class="form-group row">
            <label class="col-8">Logical Skip Value</label>
            <input class="col-2" v-model="study.valueLogicalSkip" placeholder="edit me" name="Study[valueLogicalSkip]" id="Study_valueLogicalSkip">
        </div>
        <div class="form-group row">
            <label class="col-8 col-form-label">Not Yet Answered Value</label>
            <input class="col-2" v-model="study.valueNotYetAnswered" placeholder="edit me" name="Study[valueNotYetAnswered]" id="Study_valueNotYetAnswered">
        </div>
        <div class="form-group row mb-2">
            <label for="Study_multiSessionEgoId" class="col-8 col-form-label">Multi-session Ego ID Link Variable</label>
            <div class="col-3">
            <b-form-select v-model="study.multiSessionEgoId" :options="options" name="Study[multiSessionEgoId]" id="Study_multiSessionEgoId"></b-form-select>
            </div>
        </div>
    <div class="form-group mt-4">
        <b-form-checkbox
        id="Study_hideEgoIdPage"
        v-model="study.hideEgoIdPage"
        name="Study[hideEgoIdPage]"
        value="1"
        unchecked-value="0"
        >
        Hide Ego Id Page (for studies will Ego Id prefills)
        </b-form-checkbox>

        <b-form-checkbox
        id="Study_fillAlterList"
        v-model="study.fillAlterList"
        name="Study[fillAlterList]"
        value="1"
        unchecked-value="0"
        >
        Populate alter list from participant list 
        </b-form-checkbox>
        <b-form-checkbox
        id="Study_restrictAlters"
        v-model="study.restrictAlters"
        name="Study[restrictAlters]"
        value="1"
        unchecked-value="0"
        >
        Restrict alters to participant list
        </b-form-checkbox>
        <b-form-checkbox
        id="Study_useAsAlters"
        v-model="study.useAsAlters"
        name="Study[useAsAlters]"
        value="1"
        unchecked-value="0"
        >
        Populate alter list from participant list
        </b-form-checkbox>
</div>
        <div class="btn-group col mt-3 row">
                <button id="saveStudy" class="btn btn-success">Save</button>
                <b-button class="btn btn-warning" @click="replicateStudy">Replicate</b-button>
                <b-button class="btn btn-danger"  @click="deleteStudy" :disabled="interviews != 0" title="You can only delete studies without interview data">Delete</b-button>
            </div>
    </div>

</div>
    <?= Html::endForm() ?>
</div>
<script>
SummerNote = Vue.component('summer-note', {
    template: '<textarea ref="summernote" :id="vid" :name="name"></textarea>',
    props: ['name', 'model', 'vid'],
    computed: {
        summernote() {
            return $(this.$refs.summernote);
        }
    },
    mounted() {
        var self = this;
        $(this.$refs.summernote).summernote({
            height: 100,
            toolbar: noteBar,            
            callbacks: {
                onInit: function() {
                    $(this).summernote("code", self.model);
                },
                onKeyup: function(e) {
                    self.$emit("update:model", $(this).summernote('code'));
                }
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

v = new Vue({
    el: '#authoring-app',
    components: {
        SummerNote: SummerNote
    },
    data() {
        return {
            interviews: <?php echo $interviews; ?>,
            study: <?php echo json_encode($study, ENT_QUOTES); ?>,
            options: <?php echo json_encode($egoIdOptions, ENT_QUOTES); ?>,
        }
    },
    mounted() {
        var self = this;
    },
    methods: {
        replicateStudy(){
            document.location = "/authoring/replicate/" + this.study.id;
        },
        deleteStudy(){
            if(confirm("Are you sure you want to delete this study?"))
                document.location = "/authoring/delete/" + this.study.id;
        },
        getValue() {
            var overview = this.$refs.editor.getVal()
            console.log(overview);
        }
    }
})
</script>