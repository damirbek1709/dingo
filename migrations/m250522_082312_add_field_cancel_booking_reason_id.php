<?php

use yii\db\Migration;

/**
 * Class m250522_082312_add_field_cancel_booking_reason_id
 */
class m250522_082312_add_field_cancel_booking_reason_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'cancel_reason_id', $this->integer(2)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250522_082312_add_field_cancel_booking_reason_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250522_082312_add_field_cancel_booking_reason_id cannot be reverted.\n";

        return false;
    }
    */
}
