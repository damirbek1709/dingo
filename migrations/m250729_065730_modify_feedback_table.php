<?php

use yii\db\Migration;

/**
 * Class m250729_065730_modify_feedback_table
 */
class m250729_065730_modify_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('feedback', 'created_at', $this->date()->notNull()->defaultValue(date('Y-m-d')));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250729_065730_modify_feedback_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250729_065730_modify_feedback_table cannot be reverted.\n";

        return false;
    }
    */
}
