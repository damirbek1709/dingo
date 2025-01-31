<?php 
namespace app\components;

use yii\mail\BaseMailer;
use Resend\Resend as ResendClient;
use yii\mail\MessageInterface;

class ResendMailer extends BaseMailer
{
    public $apiKey;
    public $messageClass = 'app\components\ResendMessage';

    protected function sendMessage($message)
    {
        $resend = new ResendClient($this->apiKey);
        
        $response = $resend->emails->send([
            'from' => $message->getFrom(),
            'to' => $message->getTo(),
            'subject' => $message->getSubject(),
            'html' => $message->getHtmlBody(),
            'text' => $message->getTextBody(),
        ]);

        return !empty($response->id);
    }
}

?>