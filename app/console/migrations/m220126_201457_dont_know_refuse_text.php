<?php

use yii\db\Migration;

/**
 * Class m220126_201457_dont_know_refuse_text
 */
class m220126_201457_dont_know_refuse_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if (!isset($table->columns['dontKnowText']))
            $this->addColumn('question', 'dontKnowText', 'VARCHAR(32) AFTER `dontKnowButton`');
        if (!isset($table->columns['refuseText']))
            $this->addColumn('question', 'refuseText', 'VARCHAR(32) AFTER `refuseButton`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220126_201457_dont_know_refuse_text cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220126_201457_dont_know_refuse_text cannot be reverted.\n";

        return false;
    }
    */
}
