<?php
use yii\helpers\Html;
use yii\bootstrap4\LinkPager;

?>
<?= $this->render('/layouts/nav', ['study'=> $study]); ?>
<div id="authoring-app">
    <div class="row">
        <div class="col-md-4">
            <h4>Users</h4>
            <?= Html::beginForm(['/authoring/addinterviewer/'.$study['id']], 'post', [ 'id'=>'addInterviewer']) ?>
            <input type="hidden" name="Interviewer[studyId]" value="<?php echo $study['id']; ?>" />
            <b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="interviewers"
                :fields="user_fields" striped responsive="sm">
                <template #cell(details)="row">
                    <b-link href="#" @click="deleteInterviewer(row.item.id)"><i class="fas fa-times"></i></b-link>
                </template>
                <template v-slot:custom-foot>
                    <tr class="text-white">
                        <td>
                            <b-form-select name="Interviewer[interviewerId]" :options="users" text-field="name"
                                value-field="id">
                            </b-form-select>
                        </td>
                        <td>
                            <button class="btn btn-primary button-xs">Add To Study</button>
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
            <h4>Participants</h4>
<?php
echo LinkPager::widget([
'pagination' => $pagination,
]);
?>
            <b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="alterList"
                :fields="list_fields" striped responsive="sm">
                <template #cell(name)="row">
                    <input class="form-control input-xs" @blur="editOption(row.item.id)"
                        v-on:keyup.13="editOption(row.item.id)" v-model="row.item.name" />
                </template>
                <template #cell(email)="row">
                    <input class="form-control input-xs" @blur="editOption(row.item.id)"
                        v-on:keyup.13="editOption(row.item.id)" v-model="row.item.email" />
                </template>
                <template #cell(nameGenQIds)="row">
                    <b-form-checkbox-group v-model="row.item.nameGenQIdsArray" :options="questions" text-field="title"
                        value-field="id" @change="editVal(row.item.id, $event)">
                    </b-form-checkbox-group>
                </template>
                <template #cell(interviewerId)="row">
                    <b-form-select v-model="row.item.interviewerId" name="AlterList[interviewerId]"
                        :options="interviewers" class="mb-3 text-black input-xs" value-field="id" @change="editOption(row.item.id)"
                        text-field="name" stacked>
                        <template #first>
                            <b-form-select-option value="" selected></b-form-select-option>
                        </template>
                    </b-form-select>
                </template>
                <template #cell(info)="row">
                    <b-link href="#" @click="row.toggleDetails"><i class="fas fa-link"></i></b-link>
                    <b-link href="#" @click="deleteAlterList(row.item.id)"><i class="fas fa-times"></i></b-link>
                </template>
                <template #row-details="row">
                    {{row.item.url}}
                </template>
                <template v-slot:custom-foot>
                    <tr>
                        <td colspan=5>
                            <?= Html::beginForm(['/authoring/ajaxupdate/'.$study['id']], 'post', [ 'id'=>'addAlterList']) ?>
                
                            <div class="row">
                                <div class="col-sm-2">
                                    <input class="form-control input-xs" name="AlterList[name]">
                                </div>
                                <div class="col-sm-2">
                                    <input class="form-control input-xs" name="AlterList[email]">
                                </div>
                                <input type="hidden" v-model="nameGenQIds" name="AlterList[nameGenQIds]" />
                                <div class="col-sm-4">
                                    <b-form-checkbox-group :options="questions" text-field="title" value-field="id"
                                        @change="checkVal($event)">
                                    </b-form-checkbox-group>
                                </div>
                                <div class="col-sm-2">
                                    <b-form-select name="AlterList[interviewerId]" :options="interviewers"
                                        class="mb-3 text-black input-xs" value-field="id" text-field="name"
                                        stacked>
                                        <template #first>
                                            <b-form-select-option value="" selected>-- Interviewer --
                                            </b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm">Create</button>
                                </div>
                            </div>

                            <?= Html::endForm() ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=3>
                            <?= Html::beginForm(['/authoring/importlist/'.$study['id']], 'post', [ 'id'=>'importAlterList', 'enctype' => 'multipart/form-data']) ?>
                            <input type="file" name="userfile">
                            <button class="btn btn-primary btn-sm float-right">Upload</button>
                            <?= Html::endForm() ?>
                        </td>
                        <td>
                            <?= Html::beginForm(['/authoring/exportalterlist/'.$study['id']], 'post', [ 'id'=>'exportAlterList', 'enctype' => 'multipart/form-data']) ?>
                            <button class="btn btn-info btn-sm float-right">export particpants</button>
                            <?= Html::endForm() ?>
                        </td>
                        <td>
                            <b-button class="btn btn-info btn-sm float-right btn-danger" @click="deleteAllAlterList">Delete All</b-button>
                        </td>
                    </tr>
                </template>
            </b-table>
        </div>
    </div>
