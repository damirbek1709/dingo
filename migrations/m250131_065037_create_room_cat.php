<?php

use yii\db\Migration;

/**
 * Class m250131_065037_create_room_cat
 */
class m250131_065037_create_room_cat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%room_cat}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'title_en' => $this->string(255),
            'title_ky' => $this->string(255),
            'guest_amount'=>$this->integer(11)->notNull(),
            'similar_room_amount'=>$this->integer(11)->notNull(),
            'area'=>$this->double()->notNull(),
            'bathroom'=>$this->integer(2)->defaultValue(0),
            'balcony'=>$this->integer(2)->defaultValue(0),
            'air_cond'=>$this->integer(2)->defaultValue(0),
            'kitchen'=>$this->integer(2)->defaultValue(0),
            'base_price'=>$this->double()->defaultValue(0),
            'img'=>$this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250131_065037_create_room_cat cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250131_065037_create_room_cat cannot be reverted.\n";

        return false;
    }
    */
}
