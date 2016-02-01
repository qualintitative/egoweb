<?php

class m160118_100231_image_longtext extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
            $this->alterColumn('study', 'introduction', 'longtext');
            $this->alterColumn('study', 'egoIdPrompt', 'longtext');
            $this->alterColumn('study', 'alterPrompt', 'longtext');
            $this->alterColumn('study', 'conclusion', 'longtext');
            $this->alterColumn('question', 'preface', 'longtext');
            $this->alterColumn('question', 'prompt', 'longtext');
            $this->alterColumn('question', 'citation', 'longtext');
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
		echo "m160118_100231_image_longtext does not support migration down.\n";
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
