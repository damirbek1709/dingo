<?php

use yii\db\Migration;

/**
 * Class m250530_064111_add_field_user
 */
class m250530_064111_add_field_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'fee_percent', $this->double()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250530_064111_add_field_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250530_064111_add_field_user cannot be reverted.\n";

        return false;
    }
    */
}
