<?php

use yii\db\Migration;

/**
 * Class m250717_093949_create_table_favorite
 */
class m250717_093949_create_table_favorite extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%favorite}}', [
            'id' => $this->primaryKey(),
            'object_id'=>$this->integer(11)->notNull(),
            'user_id'=>$this->integer(11)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250717_093949_create_table_favorite cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250717_093949_create_table_favorite cannot be reverted.\n";

        return false;
    }
    */
}
