<?php

class m150314_043742_otherSpecUpdate extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();
        try
        {
		    $table = Yii::app()->db->schema->getTable('questionOption');
            $this->createTable('otherSpecify', array(
                'id' => 'pk',
                'optionId' => 'int(11)',
                'interviewId' => 'int(11)',
                'value' => 'string NOT NULL',
            ));
			if(!isset($table->columns['otherSpecify']))
				$this->addColumn('questionOption', 'otherSpecify', 'tinyint(1)');
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
		echo "m150314_043742_otherSpecUpdate does not support migration down.\n";
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