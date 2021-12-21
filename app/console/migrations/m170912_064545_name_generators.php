<?php
use yii\db\Migration;

class m170912_064545_name_generators extends Migration
{
	public function up()
	{
        try
        {
            $table = Yii::$app->db->schema->getTableSchema('alters');
            if(!isset($table->columns['nameGenQIds']))
                $this->addColumn('alters', 'nameGenQIds', 'text');
        }
        catch(Exception $e)
        {
            echo "Exception: ".$e->getMessage()."\n";
            return false;
        }
	}

	public function down()
	{
		echo "m170912_064545_name_generators does not support migration down.\n";
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
