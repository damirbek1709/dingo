<?php

use yii\db\Migration;

/**
 * Class m250624_092926_add_field_booking
 */
class m250624_092926_add_field_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'payment_type', $this->integer(11)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250624_092926_add_field_booking cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250624_092926_add_field_booking cannot be reverted.\n";

        return false;
    }
    */
}
