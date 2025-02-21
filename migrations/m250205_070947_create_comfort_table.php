<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%comfort}}`.
 */
class m250205_070947_create_comfort_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%comfort}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'title_en' => $this->string(255),
            'title_ky' => $this->string(255),
            'category_id'=>$this->integer(11)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%comfort}}');
    }
}
