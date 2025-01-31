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
