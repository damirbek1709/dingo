<?php

namespace app\components;

use Resend\Client;

class ResendClient
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(
            apiKey: 're_atcXWkEq_LaWuaA5QKX5GmHNpWyFbGKDx' 
        );
    }

    public function sendEmail(
        string $from,
        string $to,
        string $subject,
        string $html,
        array $attachments = []
    ) {
        try {
            $response = $this->client->emails()->send([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
                'attachments' => $attachments,
            ]);

            // Handle the response (e.g., log successful sends)
            return $response;

        } catch (\Exception $e) {
            // Handle errors (e.g., log errors, send notifications)
            throw $e; 
        }
    }
}