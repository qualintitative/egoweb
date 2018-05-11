<?php

class m180413_082300_server_table extends CDbMigration
{
	public function up()
	{
    $table = Yii::app()->db->schema->getTable('server');
    if(!$table){
        $this->createTable('server', array(
            'id' => 'pk',
            'userId' => 'int(11)',
            'address' => 'string NOT NULL',
            'username' => 'string NOT NULL',
            'password' => 'string NOT NULL',
        ));
    }
	}

	public function down()
	{
		echo "m180413_082300_server_table does not support migration down.\n";
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
