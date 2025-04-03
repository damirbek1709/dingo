<?php

use yii\db\Migration;

/**
 * Class m250403_045059_add_field_id
 */
class m250403_045059_add_field_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('objects', 'id', $this->primaryKey());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250403_045059_add_field_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250403_045059_add_field_id cannot be reverted.\n";

        return false;
    }
    */
}
