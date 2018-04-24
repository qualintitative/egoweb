<?php

class m171010_091427_add_var_prompt_q_id extends CDbMigration
{
    public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
            $table = Yii::app()->db->schema->getTable('alterPrompt');
            if(!isset($table->columns['questionId']))
                $this->addColumn('alterPrompt', 'questionId', 'int');
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
		echo "m171010_091427_add_var_prompt_q_id does not support migration down.\n";
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
