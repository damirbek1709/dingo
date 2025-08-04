<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int $type
 * @property string $title
 * @property string $text
 * @property int $status
 * @property string $date
 */
class Notification extends \yii\db\ActiveRecord
{
    const TYPE_BOOKING_APPROVED = 1;
    const TYPE_CHECKIN_TOMORROW = 2;
    const TYPE_LEAVE_FEEDBACK = 3;
    const TYPE_BOOKING_CANCELED_BY_GUEST = 4;
    const TYPE_REFUND_PROGRESS = 5;
    const TYPE_REFUNF_COMPLETE = 6;

    const STATUS_READ = 1;
    const STATUS_NOT_READ = 0;

    const CATEGORY_BOOKING = 1;
    const CATEGORY_OBJECT = 2;
    const CATEGORY_FEEDBACK = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'title', 'text', 'user_id', 'category'], 'required'],
            [['type', 'status', 'user_id', 'category', 'model_id','booking_id'], 'integer'],
            [['date'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'title' => 'Title',
            'text' => 'Text',
            'status' => 'Status',
            'date' => 'Date',
        ];
    }

    public static function createNotification($type, $model_id ,$booking_id = null)
    {
        $client = Yii::$app->meili->connect();
        $res = $client->index('object')->getDocument($model_id);
        $notification = new Notification();
        $notification->type = $type;
        $notification->status = self::STATUS_NOT_READ;
        $notification->user_id = Yii::$app->user->id;


        switch ($type) {
            case self::TYPE_BOOKING_APPROVED:
                $name = $res['name'];
                $notification->title = "Бронирование потверждено";
                $notification->title_en = "Your booking is approved";
                $notification->title_ky = "Сиздин ээлонуз кабыл алынды";

                $notification->text = "Вы забронировали номер в " . $name[0] . " .Готовьтесь к поездке с 2025-08-01 по 2025-08-06.";
                $notification->text_en = "You have booked a room at " . $name[1] . " Get ready for your trip from 2025-08-01 to 2025-08-06.";
                $notification->text_ky = "Сиз " . $name[2] . " мейманканасынан бөлмө ээлеп алдыңыз! 2025-08-01ден 2025-08-06 чейин сапарыңызга даярданыңыз.";
                $notification->model_id = $model_id;
                $notification->category = self::CATEGORY_BOOKING;
                break;

            case self::TYPE_LEAVE_FEEDBACK:
                $name = $res['name'];

                $notification->title = "Как прошла поездка в " . $name[0] . ". Ваш отзыв поможет другим путешественникам и поддержит хоста.";
                $notification->title_en = "Your booking is approved";
                $notification->title_ky = "Сиздин ээлонуз кабыл алынды";

                $notification->text = "Как прошла поездка в " . $name[0] . ". Ваш отзыв поможет другим путешественникам и поддержит хоста.";
                $notification->text_en = "How was your trip to " . $name[1] . " Your feedback will help other travelers and support the host.";
                $notification->text_ky = $name[2] . " саякатыңыз кандай өттү? Пикириңиз башка саякатчыларга жардам берип, үй ээсине колдоо көрсөтөт";

                $notification->model_id = $model_id;
                $notification->category = self::CATEGORY_FEEDBACK;
                break;

            case self::TYPE_CHECKIN_TOMORROW:
                $name = $res['name'];

                $notification->title = "Завтра заселение!";
                $notification->title_en = "Tomorrow is a checking!";
                $notification->title_ky = "Эртең катталуу!";

                $notification->text = "Остался всего 1 день до поездки в ".$name[0].  ". Проверьте адрес и инструкции от хоста.";
                $notification->text_en = "Only 1 day left until travel to ".$name[1]. ". Check the address and instructions from the host.";
                $notification->text_ky = $name[2]."  саякатка 1 гана күн калды. Үй ээсинен даректи жана көрсөтмөлөрдү текшериңиз.";

                $notification->model_id = $model_id;
                $notification->booking_id = $booking_id;
                $notification->category = self::CATEGORY_BOOKING;
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
                $notification->category = self::CATEGORY_BOOKING;
                break;
        }

        $notification->save();
    }

    public function fields()
    {
        return [
            'id',
            'type',
            'titleList',
            'textList',
            'status',
            'date',
            'booking',
            'object'
        ];
    }

    // public function fields()
    // {
    //     $field_condition = $this->fieldarray('type');
    //     $arr = [];
    // }

    // protected function fieldarray($type)
    // {
    //     switch ($type) {
    //         case self::TYPE_CHECKIN_TOMORROW:
    //             return [
    //                 'id',
    //                 'type',
    //                 'model_id',
    //                 'titleList',
    //                 'textList',
    //                 'status',
    //                 'date'
    //             ];
    //             break;

    //         case self::TYPE_LEAVE_FEEDBACK:
    //             return [
    //                 'id',
    //                 'type',
    //                 'model_id',
    //                 'titleList',
    //                 'textList',
    //                 'status',
    //                 'date'
    //             ];
    //             break;

    //         default:
    //             return [
    //                 'id',
    //                 'type',
    //                 'model_id',
    //                 'titleList',
    //                 'textList',
    //                 'status',
    //                 'date'
    //             ];
    //     }
    // }

    public function getBooking(){
        if($this->type == self::TYPE_CHECKIN_TOMORROW){
            return Booking::findOne($this->booking_id);
        }
        return null;
    }

    public function getObject(){
        $client = Yii::$app->meili->connect();
        return $client->index('object')->getDocument($this->model_id);
    }

    public function getTitleList()
    {
        return [
            $this->title,
            $this->title_en,
            $this->title_ky
        ];
    }

    public function getTextList()
    {
        return [
            $this->text,
            $this->text_en,
            $this->text_ky
        ];
    }
}
