<?php
use yii\db\Migration;

class m181214_010547_prefill_list extends Migration
{
	public function up()
	{
    try
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if(!isset($table->columns['prefillList']))
            $this->addColumn('question', 'prefillList', 'boolean');
    }
    catch(Exception $e)
    {
        echo "Exception: ".$e->getMessage()."\n";
        return false;
    }
	}

	public function down()
	{
		echo "m181214_010547_prefill_list does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
