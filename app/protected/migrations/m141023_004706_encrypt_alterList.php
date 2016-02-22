<?php
require_once(dirname(__FILE__)."/../extensions/custy.php");


class m141023_004706_encrypt_alterList extends CDbMigration
{
    public function up()
    {
        $transaction=$this->getDbConnection()->beginTransaction();

        try
        {
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM alterList");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                $changeArray = array();

                if( strlen(trim($row["name"])) > 0 ){
                    $changeArray['name'] = encrypt($row["name"]);
                }

                if( strlen(trim($row["email"])) > 0 ){
                    $changeArray['email'] = encrypt($row["email"]);
                }

                if( count($changeArray) > 0 ){
                    $this->update( 'alterList', $changeArray, 'id='.$row["id"] );
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
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM alterList");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                $changeArray = array();

                if( strlen(trim($row["name"])) > 0 ){
                    $changeArray['name'] = decrypt($row["name"]);
                }

                if( strlen(trim($row["email"])) > 0 ){
                    $changeArray['email'] = decrypt($row["email"]);
                }

                if( count($changeArray) > 0 ){
                    $this->update( 'alterList', $changeArray, 'id='.$row["id"] );
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