</div>
<?= Html::beginForm(['/authoring/ajaxdelete/'.$study['id']], 'post', [ 'id'=>'deleteAlterList', "class"=>"d-none"]) ?>
<input type="hidden" id="deleteAlterListId" name="AlterList[id]">
<?= Html::endForm() ?>
<?= Html::beginForm(['/authoring/ajaxupdate/'.$study['id']], 'post', [ 'id'=>'updateAlterList', "class"=>"d-none"]) ?>
<input type="hidden" id="updateAlterListId" name="AlterList[id]">
<input type="hidden" id="updateAlterListName" name="AlterList[name]">
<input type="hidden" id="updateAlterListEmail" name="AlterList[email]">
<input type="hidden" id="updateAlterListNameGenQIds" name="AlterList[nameGenQIds]">
<input type="hidden" id="updateAlterListInterviewerId" name="AlterList[interviewerId]">
<?= Html::endForm() ?>
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
                'name',
                'role',
                {
                    key: "details",
                    label: ""
                }
            ],
            list_fields: [
                {
                    key:'name',
                    class:'col-2'
                },
                {
                    key:'email',
                    class:'col-2'
                },
                {
                    key:'nameGenQIds',
                    class:'col-4'
                },
                'interviewerId',
                {
                    key: "info",
                    label: ""
                }
            ],
            interviewers: interviewers,
            alterList: alterList,
            questions: questions,
            users: users,
            nameGenQIds: '',
        }
    },
    methods: {
        checkVal(val) {
            console.log(val);
            this.$forceUpdate();
            this.nameGenQIds = val.join(",")
        },
        editVal(id, val) {
            for(k in this.alterList){
                if(this.alterList[k].id == id){
                    this.$forceUpdate();
                    this.alterList[k].nameGenQIds = val.join(",")
                    this.updateList(id);
                }
            }

        },
        editOption(id) {
            for(k in this.alterList){
                if (this.alterList[k].id == id) {
                    console.log(this.alterList[k]);
                    this.updateList(id);
                }
            }
        },
        deleteInterviewer(id) {
            $("#deleteInterviewerId").val(id);
            $("#deleteInterviewer").submit();
        },
        deleteAlterList(id) {
            $("#deleteAlterListId").val(id);
            $("#deleteAlterList").submit();
        },
        getLink(id) {
            $.post("/authoring/getlink/<?php echo $study['id']; ?>", {'alterListId': id}, function(data){
                console.log(data);
            });
        },
        deleteAllAlterList(id) {
            if(confirm("Delete the entire list of participants?")){
                $("#deleteAlterListId").val("all");
                $("#deleteAlterList").submit();
            }
        },
        updateList(id){
            for(k in this.alterList){
                if(this.alterList[k].id == id){
                    $("#updateAlterListId").val(this.alterList[k].id);
                    $("#updateAlterListName").val(this.alterList[k].name);
                    $("#updateAlterListEmail").val(this.alterList[k].email);
                    $("#updateAlterListNameGenQIds").val(this.alterList[k].nameGenQIds);
                    $("#updateAlterListInterviewerId").val(this.alterList[k].interviewerId);
                    $.post("/authoring/ajaxupdate/<?php echo $study['id']; ?>",$("#updateAlterList").serialize(),function(data){
                        console.log(data);
                    });
                }
            }
        },
        setAttribute(item, type) {
            return {
                'data-id': item.id,
            }
        },
    }
});
</script>