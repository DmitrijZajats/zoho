<?php

use yii\db\Schema;
use yii\db\Migration;

class m150623_084703_zoho extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable(
            '{{%account}}',
            [
                'account_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'login' => Schema::TYPE_STRING . ' NOT NULL',
                'token' => Schema::TYPE_STRING,
                'update_at' => Schema::TYPE_TIMESTAMP . ' DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'PRIMARY KEY (account_id)'
            ],
            $tableOptions
        );
        $this->createIndex('za_login', 'zoho_account', 'login', true);

        $this->createTable(
            '{{%invoice}}',
            [
                'invoice_id' => Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'remote_id' => Schema::TYPE_STRING . ' NOT NULL',
                'data' => Schema::TYPE_TEXT . ' NOT NULL',
                'data_hash' => Schema::TYPE_STRING . '(32) NOT NULL',
                'update_frequency' => Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL DEFAULT 0',
                'invoice_url' => Schema::TYPE_STRING . '(1024) NOT NULL',
                'update_at' => Schema::TYPE_TIMESTAMP . ' DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'create_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT "0000-00-00 00:00:00"',
                'PRIMARY KEY (invoice_id)'
            ],
            $tableOptions
        );
        $this->createIndex('zi_remote_id', 'zoho_invoice', 'remote_id', true);

        $this->createTable(
            '{{%contact}}',
            [
                'contact_id' => Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'remote_id' => Schema::TYPE_STRING . ' NOT NULL',
                'data' => Schema::TYPE_TEXT . ' NOT NULL',
                'data_hash' => Schema::TYPE_STRING . '(32) NOT NULL',
                'update_frequency' => Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL DEFAULT 0',
                'update_at' => Schema::TYPE_TIMESTAMP . ' DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'create_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT "0000-00-00 00:00:00"',
                'PRIMARY KEY (contact_id)'
            ],
            $tableOptions
        );
        $this->createIndex('zc_remote_id', 'zoho_contact', 'remote_id', true);
    }

    public function down()
    {
        $this->dropTable('zoho_account');
        $this->dropTable('zoho_invoice');
        $this->dropTable('zoho_contact');
    }
}
