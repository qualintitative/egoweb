<?php

class m150202_163202_add_userId_study extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
            $table = Yii::app()->db->schema->getTable('study');
			if(!isset($table->columns['userId']))
                $this->addColumn('study', 'userId', 'int');
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
            $this->dropColumn('study', 'userId');
            $transaction->commit();
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            $transaction->rollback();
            return false;
        }
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
