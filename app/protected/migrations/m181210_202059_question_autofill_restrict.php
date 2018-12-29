<?php

class m181210_202059_question_autofill_restrict extends CDbMigration
{
	public function up()
	{
    $transaction=$this->getDbConnection()->beginTransaction();
    try
    {
        $table = Yii::app()->db->schema->getTable('question');
        if(!isset($table->columns['restrictList']))
            $this->addColumn('question', 'restrictList', 'boolean');
        if(!isset($table->columns['autocompleteList']))
            $this->addColumn('question', 'autocompleteList', 'boolean');
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
		echo "m181210_202059_question_autofill_restrict does not support migration down.\n";
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
