<?php

use yii\db\Migration;

/**
 * Class m220502_091707_setall
 */
class m220502_091707_setall extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if (!isset($table->columns['setAllText']))
            $this->addColumn('question', 'setAllText', 'VARCHAR(32) AFTER `refuseText`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220502_091707_setall cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220502_091707_setall cannot be reverted.\n";

        return false;
    }
    */
}
