<?php

namespace app\components;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidArgumentException;

class Nikita extends Component
{
    /**
     * @var string the api url for establishing connection.
     */
    private $apiUrl = 'http://smspro.nikita.kg/api/message';

    /**
     * @var string the login for establishing connection.
     */
    public $login;
    /**
     * @var string the password for establishing connection.
     */
    public $password;

    /**
     * @var string the message sender name
     */
    public $sender;

    /**
     * @var string|array the recipient(s) phone number(s)
     */
    private $recipient;

    /**
     * @var string the text to be sent
     */
    private $text;

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
        $transaction_id = rand(100000, 999999);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
            "<message>" .
            "<login>" . $this->login . "</login>" .
            "<pwd>" . $this->password . "</pwd>" .
            "<id>" . $transaction_id . "</id>" .
            "<sender>" . $this->sender . "</sender>" .
            "<text>" . $this->getText() . "</text>" .
            "<phones>" .
            "<phone>" . $this->getRecipient() . "</phone>" .
            "</phones>" .
            "</message>";

        try {
            $this->post_content($this->apiUrl, $xml);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    private function post_content($url, $postdata)
    {
        $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "c://coo.txt");

        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);

        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
    }
}
