<?php

class m140924_052523_add_dates_to_study extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
			$table = Yii::app()->db->schema->getTable('study');
			if($table){
				if(!isset($table->columns['created_date']))
					$this->addColumn('study', 'created_date', 'int');
				if(!isset($table->columns['closed_date']))
					$this->addColumn('study', 'closed_date', 'int');
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
		echo "m140924_052523_add_dates_to_study does not support migration down.\n";
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