<?php
require_once(dirname(__FILE__)."/../extensions/custy.php");

class m141023_003707_encrypt_alters extends CDbMigration
{
	public function up()
	{
        $transaction=$this->getDbConnection()->beginTransaction();

        try
        {
            $cmd = $this->getDbConnection()->createCommand( "SELECT * FROM alters");
            $rows = $cmd->queryAll();

            foreach( $rows as $row ){
                if( strlen(trim($row["name"])) > 0 ){
                    $encrypted = encrypt($row["name"]);
                    $this->update( 'alters', array( 'name'=>$encrypted ), 'id='.$row["id"] );
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
		echo "m141023_003707_encrypt_alters does not support migration down.\n";
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
