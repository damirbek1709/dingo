<?php

use yii\db\Migration;

class m250801_100937_create_notification_languages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notification', 'title_en', $this->string(length: 255)->notNull());
        $this->addColumn('notification', 'title_ky', $this->string(length: 255)->notNull());
        $this->addColumn('notification', 'text_en', $this->string(length: 255)->notNull());
        $this->addColumn('notification', 'text_ky', $this->string(length: 255)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250801_100937_create_notification_languages cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250801_100937_create_notification_languages cannot be reverted.\n";

        return false;
    }
    */
}
