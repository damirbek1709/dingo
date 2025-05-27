<?php

use yii\db\Migration;

/**
 * Class m250527_095747_add_field_user
 */
class m250527_095747_add_field_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'name', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250527_095747_add_field_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250527_095747_add_field_user cannot be reverted.\n";

        return false;
    }
    */
}
