<?php
use yii\helpers\Html;
?>
<?= $this->render('/layouts/nav', ['study'=> $study]); ?>

<div id="authoring-app">
    <div class="row">
        <div class="col-md-4">
        <?= Html::beginForm(['/authoring/addinterviewer/'.$study['id']], 'post', [ 'id'=>'addInterviewer']) ?>
            <input type="hidden" name="Interviewer[studyId]" value="<?php echo $study['id']; ?>" />
            <b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="interviewers" :fields="user_fields" striped responsive="sm">
            <template #cell(details)="row">
                                <b-link href="#" @click="deleteInterviewer(row.item.id)"><i class="fas fa-times"></i></b-link>
                            </template>
            <template v-slot:custom-foot>
                <tr class="text-white" >
                    <td>
                    <b-form-select  name="Interviewer[interviewerId]"  :options="users" text-field="name" value-field="id" >
                </b-form-select>
                    </td>
                    <td>
                        <button class="btn btn-primary button-xs">Create</button>
                    </td>
                </tr>
            </template>
            </b-table>
            <?= Html::endForm() ?>
        </div>

<?= Html::beginForm(['/authoring/delete-interviewer/'.$study['id']], 'post', [ 'id'=>'deleteInterviewer', "class"=>"d-none"]) ?>
<input type="hidden" id="deleteInterviewerId" name="Interviewer[id]">
<?= Html::endForm() ?>
        <div class="col-md-8">
        <?= Html::beginForm(['/authoring/ajaxupdate/'.$study['id']], 'post', [ 'id'=>'addAlterList']) ?>

            <b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="alterList" :fields="list_fields" striped responsive="sm">
            <template v-slot:custom-foot>
            <tr  >
                <td>
                    <input name="AlterList[name]">
                </td>
                <td>
                    <input name="AlterList[email]">
                </td>
                <td>
                    <input type="hidden" v-model="nameGenQIds" name="AlterList[nameGenQIds]" />
                    <b-form-checkbox-group
                    :options="questions"
                    text-field="title"
                    value-field="id"
                    @change="checkVal($event)"
                    >
                    </b-form-checkbox-group>
                </td>
                <td>
                    <b-form-select
                        name="AlterList[interviewerId]"
                        :options="interviewers"
                        class="mb-3 text-black"
                        value-field="id"
                        text-field="interviewer"
                        stacked
                    ></b-form-select>

                </td>
                <td>
                    <button variant="primary" size="xs">Create</button>
                </td>
            </tr>
        </template>
            </b-table>
            <?= Html::endForm() ?>

        </div>
    </div>
</div>
<script>
interviewers = <?php echo json_encode($interviewers, ENT_QUOTES); ?>;
alterList = <?php echo json_encode($alterList, ENT_QUOTES); ?>;
users = <?php echo json_encode($users, ENT_QUOTES); ?>;
questions = <?php echo json_encode($questions, ENT_QUOTES); ?>;

new Vue({
  el: '#authoring-app',
    data() {
        return {
            user_fields: [
                'interviewer',
                'role',
                {
                    key: "details",
                    label: ""
                }
            ],
            list_fields: [
                'name',
                'email',
                'name generators',
                'interviewer',
                {
                    key: "details",
                    label: ""
                }
            ],
            interviewers: interviewers,
            alterList: alterList,
            questions: questions,
            users: users,
            nameGenQIds:'',
        }
    },
    methods: {
        checkVal(val){
            this.$forceUpdate();
            this.nameGenQIds = val.join(",")
        },
        deleteInterviewer(id) {
            $("#deleteInterviewerId").val(id);
            $("#deleteInterviewer").submit();
        },
        setAttribute(item, type) {
            return {
                'data-id': item.id,
            }
        },
    }
});
</script>