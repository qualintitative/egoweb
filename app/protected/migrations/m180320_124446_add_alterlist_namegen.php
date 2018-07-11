<?php

class m180320_124446_add_alterlist_namegen extends CDbMigration
{
	public function up()
	{
    $transaction=$this->getDbConnection()->beginTransaction();
    try
    {
        $table = Yii::app()->db->schema->getTable('alterList');
        if(!isset($table->columns['nameGenQIds']))
            $this->addColumn('alterList', 'nameGenQIds', 'text');
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
		echo "m180320_124446_add_alterlist_namegen does not support migration down.\n";
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
