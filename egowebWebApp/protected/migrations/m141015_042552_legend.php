<?php

class m141015_042552_legend extends CDbMigration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
		try
		{
			$this->createTable('legend', array(
				'id' => 'pk',
				'studyId' => 'integer NOT NULL',
				'questionId' => 'integer NOT NULL',
				'shape' => 'string NOT NULL',
				'label' => 'string',
				'color' => 'string',
				'size' => 'integer',
				'ordering' => 'integer',
			));
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
		echo "m141015_042552_legend does not support migration down.\n";
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