<?php
use yii\db\Migration;

class m181210_202059_question_autofill_restrict extends Migration
{
	public function up()
	{
    try
    {
        $table = Yii::$app->db->schema->getTableSchema('question');
        if(!isset($table->columns['restrictList']))
            $this->addColumn('question', 'restrictList', 'boolean');
        if(!isset($table->columns['autocompleteList']))
            $this->addColumn('question', 'autocompleteList', 'boolean');
    }
    catch(Exception $e)
    {
        echo "Exception: ".$e->getMessage()."\n";
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
