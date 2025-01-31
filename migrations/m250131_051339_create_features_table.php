<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%features}}`.
 */
class m250131_051339_create_features_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%features}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'title_en' => $this->string(255),
            'title_ky' => $this->string(255),
            'img' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%features}}');
    }
}
