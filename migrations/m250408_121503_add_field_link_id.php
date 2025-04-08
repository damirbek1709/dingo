<?php

use yii\db\Migration;

/**
 * Class m250408_121503_add_field_link_id
 */
class m250408_121503_add_field_link_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250408_121503_add_field_link_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250408_121503_add_field_link_id cannot be reverted.\n";

        return false;
    }
    */
}
