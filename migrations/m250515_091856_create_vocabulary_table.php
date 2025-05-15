<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vocabulary}}`.
 */
class m250515_091856_create_vocabulary_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vocabulary}}', [
            'id' => $this->primaryKey(),
            'title'=>$this->string(255)->notNull(),
            'title_en'=>$this->string(255)->notNull(),
            'title_ky'=>$this->string(255)->notNull(),
            'model'=>$this->integer(22)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vocabulary}}');
    }
}
