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


use Application\Model\Interfaces\MailTemplateInterface;
use Application\Model\Repository\MailTemplateRepository;
use Application\Model\Service\Interfaces\EmailBuilderInterface;
use Application\Model\Service\Interfaces\MailerInterface;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\FileOptions;
use Laminas\Mail\Transport\Sendmail;

class EmailBuilder implements EmailBuilderInterface
{
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var MailTemplateRepository
     */
    private $mail_template_repository;

    public function __construct(MailerInterface $mailer, MailTemplateInterface $mail_template_repository)
    {
        $this->mailer = $mailer;
        $this->mail_template_repository = $mail_template_repository;
    }

    public function withTemplate($templateName)
    {
        $mailTpl = $this->mail_template_repository->findBy('name', $templateName);

        $this->setSubject($mailTpl->subject)->setBodyText($mailTpl->text);

        return $this;
    }

    public function setBodyText($text)
    {
        $this->mailer->setBodyText($text);

        return $this;
    }

    public function setSubject($subject)
    {
        $this->mailer->setSubject($subject);

        return $this;
    }

    public function withSendmailTransport()
    {
        $this->mailer->setTransport(new Sendmail());

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
        $this->mailer->setTransport($transport);

        return $this;
    }

    public function setTemplateVar($name, $value)
    {
        $this->mailer->setTemplateVar($name, $value);

        return $this;
    }

    public function setReceiverMail($mail)
    {
        $this->mailer->setReceiverMail($mail);

        return $this;
    }

    public function setFromMail($mail)
    {
        $this->mailer->setFromMail($mail);

        return $this;
    }

    public function setReceiverAlias($alias)
    {
        $this->mailer->setReceiverAlias($alias);

        return $this;
    }

    public function build()
    {
        return $this->mailer->getHtmlMailEntity();
    }

}