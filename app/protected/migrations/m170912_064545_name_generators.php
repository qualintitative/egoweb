<?php

class m170912_064545_name_generators extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
            $table = Yii::app()->db->schema->getTable('alters');
            if(!isset($table->columns['nameGenQIds']))
                $this->addColumn('alters', 'nameGenQIds', 'text');
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
		echo "m170912_064545_name_generators does not support migration down.\n";
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
