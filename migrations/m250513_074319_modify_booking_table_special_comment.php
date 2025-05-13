<?php

use yii\db\Migration;

/**
 * Class m250513_074319_modify_booking_table_special_comment
 */
class m250513_074319_modify_booking_table_special_comment extends Migration
{
    public function up()
    {
        $this->addColumn('booking', 'special_comment', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('booking', 'special_comment');
    }
}
