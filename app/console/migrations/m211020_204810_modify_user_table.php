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
