<?php

class m170317_083540_add_alter_matcher extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
		    $table = Yii::app()->db->schema->getTable('matchedAlters');
			if(!isset($table->columns['userId']))
				$this->addColumn('matchedAlters', 'userId', 'int');
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
		echo "m170317_083540_add_alter_matcher does not support migration down.\n";
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
