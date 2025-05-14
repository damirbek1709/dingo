<?php

use yii\db\Migration;

/**
 * Class m250514_120853_tariff_add_field_title
 */
class m250514_120853_tariff_add_field_title extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tariff', 'title_en', $this->string(255));
        $this->addColumn('tariff', 'title_ky', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250514_120853_tariff_add_field_title cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250514_120853_tariff_add_field_title cannot be reverted.\n";

        return false;
    }
    */
}
