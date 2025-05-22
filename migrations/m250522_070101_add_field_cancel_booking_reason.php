<?php

use yii\db\Migration;

/**
 * Class m250522_070101_add_field_cancel_booking_reason
 */
class m250522_070101_add_field_cancel_booking_reason extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'cancel_reason', $this->string(500)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250522_070101_add_field_cancel_booking_reason cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250522_070101_add_field_cancel_booking_reason cannot be reverted.\n";

        return false;
    }
    */
}
