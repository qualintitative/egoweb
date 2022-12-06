<?php

use yii\db\Migration;

/**
 * Class m221206_194240_add_disabled_study_text
 */
class m221206_194240_add_disabled_study_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('study');
        if (!isset($table->columns['disabled']))
            $this->addColumn('study', 'disabled', 'longtext NULL AFTER `header`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221206_194240_add_disabled_study_text cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221206_194240_add_disabled_study_text cannot be reverted.\n";

        return false;
    }
    */
}
