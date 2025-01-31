<?php
namespace app\components;

use yii\mail\BaseMessage;

class ResendMessage extends BaseMessage
{
    private $from;
    private $to;
    private $subject;
    private $htmlBody;
    private $textBody;
    private $bcc;
    private $cc;
    private $replyTo;
    private $charset;

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;
        return $this;
    }

    public function getTextBody()
    {
        return $this->textBody;
    }

    public function setTextBody($text)
    {
        $this->textBody = $text;
        return $this;
    }

    public function getBcc()
    {
        return $this->bcc;
    }

    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    public function getCc()
    {
        return $this->cc;
    }

    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    public function getReplyTo()
    {
        return $this->replyTo;
    }

    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    public function attach($fileName, array $options = [])
    {
        throw new \BadMethodCallException('Attachments are not supported by Resend API yet.');
    }

    public function attachContent($content, array $options = [])
    {
        throw new \BadMethodCallException('Attachments are not supported by Resend API yet.');
    }

    public function embed($fileName, array $options = [])
    {
        throw new \BadMethodCallException('Embedding files is not supported by Resend API yet.');
    }

    public function embedContent($content, array $options = [])
    {
        throw new \BadMethodCallException('Embedding content is not supported by Resend API yet.');
    }

    public function toString()
    {
        return "To: " . implode(', ', (array) $this->to) . "\n" .
            "From: " . $this->from . "\n" .
            "Subject: " . $this->subject . "\n" .
            "CC: " . implode(', ', (array) $this->cc) . "\n" .
            "BCC: " . implode(', ', (array) $this->bcc) . "\n" .
            "Reply-To: " . $this->replyTo . "\n" .
            "Body: " . $this->textBody;
    }
}

?>