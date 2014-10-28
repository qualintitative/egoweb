<?php
require_once(dirname(__FILE__)."/../extensions/custy.php");

class m141021_013819_encrypt_questionOption extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();

        try
        {
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM questionOption");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                if( strlen(trim($row["name"])) > 0 ){
                    $encrypted = encrypt($row["name"]);
                    $this->update( 'questionOption', array( 'name'=>$encrypted ), 'id='.$row["id"] );
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
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM questionOption");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                if( strlen(trim($row["name"])) > 0 ){
                    $decrypted = decrypt($row["name"]);
                    $this->update( 'questionOption', array( 'name'=>$decrypted ), 'id='.$row["id"] );
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
