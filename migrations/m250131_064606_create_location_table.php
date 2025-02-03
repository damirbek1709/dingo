<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%location}}`.
 */
class m250131_064606_create_location_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%location}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'title_en' => $this->string(255),
            'title_ky' => $this->string(255),
            'lat'=>$this->double(),
            'lon'=>$this->double(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%location}}');
    }
}
