<?php
use yii\helpers\Html;
?>
<div id="useradmin-app">
<?= Html::beginForm(['/admin/user'], 'post', [ 'id'=>'addUser']) ?>

<b-table class="options" head-variant="dark" :tbody-tr-attr="setAttribute" :items="users" :fields="user_fields" striped responsive="sm">

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
    setAttribute(item, type) {
            return {
                'data-id': item.id,
            }
        },
    }
});
</script>