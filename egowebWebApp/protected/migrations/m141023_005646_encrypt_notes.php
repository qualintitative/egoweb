<?php
require_once(dirname(__FILE__)."/../extensions/custy.php");


class m141023_005646_encrypt_notes extends CDbMigration
{
    public function up()
    {
        $transaction=$this->getDbConnection()->beginTransaction();

        try
        {
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM notes");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                if( strlen( trim( $row["notes"] ) ) > 0 ){
                    $encrypted = encrypt( $row["notes"] );
                    $this->update( 'notes', array( 'notes'=>$encrypted ), 'id='.$row["id"] );
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
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM notes");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                if( strlen( trim( $row["notes"] ) ) > 0 ){
                    $decrypted = decrypt( $row["notes"] );
                    $this->update( 'notes', array( 'notes'=>$decrypted ), 'id='.$row["id"] );
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
