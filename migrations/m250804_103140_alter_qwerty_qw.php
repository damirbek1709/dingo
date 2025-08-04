<?php

use yii\db\Migration;

class m250804_103140_alter_qwerty_qw extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Example: changing `user_id` column in `your_table` from INT to STRING
        $this->alterColumn('notification', 'model_id', $this->integer(11)->null());
    }

    public function safeDown()
    {
        // Revert back to INT if needed
        $this->alterColumn('notification', 'model_id', $this->integer(11)->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250804_103140_alter_qwerty_qw cannot be reverted.\n";

        return false;
    }
    */
}
