<?php
namespace app\components;

use GuzzleHttp\Client;

class ResendMailer
{
    private $apiKey;
    private $client;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;

        $this->client = new Client([
            'base_uri' => 'https://api.resend.com/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function send($from, $to, $subject, $html)
    {
        $response = $this->client->post('emails', [
            'json' => [
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
