<?php

use yii\db\Migration;

/**
 * Class m250225_151203_extend_user_model
 */
class m250225_151203_extend_user_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    private $tableName = '{{%user}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'search_data', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250225_151203_extend_user_model cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250225_151203_extend_user_model cannot be reverted.\n";

        return false;
    }
    */
}
