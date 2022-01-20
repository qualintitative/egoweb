<?php

use yii\db\Migration;

/**
 * Class m220119_221016_answer_timestamp
 */
class m220119_221016_answer_timestamp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('answer');
        if (!isset($table->columns['timestamp']))
            $this->addColumn('answer', 'timestamp', 'int');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220119_221016_answer_timestamp cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220119_221016_answer_timestamp cannot be reverted.\n";

        return false;
    }
    */
}
