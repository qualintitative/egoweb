<?php

use yii\db\Migration;

/**
 * Class m220621_090923_hide_name_gen_q
 */
class m220621_090923_hide_name_gen_q extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if (!isset($table->columns['hideNameGenQ']))
            $this->addColumn('question', 'hideNameGenQ', 'TINYINT(1) AFTER `prefillPrev`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220621_090923_hide_name_gen_q cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220621_090923_hide_name_gen_q cannot be reverted.\n";

        return false;
    }
    */
}
