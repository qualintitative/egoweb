<?php

class m141118_014141_add_completed_started_and_status_to_study extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
            $table = Yii::app()->db->schema->getTable('study');
			if(!isset($table->columns['completed']))
                $this->addColumn('study', 'completed', 'int');
            if(!isset($table->columns['started']))
                $this->addColumn('study', 'started', 'int');
            if(!isset($table->columns['status']))
                $this->addColumn('study', 'status', 'int');
            $transaction->commit();
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            $transaction->rollback();
            return false;
        }
	}

	public function down(){
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
            $this->dropColumn('study', 'completed');
            $this->dropColumn('study', 'started');
            $this->dropColumn('study', 'status');
            $transaction->commit();
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            $transaction->rollback();
            return false;
        }
    }
}
