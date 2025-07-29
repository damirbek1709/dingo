<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback}}`.
 */
class m250729_055135_create_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%feedback}}', [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer(length: 11)->notNull(),
            'general' => $this->integer(length: 2)->null(),
            'cleaning' => $this->integer(length: 2)->null(),
            'location' => $this->integer(length: 2)->null(),
            'room' => $this->integer(length: 2)->null(),
            'meal' => $this->integer(length: 2)->null(),
            'hygien' => $this->integer(length: 2)->null(),
            'price_quality' => $this->integer(length: 2)->null(),
            'service' => $this->integer(length: 2)->null(),
            'wifi' => $this->integer(length: 2)->null(),
            'pos' => $this->string(800)->null(),
            'cons' => $this->string(length: 800)->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%feedback}}');
    }
}
