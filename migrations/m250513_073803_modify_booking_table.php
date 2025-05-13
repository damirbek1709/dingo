<?php

use yii\db\Migration;

/**
 * Class m250513_073803_modify_booking_table
 */
class m250513_073803_modify_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->dropColumn('booking', 'speacial_comment');
    }

    public function down()
    {
        $this->addColumn('booking', 'speacial_comment', $this->string(500));
    }
}
