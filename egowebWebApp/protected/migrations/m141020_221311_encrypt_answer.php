<?php
require_once(dirname(__FILE__)."/../extensions/custy.php");

class m141020_221311_encrypt_answer extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();

        try
        {
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM answer");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                $changeArray = array();

                if( strlen(trim($row["value"])) > 0 ){
                    $changeArray['value'] = encrypt($row["value"]);
                }

                if( strlen(trim($row["otherSpecifyText"])) > 0 ){
                    $changeArray['otherSpecifyText'] = encrypt($row["otherSpecifyText"]);
                }

                if( count($changeArray) > 0 ){
                    $this->update( 'answer', $changeArray, 'id='.$row["id"] );
                }
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
        $transaction=$this->getDbConnection()->beginTransaction();

        try
        {
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM answer");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                $changeArray = array();

                if( strlen(trim($row["value"])) > 0 ){
                    $changeArray['value'] = decrypt($row["value"]);
                }

                if( strlen(trim($row["otherSpecifyText"])) > 0 ){
                    $changeArray['otherSpecifyText'] = decrypt($row["otherSpecifyText"]);
                }

                if( count($changeArray) > 0 ){
                    $this->update( 'answer', $changeArray, 'id='.$row["id"] );
                }
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
