<?php

use yii\db\Migration;

/**
 * Class m211020_204810_modify_user_table
 */
class m211020_204810_modify_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('user');
        
        if(!isset($table->columns['password_reset_token']))
            $this->addColumn('user', 'password_reset_token', 'varchar(255)');

        if(!isset($table->columns['auth_key']))
            $this->addColumn('user', 'auth_key', 'varchar(255)');

        if(!isset($table->columns['status']))
            $this->addColumn('user', 'status', 'tinyint');
        
        if(!isset($table->columns['created_at']))
            $this->addColumn('user', 'created_at', 'int');

        if(!isset($table->columns['updated_at']))
            $this->addColumn('user', 'updated_at', 'int');

        if (isset($table->columns['lastActivity']))
            $this->dropColumn('user', 'lastActivity');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211020_204810_modify_user_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211020_204810_modify_user_table cannot be reverted.\n";

        return false;
    }
    */
}
