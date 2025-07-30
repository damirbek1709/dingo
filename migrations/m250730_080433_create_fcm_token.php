<?php

use yii\db\Migration;

class m250730_080433_create_fcm_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        $this->createTable('fcm_token', [
            'id' => $this->primaryKey(),
            'token' => $this->string(255),
            'device_id' => $this->string(255),
            'user_id' => $this->integer('11'),
            'created_at' => $this->integer('11'),
            'updated_at' => $this->integer('11'),
        ], $tableOptions);
        $this->addForeignKey(
            'fk-fcmtkn-user_id',
            'fcm_token',
            'user_id',
            'user',
            'id',
            'CASCADE',
            'NO ACTION'
        );
        $this->createIndex(
            'idx-fcmtkn-tkn',
            'fcm_token',
            'token'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('fcm_token');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250730_080433_create_fcm_token cannot be reverted.\n";

        return false;
    }
    */
}
