<?php

class m150506_232010_matchTable extends CDbMigration
{
	public function up()
	{
        $table = Yii::app()->db->schema->getTable('matchedAlters');
        if(!$table){
            $this->createTable('matchedAlters', array(
                'id' => 'pk',
                'studyId' => 'int(11)',
                'alterId1' => 'int(11)',
                'alterId2' => 'int(11)',
                'matchedName' => 'string NOT NULL',
            ));
        }
	}

	public function down()
	{
		echo "m150506_232010_matchTable does not support migration down.\n";
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
