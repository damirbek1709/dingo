<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
use app\models\Booking;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Notification;


/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NotificationController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionCheckinSoon()
    {
        $booking = Booking::find()
            ->where(['date_from' => date('Y-m-d', strtotime('+1 day'))])
            ->all();
        foreach ($booking as $item) {
            Notification::createNotification(3, 7, 95, $item->user_id);
        }

    }
}