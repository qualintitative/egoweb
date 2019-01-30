<?php

class m190130_075827_matchnotes extends CDbMigration
{
	public function up()
	{
    $transaction=$this->getDbConnection()->beginTransaction();
    $table = Yii::app()->db->schema->getTable('matchedAlters');
    if(!isset($table->columns['notes']))
        $this->addColumn('matchedAlters', 'notes', 'string');
    $transaction->commit();
	}

	public function down()
	{
		echo "m190130_075827_matchnotes does not support migration down.\n";
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
