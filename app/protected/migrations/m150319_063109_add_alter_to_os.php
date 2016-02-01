<?php

class m150319_063109_add_alter_to_os extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
		    $table = Yii::app()->db->schema->getTable('otherSpecify');
			if(!isset($table->columns['alterId']))
				$this->addColumn('otherSpecify', 'alterId', 'int(11)');
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
		echo "m150319_063109_add_alter_to_os does not support migration down.\n";
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