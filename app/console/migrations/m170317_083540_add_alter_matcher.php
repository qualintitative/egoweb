<?php
use yii\db\Migration;

class m170317_083540_add_alter_matcher extends Migration
{
	public function up()
	{
        try
        {
		    $table = Yii::$app->db->schema->getTableSchema('matchedAlters');
			if(!isset($table->columns['userId']))
				$this->addColumn('matchedAlters', 'userId', 'int');
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
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
