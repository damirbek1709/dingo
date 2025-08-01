<?php

use yii\db\Migration;

class m250801_100508_create_notification_qwert extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notification', 'model_id', $this->integer(11)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250801_100508_create_notification_qwert cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250801_100508_create_notification_qwert cannot be reverted.\n";

        return false;
    }
    */
}
