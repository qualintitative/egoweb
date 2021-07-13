<?php
use yii\helpers\Html;
?>
<div id="useradmin-app">
<?= Html::beginForm(['/admin/user'], 'post', [ 'id'=>'addUser']) ?>

<b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="users" :fields="user_fields" striped responsive="sm">

    <template #cell(permissions)="row">
        <b-form-select
            v-model="row.item.permissions"
                name="User[permissions]" 
                :options="roles"
                class="mb-3 text-black"
                stacked
                disabled
                value="3"
            >
        </b-form-select>
    </template>

    <template #cell(details)="row">
        <b-link href="#" @click="deleteUser(row.item.id)"><i class="fas fa-times"></i></b-link>
    </template>
<template v-slot:custom-foot>

            <tr  >
                <td>
                    <input name="User[name]">
                </td>
                <td>
                    <input name="User[email]" >
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
<?= Html::beginForm(['/admin/userdelete/'], 'post', [ 'id'=>'userForm', "class"=>"d-none"]) ?>
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
                tdClass: 'col-md-6'
            }, 'email', 'permissions', {
                key: "details",
                label: ""
            }],
            users: users,
            roles: roles,
        }
    },
    methods: {
    deleteUser(id){
        if(confirm("Delete user?")){
            $("#User_id").val(id);
            $("#userForm").submit();
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