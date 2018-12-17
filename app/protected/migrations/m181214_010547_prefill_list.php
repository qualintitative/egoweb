<?php

class m181214_010547_prefill_list extends CDbMigration
{
	public function up()
	{
    $transaction=$this->getDbConnection()->beginTransaction();
    try
    {
        $table = Yii::app()->db->schema->getTable('question');
        if(!isset($table->columns['prefillList']))
            $this->addColumn('question', 'prefillList', 'boolean');
        $transaction->commit();
    }
    catch(Exception $e)
    {
        echo "Exception: ".$e->getMessage()."\n";
        $transaction->rollback();
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
