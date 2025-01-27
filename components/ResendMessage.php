<?php
namespace app\components;

use yii\mail\BaseMessage;

class ResendMessage extends BaseMessage
{
    private $_from;
    private $_to;
    private $_subject;
    private $_htmlBody;
    private $_textBody;
    private $_charset = 'UTF-8';
    private $_replyTo;
    private $_cc;
    private $_bcc;
    private $_attachments = [];
    private $_embeddedFiles = [];

    public function getCharset()
    {
        return $this->_charset;
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }

    public function getFrom()
    {
        return $this->_from;
    }

    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }

    public function getTo()
    {
        return $this->_to;
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $replyTo;
        return $this;
    }

    public function getCc()
    {
        return $this->_cc;
    }

    public function setCc($cc)
    {
        $this->_cc = $cc;
        return $this;
    }

    public function getBcc()
    {
        return $this->_bcc;
    }

    public function setBcc($bcc)
    {
        $this->_bcc = $bcc;
        return $this;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    public function setHtmlBody($html)
    {
        $this->_htmlBody = $html;
        return $this;
    }

    public function getHtmlBody()
    {
        return $this->_htmlBody;
    }

    public function setTextBody($text)
    {
        $this->_textBody = $text;
        return $this;
    }

    public function getTextBody()
    {
        return $this->_textBody;
    }

    public function attach($fileName, array $options = [])
    {
        $this->_attachments[] = ['fileName' => $fileName, 'options' => $options];
        return $this;
    }

    public function embed($fileName, array $options = [])
    {
        $cid = uniqid('cid:');
        $this->_embeddedFiles[] = [
            'fileName' => $fileName,
            'options' => $options,
            'cid' => $cid
        ];
        return $cid;
    }

    public function toString()
    {
        return json_encode([
            'from' => $this->_from,
            'to' => $this->_to,
            'subject' => $this->_subject,
            'htmlBody' => $this->_htmlBody,
            'textBody' => $this->_textBody
        ]);
    }

    public function attachContent($content, array $options = [])
    {
        $this->_attachments[] = [
            'content' => $content,
            'options' => $options
        ];
        return $this;
    }

    public function embedContent($content, array $options = [])
    {
        $cid = uniqid('cid:');
        $this->_embeddedFiles[] = [
            'content' => $content,
            'options' => $options,
            'cid' => $cid
        ];
        return $cid;
    }
}