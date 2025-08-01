<?php

use yii\db\Migration;

class m250801_100158_create_notification_vocabulary extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notification', 'category', $this->integer(11)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250801_100158_create_notification_vocabulary cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250801_100158_create_notification_vocabulary cannot be reverted.\n";

        return false;
    }
    */
}
