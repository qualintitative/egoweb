<?php

use yii\db\Migration;

/**
 * Class m220822_182007_networkGraphs
 */
class m220822_182007_networkGraphs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if (isset($table->columns['networkNColorQId']))
            $this->dropColumn('question', 'networkNColorQId');
        if (isset($table->columns['networkNSizeQId']))
            $this->dropColumn('question', 'networkNSizeQId');
        if (isset($table->columns['networkEColorQId']))
            $this->dropColumn('question', 'networkEColorQId');
        if (isset($table->columns['networkESizeQId']))
            $this->dropColumn('question', 'networkESizeQId');
        if (!isset($table->columns['networkGraphs']))
            $this->addColumn('question', 'networkGraphs', 'MEDIUMTEXT AFTER `networkParams`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220822_182007_networkGraphs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220822_182007_networkGraphs cannot be reverted.\n";

        return false;
    }
    */
}
