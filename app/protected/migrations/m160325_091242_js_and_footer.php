<?php

class m160325_091242_js_and_footer extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
		    $table = Yii::app()->db->schema->getTable('study');
			if(!isset($table->columns['javascript']))
				$this->addColumn('study', 'javascript', 'longtext');
			if(!isset($table->columns['footer']))
				$this->addColumn('study', 'footer', 'longtext');
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
		echo "m160325_091242_js_and_footer does not support migration down.\n";
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