<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use yii\base\InvalidConfigException;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

class Sms extends Component
{
    /**
     * @var string the api url for establishing connection.
     */
    private $apiUrl = 'https://online.mirsms.ru/api/';

    /**
     * @var string the username for establishing connection.
     */
    public $username;
    /**
     * @var string the password for establishing connection.
     */
    public $password;

    /**
     * @var string the recipient(s) phone number(s)
     */
    private $token;

    /**
     * @var string|array the recipient(s) phone number(s)
     */
    private $recipient;

    /**
     * @var string the message type
     */
    private $type;

    /**
     * @var string the message sender name
     */
    private $sender;

    /**
     * @var string the text to be sent
     */
    private $text;

    /**
     * @throws \yii\httpclient\Exception
     * @throws InvalidConfigException
     */
    private function login()
    {
        $client = new Client();

        $response = $client
            ->createRequest()
            ->setMethod('POST')
            ->setUrl($this->apiUrl.'login')
            ->setData([
                'username' => $this->username,
                'password' => $this->password,
            ])->send();

        if ($response->isOk) {
            $this->setToken(ArrayHelper::getValue($response->data, 'token'));
        } else {
            throw new InvalidConfigException(ArrayHelper::getValue($response->data, 'message'));
        }
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private function getToken()
    {
        if (!isset($this->token)) {
            throw new InvalidArgumentException('Token must be set');
        }

        return $this->token;
    }

    /**
     * @param string|array $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getRecipient()
    {
        if (!isset($this->recipient)) {
            throw new InvalidArgumentException('Recipient must be set');
        }

        return $this->recipient;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        if (!isset($this->type)) {
            throw new InvalidArgumentException('Type must be set');
        }

        return $this->type;
    }

    /**
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        if (!isset($this->sender)) {
            throw new InvalidArgumentException('Sender must be set');
        }

        return $this->sender;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        if (!isset($this->text)) {
            throw new InvalidArgumentException('Text must be set');
        }

        return $this->text;
    }

    /**
     * @throws InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function send()
    {
        $client = new Client();

        $this->login();

        $response = $client
            ->createRequest()
            ->setMethod('POST')
            ->setUrl($this->apiUrl.'sendings')
            ->setHeaders(['Authorization' => $this->getToken()])
            ->setData([
                'recipient' => $this->getRecipient(),
                'type' => $this->getType(),
                'payload' => [
                    'sender' => $this->getSender(),
                    'text' => $this->getText(),
                ],
            ])->send();

        if (!$response->isOk) {
            throw new InvalidArgumentException(ArrayHelper::getValue($response->data, 'message'));
        }
    }
}
