<?php

use yii\db\Migration;

/**
 * Class m250624_101853_modify_field_booking
 */
class m250624_101853_modify_field_booking extends Migration
{
    public function safeUp()
    {
        // Example: changing `user_id` column in `your_table` from INT to STRING
        $this->alterColumn('booking', 'payment_type', $this->string(255));
    }

    public function safeDown()
    {
        // Revert back to INT if needed
        $this->alterColumn('booking', 'payment_type', $this->integer());
    }
}
