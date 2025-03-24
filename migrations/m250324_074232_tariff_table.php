<?php

use yii\db\Migration;

/**
 * Class m250324_074232_tariff_table
 */
class m250324_074232_tariff_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tariff}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'payment_on_book' => $this->boolean()->Null(),
            'payment_on_reception' => $this->boolean()->Null(),
            'cancellation' => $this->integer(1)->notNull(),
            'meal_type' => $this->integer(1)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250324_074232_tariff_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250324_074232_tariff_table cannot be reverted.\n";

        return false;
    }
    */
}
