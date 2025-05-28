<?php

use yii\db\Migration;

/**
 * Class m250528_062744_add_field_user
 */
class m250528_062744_add_field_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'phone', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250528_062744_add_field_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250528_062744_add_field_user cannot be reverted.\n";

        return false;
    }
    */
}
