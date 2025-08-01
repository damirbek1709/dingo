<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification}}`.
 */
class m250801_075320_create_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification}}', [
            'id' => $this->primaryKey(),
            'type' => $this->integer(length: 2)->notNull(),
            'title' => $this->string(255)->notNull(),
            'text' => $this->string(500)->notNull(),
            'status' => $this->integer(11)->notNull()->defaultValue(0),
            'date'=>$this->dateTime()->notNull()->defaultValue(date('Y-m-d H:i:s')),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%notification}}');
    }
}
