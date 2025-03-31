<?php

use yii\db\Migration;

/**
 * Class m250326_080806_add_field_tariff
 */
class m250326_080806_add_field_tariff extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tariff', 'object_id', $this->integer(11));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250326_080806_add_field_tariff cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250326_080806_add_field_tariff cannot be reverted.\n";

        return false;
    }
    */
}
