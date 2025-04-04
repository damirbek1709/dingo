<?php

use yii\db\Migration;

/**
 * Class m250404_065701_add_fields_tariff
 */
class m250404_065701_add_fields_tariff extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tariff', 'penalty_sum', $this->double()->null());
        $this->addColumn('tariff', 'penalty_days', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250404_065701_add_fields_tariff cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250404_065701_add_fields_tariff cannot be reverted.\n";

        return false;
    }
    */
}
