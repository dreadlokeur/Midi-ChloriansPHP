<?php

namespace framework\error\observers;

use framework\mail\SwiftMailer;
use framework\utility\Validate;
use framework\Language;

class Mail implements \SplObserver {

    protected $_mailConfig = array();

    public function __construct($mailConfig, $initSendmailFrom = true) {
        SwiftMailer::getInstance();
        // Init sendmail_from
        if ($initSendmailFrom && ini_get('sendmail_from') != $initSendmailFrom) {
            if (!Validate::isEmail($initSendmailFrom))
                throw new \Exception('initSendmailFrom parameter must be a valid email');
            ini_set('sendmail_from', $initSendmailFrom);
        }
        //Set mail config
        if (!is_array($mailConfig))
            throw new \Exception('mailConfig parameter must be an array');

        // sender params
        if (!isset($mailConfig['fromEmail']))
            throw new \Exception('mailConfig[\'fromEmail\'] parameter don\'t exists');
        if (!Validate::isEmail($mailConfig['fromEmail']))
            throw new \Exception('mailConfig[\'fromEmail\'] parameter must be a valid email');
        $this->_mailConfig['fromEmail'] = $mailConfig['fromEmail'];
        if (!isset($mailConfig['fromName']))
            throw new \Exception('mailConfig[\'fromName\'] parameter don\'t exists');
        if (!is_string($mailConfig['fromName']))
            throw new \Exception('mailConfig[\'fromName\'] parameter must be a string');
        $this->_mailConfig['fromName'] = $mailConfig['fromName'];
        // receiver params
        if (!isset($mailConfig['toEmail']))
            throw new \Exception('mailConfig[\'toEmail\'] parameter don\'t exists');
        if (!Validate::isEmail($mailConfig['toEmail']))
            throw new \Exception('mailConfig[\'toEmail\'] parameter must be a valid email');
        $this->_mailConfig['toEmail'] = $mailConfig['toEmail'];
        if (!isset($mailConfig['toName']))
            throw new \Exception('mailConfig[\'toName\'] parameter don\'t exists');
        if (!is_string($mailConfig['fromName']))
            throw new \Exception('mailConfig[\'toName\'] parameter must be a string');
        $this->_mailConfig['toName'] = $mailConfig['toName'];

        //Optional subject of mail params
        if (isset($mailConfig['mailSubject'])) {
            if (!is_string($mailConfig['mailSubject']))
                throw new \Exception('mailConfig[\'mailSubject\'] parameter must be a string');
            $this->_mailConfig['mailSubject'] = $mailConfig['mailSubject'];
        }
    }

    public function update(\SplSubject $subject, $isException = false) {
        $mail = \Swift_Message::newInstance();
        $mail->setFrom(array($this->_mailConfig['fromEmail'] => $this->_mailConfig['fromName']));
        $mail->setTo(array($this->_mailConfig['toEmail'] => $this->_mailConfig['toName']));
        if (isset($this->_mailConfig['mailSubject']))
            $mail->setSubject($this->_mailConfig['mailSubject']);

        if (!$isException) {
            $error = $subject->getError();
            $mail->addPart(Language::getVar('site_name') . ' vient de generer une erreur PHP <br /> <b>' . $error->type . '</b> : ' . $error->message . ' in <b>' . $error->file . '</b> on line <b>' . $error->line . '</b>', 'text/html');
        } else {
            $exception = $subject->getException();
            $mail->addPart(Language::getVar('site_name') . ' vient de generer une exception PHP <br /> <b> ' . $exception->type . ' </b> : "' . $exception->message . '" in <b>' . $exception->file . '</b> on line <b>' . $exception->line . '</b> with trace : <br />' . $exception->trace, 'text/html');
        }
        $transport = defined('SMTP_SERVER') && !is_null(SMTP_SERVER) && SMTP_SERVER != '' ? \Swift_SmtpTransport::newInstance(SMTP_SERVER, 25) : \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($mail);
    }

}

?>