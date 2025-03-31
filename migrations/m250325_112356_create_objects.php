<?php

use yii\db\Migration;

/**
 * Class m250325_112356_create_objects
 */
class m250325_112356_create_objects extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%objects}}', [
            'id' => $this->primaryKey(),
            'name'=>$this->string(255)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250325_112356_create_objects cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250325_112356_create_objects cannot be reverted.\n";

        return false;
    }
    */
}
