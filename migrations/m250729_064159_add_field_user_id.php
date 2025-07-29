<?php

use yii\db\Migration;

/**
 * Class m250729_064159_add_field_user_id
 */
class m250729_064159_add_field_user_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('feedback', 'user_id', $this->integer(11)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250729_064159_add_field_user_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250729_064159_add_field_user_id cannot be reverted.\n";

        return false;
    }
    */
}
