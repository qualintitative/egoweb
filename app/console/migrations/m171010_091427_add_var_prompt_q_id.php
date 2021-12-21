<?php
use yii\db\Migration;

class m171010_091427_add_var_prompt_q_id extends Migration
{
    public function up()
	{
        try
        {
            $table = Yii::$app->db->schema->getTableSchema('alterPrompt');
            if(!isset($table->columns['questionId']))
                $this->addColumn('alterPrompt', 'questionId', 'int');
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            return false;
        }
	}

	public function down()
	{
		echo "m171010_091427_add_var_prompt_q_id does not support migration down.\n";
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
