<?php
use yii\db\Migration;

class m180320_124446_add_alterlist_namegen extends Migration
{
	public function up()
	{
    try
    {
        $table = Yii::$app->db->schema->getTableSchema('alterList');
        if(!isset($table->columns['nameGenQIds']))
            $this->addColumn('alterList', 'nameGenQIds', 'text');
    }
    catch(Exception $e)
    {
        echo "Exception: ".$e->getMessage()."\n";
        return false;
    }
	}

	public function down()
	{
		echo "m180320_124446_add_alterlist_namegen does not support migration down.\n";
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
