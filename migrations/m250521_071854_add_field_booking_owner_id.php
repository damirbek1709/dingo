<?php

use yii\db\Migration;

/**
 * Class m250521_071854_add_field_booking_owner_id
 */
class m250521_071854_add_field_booking_owner_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'owner_id', $this->integer(22)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250521_071854_add_field_booking_owner_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250521_071854_add_field_booking_owner_id cannot be reverted.\n";

        return false;
    }
    */
}
