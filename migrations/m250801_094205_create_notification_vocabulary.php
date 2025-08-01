<?php

use yii\db\Migration;

class m250801_094205_create_notification_vocabulary extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification_list}}', [
            'id' => $this->primaryKey(),
            'category' => $this->integer(length: 2)->notNull(),
            'title' => $this->string(255)->notNull(),
            'text' => $this->string(500)->notNull(),

            'title_en' => $this->string(255)->notNull(),
            'text_en' => $this->string(500)->notNull(),

            'title_ky' => $this->string(255)->notNull(),
            'text_ky' => $this->string(500)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250801_094205_create_notification_vocabulary cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250801_094205_create_notification_vocabulary cannot be reverted.\n";

        return false;
    }
    */
}
