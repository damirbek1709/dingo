<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%oblast}}`.
 */
class m250131_062753_create_oblast_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%oblast}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'title_en' => $this->string(255),
            'title_ky' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%oblast}}');
    }
}
