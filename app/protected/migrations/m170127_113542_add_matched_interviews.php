<?php

class m170127_113542_add_matched_interviews extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
		    $table = Yii::app()->db->schema->getTable('matchedAlters');
			if(!isset($table->columns['interviewId1']))
				$this->addColumn('matchedAlters', 'interviewId1', 'int');
			if(!isset($table->columns['interviewId2']))
				$this->addColumn('matchedAlters', 'interviewId2', 'int');
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
		echo "m170127_113542_add_matched_interviews does not support migration down.\n";
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