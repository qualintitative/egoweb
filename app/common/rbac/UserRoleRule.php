<?php
namespace common\rbac;
 
use yii\rbac\Rule;
use app\models\User;
 
/**
 * Checks if role matches user's own role
 */
class UserRoleRule extends Rule
{
    public $name = 'isUserRole';
 
    /**
     * Usage: if (Yii::$app->user->can('manageTrip')) {...}
     * 
     * @param string|integer $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        // check the role from table User         
        if(isset(\Yii::$app->user->identity->role)) {
            $role = \Yii::$app->user->identity->role;
        } else {
             return false;
        }
 
        if ($item->name === 'admin') {
            return $role == User::ROLE_ADMIN;
        } elseif ($item->name === 'editor') {
            // editor is a child of admin
            return $role == User::ROLE_ADMIN || 
                   $role == User::ROLE_EDITOR;
        } elseif ($item->name === 'author') {
            // author is a child of editor and admin
            return $role == User::ROLE_ADMIN  || 
                   $role == User::ROLE_EDITOR || 
                   $role == User::ROLE_AUTHOR;
       } elseif ($item->name === 'poweruser') {
            // poweruser is a child of author, editor and admin
            return $role == User::ROLE_ADMIN  || 
                   $role == User::ROLE_EDITOR || 
                   $role == User::ROLE_AUTHOR || 
                   $role == User::ROLE_POWERUSER;
        } elseif ($item->name === 'registered') {
            // registered is a child of author, editor, and admin.
            // if we have no role defined, this is also the default role.
            return $role == User::ROLE_ADMIN  || 
                   $role == User::ROLE_EDITOR || 
                   $role == User::ROLE_AUTHOR || 
                   $role == User::ROLE_REGISTERED || 
                   $role == NULL; 
        } else {
            return false;
        }
    }
}