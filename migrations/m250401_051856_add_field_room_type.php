<?php

use yii\db\Migration;

/**
 * Class m250401_051856_add_field_room_type
 */
class m250401_051856_add_field_room_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('room_type', 'room_amount', $this->integer(11));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250401_051856_add_field_room_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250401_051856_add_field_room_type cannot be reverted.\n";

        return false;
    }
    */
}
