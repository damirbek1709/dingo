<?php

use yii\db\Migration;

/**
 * Class m250709_082824_modify_field_booking_qwert
 */
class m250709_082824_modify_field_booking_qwert extends Migration
{
    public function safeUp()
    {
        // MySQL & PostgreSQL compatible way
        $this->execute("ALTER TABLE booking ALTER COLUMN return_status SET DEFAULT 0");
    }

    public function safeDown()
    {
        $this->execute("ALTER TABLE booking ALTER COLUMN return_status DROP DEFAULT");
    }
}
