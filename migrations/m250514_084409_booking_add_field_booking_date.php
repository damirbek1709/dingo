<?php

use yii\db\Migration;

/**
 * Class m250514_084409_booking_add_field_booking_date
 */
class m250514_084409_booking_add_field_booking_date extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'created_at', $this->date());
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250514_084409_booking_add_field_booking_date cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250514_084409_booking_add_field_booking_date cannot be reverted.\n";

        return false;
    }
    */
}
