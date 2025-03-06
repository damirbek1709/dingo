<?php

use yii\db\Migration;

/**
 * Class m250304_075319_extend_room_cat
 */
class m250304_075319_extend_room_cat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('room_cat', 'type_id', $this->integer(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250304_075319_extend_room_cat cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250304_075319_extend_room_cat cannot be reverted.\n";

        return false;
    }
    */
}
