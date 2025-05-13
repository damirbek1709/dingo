<?php

use yii\db\Migration;

/**
 * Class m250513_073526_add_field_booking_table
 */
class m250513_073526_add_field_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'user_id', $this->integer(11)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250513_073526_add_field_booking_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250513_073526_add_field_booking_table cannot be reverted.\n";

        return false;
    }
    */
}
