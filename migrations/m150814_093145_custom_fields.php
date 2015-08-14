<?php

use yii\db\Schema;
use yii\db\Migration;

class m150814_093145_custom_fields extends Migration{
    public function up(){
        $this->addColumn('{{%contact_info}}', 'debiteurnummer', Schema::TYPE_STRING);
    }

    public function down(){
        $this->dropColumn('{{%contact_info}}', 'debiteurnummer');
    }
}
