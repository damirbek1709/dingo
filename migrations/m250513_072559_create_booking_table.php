<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking}}`.
 */
class m250513_072559_create_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%booking}}', [
            'id' => $this->primaryKey(),
            'object_id'=>$this->integer(11)->notNull(),
            'room_id'=>$this->integer(length: 11)->notNull(),
            'tariff_id'=>$this->string(11)->notNull(),
            'sum'=>$this->double()->notNull(),
            'guest_email'=>$this->string(255),
            'guest_phone'=>$this->string(255),
            'guest_name'=>$this->string(255),
            'speacial_comment'=>$this->string(255),
            'date_from'=>$this->date()->notNull(),
            'date_to'=>$this->date()->notNull(),
            'status'=>$this->integer(11)->notNull(),
            'other_guests'=>$this->string(500)->null(),
            'transaction_number'=>$this->string(255)->null(),
            'cancellation_type'=>$this->integer(11)->null(),
            'cancellation_penalty_sum'=>$this->double(11)->null()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%booking}}');
    }
}
