<?php

use yii\db\Migration;

/**
 * Class m250728_072404_create_payment_page
 */
class m250728_072404_create_payment_page extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%transaction}}', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer(length: 11)->notNull(),
            'sum' => $this->double()->notNull(),
            'status' => $this->integer(11)->notNull(),
            'date'=>$this->dateTime()->notNull()->defaultValue(date('Y-m-d H:i:s')),
        ]);
        $this->db->createCommand("ALTER TABLE {{%transaction}} AUTO_INCREMENT = 1000000")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%booking}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250728_072404_create_payment_page cannot be reverted.\n";

        return false;
    }
    */
}
