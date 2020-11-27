<?php

class m201126_180202_refuse_option extends CDbMigration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
        try
        {
		    $table = Yii::app()->db->schema->getTable('questionOption');
			if(!isset($table->columns['single']))
				$this->addColumn('questionOption', 'single', 'tinyint(1)');
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
		echo "m201126_180202_refuse_option does not support migration down.\n";
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