<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Model\Service;


use Application\Model\Service\Entity\MailEntity;
use Application\Model\Service\Interfaces\EmailBuilderInterface;
use Application\Model\Service\Interfaces\MailerInterface;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\FileOptions;
use Laminas\Mail\Transport\Sendmail;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class EmailBuilderFileTemplate implements EmailBuilderInterface
{
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var PhpRenderer
     */
    private $renderer;
    /**
     * @var ViewModel
     */
    private $view_model;

    private $_bodyText;
    private $_subject;
    private $_receiverMail;
    private $_receiverAlias;
    private $_fromAlias = "opendesktop.org";
    private $_fromMail = "contact@opendesktop.org";
    private $_tplVars = array();
    /**
     * @var File|Sendmail
     */
    private $_transport;

    public function __construct(MailerInterface $mailer, PhpRenderer $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
        $this->renderer->layout()->setTemplate('/emails/layout.phtml');
        $this->view_model = new ViewModel();
    }

    public function withTemplate($templateName)
    {
        $this->view_model->setTemplate($templateName);

        return $this;
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;

        return $this;
    }

    public function withSendmailTransport()
    {
        $this->_transport = new Sendmail();

        return $this;
    }

    public function withFileTransport()
    {
        $transport = new File();
        $options = new FileOptions(
            array(
                'path'     => 'data/mail/',
                'callback' => function (File $transport) {
                    return 'Message_' . microtime(true) . '_' . mt_rand() . '.eml';
                },
            )
        );
        $transport->setOptions($options);
        $this->_transport = $transport;

        return $this;
    }

    public function setTemplateVar($name, $value)
    {
        $this->_tplVars[$name] = $value;

        return $this;
    }

    public function setReceiverMail($mail)
    {
        $this->_receiverMail = $mail;

        return $this;
    }

    public function setFromMail($mail)
    {
        $this->_fromMail = $mail;

        return $this;
    }

    public function setReceiverAlias($alias)
    {
        $this->_receiverAlias = $alias;

        return $this;
    }

    public function build()
    {
        $this->view_model->setVariables($this->_tplVars);
        $body_text = $this->renderer->render($this->view_model);
        $this->setBodyText($body_text);

        $mail = new MailEntity();
        $mail->setBody($body_text);
        $mail->setFromAlias($this->_fromAlias);
        $mail->setFromMail($this->_fromMail);
        $mail->setReceiverMail($this->_receiverMail);
        $mail->setReceiverAlias($this->_receiverAlias);
        $mail->setSubject($this->_subject);

        return $mail;
    }

    public function setBodyText($text)
    {
        $this->_bodyText = $text;

        return $this;
    }

    public function setFromAlias($alias)
    {
        $this->_fromAlias = $alias;

        return $this;
    }
}