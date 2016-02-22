<?php

class m140924_014007_add_dates_to_interview extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
			$table = Yii::app()->db->schema->getTable('interview');
			if($table){
				if(!isset($table->columns['start_date']))
	            	$this->addColumn('interview', 'start_date', 'int');
				if(!isset($table->columns['complete_date']))
	            	$this->addColumn('interview', 'complete_date', 'int');
				$transaction->commit();
	        }
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
		echo "m140924_014007_add_dates_to_interview does not support migration down.\n";
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