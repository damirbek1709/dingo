<?php

use yii\db\Migration;

/**
 * Class m250709_081637_modify_field_booking_alter
 */
class m250709_081637_modify_field_booking_alter extends Migration
{
    public function safeUp()
    {
        // MySQL & PostgreSQL compatible way
        $this->execute("ALTER TABLE booking ALTER COLUMN return_status SET DEFAULT 1");
    }

    public function safeDown()
    {
        $this->execute("ALTER TABLE booking ALTER COLUMN return_status DROP DEFAULT");
    }
}
