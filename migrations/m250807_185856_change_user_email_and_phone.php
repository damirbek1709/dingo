<?php

use yii\db\Migration;

class m250807_185856_change_user_email_and_phone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'email', $this->string(length: 255)->null());
    }

    public function safeDown()
    {
        // Revert back to INT if needed
         $this->alterColumn('user', 'email', $this->string(length: 255)->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250807_185856_change_user_email_and_phone cannot be reverted.\n";

        return false;
    }
    */
}
