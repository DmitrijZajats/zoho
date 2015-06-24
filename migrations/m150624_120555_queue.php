<?php

use yii\db\Schema;
use yii\db\Migration;

class m150624_120555_queue extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%invoice_queue}}',
            [
                'remote_id' => Schema::TYPE_STRING . ' NOT NULL',
                'PRIMARY KEY (remote_id)'
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%contact_queue}}',
            [
                'remote_id' => Schema::TYPE_STRING . ' NOT NULL',
                'PRIMARY KEY (remote_id)'
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable('{{%invoice_queue}}');
        $this->dropTable('{{%contact_queue}}');
    }
}
