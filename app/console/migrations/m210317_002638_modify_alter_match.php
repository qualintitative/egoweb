<?php
use yii\db\Migration;

class m210317_002638_modify_alter_match extends Migration
{
	public function up()
	{
        try
        {
			$table = Yii::$app->db->schema->getTableSchema('alters');
			if (isset($table->columns['alterListId']))
            	$this->alterColumn('alters', 'alterListId', 'varchar(500)');
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            return false;
        }
	}

	public function down()
	{
		echo "m210317_002638_modify_alter_match does not support migration down.\n";
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