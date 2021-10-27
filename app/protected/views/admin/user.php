<?php
use yii\helpers\Html;
?>
<div id="useradmin-app">
<?= Html::beginForm(['/admin/user'], 'post', [ 'id'=>'addUser']) ?>

<b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="users" :fields="user_fields" striped responsive="sm">

    <template #cell(permissions)="row">
        {{roles[row.item.permissions].text}}
    </template>

    <template #cell(info)="row">
        <b-link @click="row.toggleDetails"><i class="fas fa-edit"></i></b-link>
        <b-link href="#" @click="deleteUser(row.item.id)"><i class="fas fa-times"></i></b-link>
    </template>

    <template #row-details="row">
          <b-row class="mb-2">
                <b-col class="col-md-5">
                    <input :id="row.item.id + '_name'" placeholder="Name" :value="row.item.name" class="form-control">
                </b-col>
                <b-col class="col-md-3">
                    <input :id="row.item.id + '_email'" placeholder="Email" :value="row.item.email" class="form-control">
                </b-col>
                <b-col class="col-md-2">
                    <b-form-select
                        :id="row.item.id + '_permissions'" 
                        :options="roles"
                        class="mb-3 text-black"
                        stacked
                        :value="row.item.permissions"
                    >
                </b-form-select>

                </b-col>
                <b-col>
                    <button class="btn btn-success btn-sm" @click="updateUser(row.item.id)" onclick="return false">Update</button>
                </b-col>          
            </b-row>
          <b-row class="mb-2">
          <b-col class="col-md-2">Password reset link</b-col>
            <b-col><a :href='row.item.link' target="_blank">{{row.item.link}}</a></b-col>
          </b-row>
      </template>
<template v-slot:custom-foot>

            <tr  >
                <td>
                    <input name="User[name]" placeholder="Name" class="form-control">
                </td>
                <td>
                    <input name="User[email]" placeholder="Email" class="form-control">
                </td>
                <td>
                    <b-form-select
                        name="User[permissions]" 
                        :options="roles"
                        class="mb-3 text-black"
                        stacked
                        value="3"
                    >
                </b-form-select>

                </td>
                <td>
                    <button class="btn btn-primary btn-sm">Create User</button>
                </td>
            </tr>
        </template>

</b-table>
<?= Html::endForm() ?>
<?= Html::beginForm(['/admin/userdelete/'], 'post', [ 'id'=>'userDeleteForm', "class"=>"d-none"]) ?>
<input type="hidden" id="delete_User_id" name="User[id]">
<?= Html::endForm() ?>
<?= Html::beginForm(['/admin/user-edit/'], 'post', [ 'id'=>'userEditForm', "class"=>"d-none"]) ?>
<input type="hidden" id="User_id" name="User[id]">
<input type="hidden" id="User_name" name="User[name]">
<input type="hidden" id="User_email" name="User[email]">
<input type="hidden" id="User_permissions" name="User[permissions]">
<?= Html::endForm() ?>
</div>
<script>
users = <?php echo json_encode($users, ENT_QUOTES); ?>;
roles = <?php echo json_encode($roles, ENT_QUOTES); ?>;

new Vue({
  el: '#useradmin-app',
    data() {
        return {
            user_fields: [{
                key: "name",
                label: "Name",
                tdClass: 'col-md-5'
            }, {
                key: "email",
                label: "Email",
                tdClass: 'col-md-3'
            },  {
                key: "permissions",
                label: "Permissions",
                tdClass: 'col-md-2'
            }, {
                key: "info",
                label: ""
            }],
            users: users,
            roles: roles,
        }
    },
    methods: {
    deleteUser(id){
        if(confirm("Delete user?")){
            $("#delete_User_id").val(id);
            $("#userDeleteForm").submit();
        }
    },
    updateUser(id){
        $("#User_id").val(id);
        $("#User_name").val($("#" + id + "_name").val());
        $("#User_email").val($("#" + id + "_email").val());
        $("#User_permissions").val($("#" + id + "_permissions").val());
        $("#userEditForm").submit();
    },
    setAttribute(item, type) {
            return {
                'data-id': item.id,
            }
        },
    }
});
</script>