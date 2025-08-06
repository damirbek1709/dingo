<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
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
        //Notification::createNotification(3, 7, booking_id: 95);
        
        $type = 4;
        $model_id = 7;
        $booking_id = 95;

        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->getDocument($model_id);
        $notification = new Notification();
        $notification->type = $type;
        $notification->status = Notification::STATUS_NOT_READ;
        $notification->user_id = Yii::$app->user->id;
        $notification->booking_id = $booking_id;

        switch ($type) {
            case Notification::TYPE_BOOKING_APPROVED:
                $name = $res['name'];
                $notification->title = "Бронирование потверждено";
                $notification->title_en = "Your booking is approved";
                $notification->title_ky = "Сиздин ээлонуз кабыл алынды";

                $notification->text = "Вы забронировали номер в " . $name[0] . " .Готовьтесь к поездке с 2025-08-01 по 2025-08-06.";
                $notification->text_en = "You have booked a room at " . $name[1] . " Get ready for your trip from 2025-08-01 to 2025-08-06.";
                $notification->text_ky = "Сиз " . $name[2] . " мейманканасынан бөлмө ээлеп алдыңыз! 2025-08-01ден 2025-08-06 чейин сапарыңызга даярданыңыз.";
                $notification->model_id = $model_id;
                $notification->category = Notification::CATEGORY_BOOKING;
                break;

            case Notification::TYPE_LEAVE_FEEDBACK:
                $name = $res['name'];

                $notification->title = "Оставьте отзыв";
                $notification->title_en = "Leave a feedback";
                $notification->title_ky = "Cын калтыргыла";

                $notification->text = "Как прошла поездка в " . $name[0] . ". Ваш отзыв поможет другим путешественникам и поддержит хоста.";
                $notification->text_en = "How was your trip to " . $name[1] . " Your feedback will help other travelers and support the host.";
                $notification->text_ky = $name[2] . " саякатыңыз кандай өттү? Пикириңиз башка саякатчыларга жардам берип, үй ээсине колдоо көрсөтөт";

                $notification->model_id = $model_id;
                $notification->category = Notification::CATEGORY_FEEDBACK;
                break;

            case Notification::TYPE_CHECKIN_TOMORROW:
                $name = $res['name'];

                $notification->title = "Завтра заселение!";
                $notification->title_en = "Tomorrow is a checking!";
                $notification->title_ky = "Эртең катталуу!";

                $notification->text = "Остался всего 1 день до поездки в ".$name[0].  ". Проверьте адрес и инструкции от хоста.";
                $notification->text_en = "Only 1 day left until travel to ".$name[1]. ". Check the address and instructions from the host.";
                $notification->text_ky = $name[2]."  саякатка 1 гана күн калды. Үй ээсинен даректи жана көрсөтмөлөрдү текшериңиз.";

                $notification->model_id = $model_id;
                $notification->booking_id = $booking_id;
                $notification->category = Notification::CATEGORY_BOOKING;
                break;

            default:
                $name = $res['name'];
                $notification->title = "Бронирование потверждено";
                $notification->title_en = "Your booking is approved";
                $notification->title_ky = "Сиздин ээлонуз кабыл алынды";

                $notification->text = "Вы забронировали номер в " . $name[0] . " .Готовьтесь к поездке с 2025-08-01 по 2025-08-06.";
                $notification->text_en = "You have booked a room at " . $name[1] . " Get ready for your trip from 2025-08-01 to 2025-08-06.";
                $notification->text_ky = "Сиз " . $name[2] . " мейманканасынан бөлмө ээлеп алдыңыз! 2025-08-01ден 2025-08-06 чейин сапарыңызга даярданыңыз.";
                $notification->model_id = $model_id;
                $notification->category = Notification::CATEGORY_BOOKING;
                break;
        }

        $notification->save();
    }
}