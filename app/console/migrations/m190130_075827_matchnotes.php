<?php
use yii\db\Migration;

class m190130_075827_matchnotes extends Migration
{
	public function up()
	{
    $table = Yii::$app->db->schema->getTableSchema('matchedAlters');
    if(!isset($table->columns['notes']))
        $this->addColumn('matchedAlters', 'notes', 'string');
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
