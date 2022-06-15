<?php

use yii\db\Migration;

/**
 * Class m220614_081048_load_multi_alter
 */
class m220614_081048_load_multi_alter extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if (!isset($table->columns['restrictPrev']))
            $this->addColumn('question', 'restrictPrev', 'TINYINT(1) AFTER `prefillList`');
        if (!isset($table->columns['autocompletePrev']))
            $this->addColumn('question', 'autocompletePrev', 'TINYINT(1) AFTER `restrictPrev`');
        if (!isset($table->columns['prefillPrev']))
            $this->addColumn('question', 'prefillPrev', 'TINYINT(1) AFTER `autocompletePrev`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220614_081048_load_multi_alter cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220614_081048_load_multi_alter cannot be reverted.\n";

        return false;
    }
    */
}
