<?php

use yii\db\Migration;

/**
 * Class m250408_062521_create_room_comfort
 */
class m250408_062521_create_room_comfort extends Migration
{
    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%room_comfort}}', [
            'id' => $this->primaryKey(),
            'title'=>$this->string(255),
            'title_en'=>$this->string(255),
            'title_ky'=>$this->string(255),
            'category_id'=>$this->integer(5)
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
        echo "m250408_062521_create_room_comfort cannot be reverted.\n";

        return false;
    }
    */
}
