<?php

class m140917_223213_graph_update extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
			$table = Yii::app()->db->schema->getTable('graphs');
			if($table){
				if(isset($table->columns['name']))
	            	$this->dropColumn('graphs', 'name');
				if(isset($table->columns['json']))
					$this->dropColumn('graphs', 'json');
            }else{
				$this->createTable('grpahs', array(
	                'id' => 'pk',
					'interviewId' => 'integer',
					'expressionId' => 'integer',
	                'nodes' => 'text NOT NULL',
	                'params' => 'text NOT NULL',
				));
            }
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
		echo "m140917_223213_graph_update does not support migration down.\n";
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