<?php

use yii\db\Migration;

/**
 * Class m250514_083136_booking_add_field_currency
 */
class m250514_083136_booking_add_field_currency extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'currency', $this->string(22)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250514_083136_booking_add_field_currency cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250514_083136_booking_add_field_currency cannot be reverted.\n";

        return false;
    }
    */
}
