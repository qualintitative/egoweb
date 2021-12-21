<?php
use yii\db\Migration;

class m201027_223018_alter_order extends Migration
{
	public function up()
	{
        try
        {
            $this->alterColumn('alters', 'ordering', 'varchar(500)');
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            return false;
        }
	}

	public function down()
	{
		echo "m201027_223018_alter_order does not support migration down.\n";
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