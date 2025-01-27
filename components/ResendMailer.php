<?php 
namespace app\components;

use Http\Discovery\Exception\NotFoundException;
use Resend\Transporters\CurlTransporter;
use Resend\Client;

class ResendMailer extends \yii\base\Component
{
    public $apiKey; // Property to hold the API key
    private $client;

    public function init()
    {
        parent::init();

        if (!$this->apiKey) {
            throw new \Exception('Resend API key is required.');
        }

        // Initialize the Resend Client with the API key
        $this->client = new Client(['api_key' => $this->apiKey]);
    }


    public function sendEmail($to, $from, $subject, $html, $text = null)
    {
        try {
            $response = $this->client->emails->send([
                'to' => $to,
                'from' => $from,
                'subject' => $subject,
                'html' => $html,
                'text' => $text,
            ]);

            return $response; // Contains email ID or success message
        } catch (NotFoundException $e) {
            // Handle exceptions
            \Yii::error('Failed to send email: ' . $e->getMessage());
            return false;
        }
    }
}

?>