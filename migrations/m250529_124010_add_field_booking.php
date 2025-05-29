<?php

use yii\db\Migration;

/**
 * Class m250529_124010_add_field_booking
 */
class m250529_124010_add_field_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'cancel_date', $this->date()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250529_124010_add_field_booking cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250529_124010_add_field_booking cannot be reverted.\n";

        return false;
    }
    */
}
