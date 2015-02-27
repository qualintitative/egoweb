<?php

class m150227_070331_hide_ego_id extends CDbMigration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		$table = Yii::app()->db->schema->getTable('study');
		if($table){
			if(isset($table->columns['started']))
				$this->dropColumn('study', 'started');
			if(isset($table->columns['completed']))
				$this->dropColumn('study', 'completed');
			if(isset($table->columns['adjacencyExpressionId']))
				$this->dropColumn('study', 'adjacencyExpressionId');
			if(!isset($table->columns['hideEgoIdPage']))
				$this->addColumn('study', 'hideEgoIdPage', 'tinyint(1)');
		}
		$transaction->commit();
	}

	public function down()
	{
		echo "m150227_070331_hide_ego_id does not support migration down.\n";
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