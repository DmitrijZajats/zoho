<?php

use yii\db\Schema;
use yii\db\Migration;

class m150630_095745_split2columns extends Migration
{
    public function up(){
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable(
            '{{%invoice_header}}',
            [
                'header_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'local_invoice_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL',
                'invoice_id' => Schema::TYPE_STRING,
                'invoice_number' => Schema::TYPE_STRING,
                'date' => Schema::TYPE_DATE,
                'status' => Schema::TYPE_STRING,
                'payment_terms' => Schema::TYPE_INTEGER,
                'payment_terms_label' => Schema::TYPE_STRING,
                'due_date' => Schema::TYPE_DATE,
                'payment_expected_date' => Schema::TYPE_DATE,
                'reference_number' => Schema::TYPE_STRING,
                'customer_id' => Schema::TYPE_STRING,
                'customer_name' => Schema::TYPE_STRING,
                'sub_total' => Schema::TYPE_FLOAT,
                'tax_total' => Schema::TYPE_FLOAT,
                'total' => Schema::TYPE_FLOAT,
                'notes' => Schema::TYPE_TEXT,
                'PRIMARY KEY (header_id)'
            ],
            $tableOptions
        );

        $this->addForeignKey('ih_fk_local_invoice', '{{%invoice_header}}', 'local_invoice_id', '{{%invoice}}', 'invoice_id', 'CASCADE', 'CASCADE');

        $this->createTable(
            '{{%invoice_lines}}',
            [
                'line_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'local_invoice_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL',
                'invoice_id' => Schema::TYPE_STRING,
                'line_item_id' => Schema::TYPE_STRING,
                'item_id' => Schema::TYPE_STRING,
                'name' => Schema::TYPE_STRING,
                'description' => Schema::TYPE_TEXT,
                'rate' => Schema::TYPE_FLOAT,
                'quantity' => Schema::TYPE_FLOAT,
                'discount_amount' => Schema::TYPE_FLOAT,
                'item_total' => Schema::TYPE_FLOAT,
                'PRIMARY KEY (line_id)'
            ],
            $tableOptions
        );

        $this->addForeignKey('il_fk_local_invoice', '{{%invoice_lines}}', 'local_invoice_id', '{{%invoice}}', 'invoice_id', 'CASCADE', 'CASCADE');

        $this->dropColumn('{{%invoice}}', 'data');
    }

    public function down()
    {
        $this->dropTable('zoho_invoice_header');
        $this->dropTable('zoho_invoice_lines');
    }
}
