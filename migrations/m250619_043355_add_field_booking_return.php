<?php

use yii\db\Migration;

/**
 * Class m250619_043355_add_field_booking_return
 */
class m250619_043355_add_field_booking_return extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'return_status', $this->boolean()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250619_043355_add_field_booking_return cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250619_043355_add_field_booking_return cannot be reverted.\n";

        return false;
    }
    */
}
