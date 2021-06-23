<?php

class m210616_191849_update_user_table extends CDbMigration
{
	public function up()
	{
		$transaction=$this->getDbConnection()->beginTransaction();
        try
        {
			$this->addColumn('user', 'password_reset_token', 'varchar(255)');
			$this->addColumn('user', 'auth_key', 'varchar(255)');
			$this->addColumn('user', 'status', 'tinyint');
			$this->addColumn('user', 'created_at', 'int');
			$this->addColumn('user', 'updated_at', 'int');
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
		echo "m210616_191849_update_user_table does not support migration down.\n";
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