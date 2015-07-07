<?php

use yii\db\Schema;
use yii\db\Migration;

class m150707_073305_contacts extends Migration{
    public function up(){
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable(
            '{{%contact_info}}',
            [
                'info_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'local_contact_id' =>  Schema::TYPE_INTEGER . '(10) UNSIGNED NOT NULL',
                'contact_id' => Schema::TYPE_STRING,
                'contact_name' => Schema::TYPE_STRING,
                'company_name' => Schema::TYPE_STRING,
                'contact_person_id' => Schema::TYPE_STRING,
                'salutation' => Schema::TYPE_STRING,
                'first_name' => Schema::TYPE_STRING,
                'last_name' => Schema::TYPE_STRING,
                'email' => Schema::TYPE_STRING,
                'PRIMARY KEY (info_id)'
            ],
            $tableOptions
        );

        $this->addForeignKey('ci_fk_local_contact_id', '{{%contact_info}}', 'local_contact_id', '{{%contact}}', 'contact_id', 'CASCADE', 'CASCADE');

        $this->dropColumn('{{%contact}}', 'data');
    }

    public function down(){
        $this->dropTable('zoho_contact_info');
    }
}
