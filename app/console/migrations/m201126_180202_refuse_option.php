<?php
use yii\db\Migration;

class m201126_180202_refuse_option extends Migration
{
	public function up()
	{
        try
        {
		    $table = Yii::$app->db->schema->getTableSchema('questionOption');
			if(!isset($table->columns['single']))
				$this->addColumn('questionOption', 'single', 'tinyint(1)');
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            return false;
        }
	}

	public function down()
	{
		echo "m201126_180202_refuse_option does not support migration down.\n";
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