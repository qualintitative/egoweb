<?php

use yii\db\Migration;

/**
 * Class m240916_170750_alterlist_name_email
 */
class m240916_170750_alterlist_name_email extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('alterList', 'name', "VARCHAR(500)  NULL DEFAULT ''");
        $this->alterColumn('alterList', 'email', "VARCHAR(500)  NULL DEFAULT ''");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240916_170750_alterlist_name_email cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240916_170750_alterlist_name_email cannot be reverted.\n";

        return false;
    }
    */
}
