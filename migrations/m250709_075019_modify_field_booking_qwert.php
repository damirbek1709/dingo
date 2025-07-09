<?php

use yii\db\Migration;

/**
 * Class m250709_075019_modify_field_booking_qwert
 */
class m250709_075019_modify_field_booking_qwert extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('booking', 'return_status', $this->integer()->defaultValue(1));
    }

    public function safeDown()
    {
        $this->alterColumn('booking', 'return_status', $this->integer()->defaultValue(null));
    }
}
