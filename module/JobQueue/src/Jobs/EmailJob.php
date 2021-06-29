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

namespace JobQueue\Jobs;


use JobQueue\Jobs\Interfaces\JobInterface;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\FileOptions;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

/**
 * Class EmailJob
 *
 * @package JobQueue\Jobs
 */
class EmailJob implements JobInterface
{

    public function setUp()
    {
        // Set up environment for this job
    }

    /**
     * @param $args
     *
     * @see \Application\Model\Service\RegisterManager::sendConfirmationMail
     * @see \Application\Controller\ProductcommentController::sendNotificationToParent
     * @see \Application\Controller\ProductcommentController::sendNotificationToOwner
     */
    public function perform($args)
    {
        $transport = new Sendmail();
        if (isset($args['withFileTransport']) and $args['withFileTransport'] == true) {
            $transport = $this->getFileTransport();
        }
        if (isset($args['withSmtpTransport']) and $args['withSmtpTransport'] == true) {
            $transport = $this->getSmtpTransport(unserialize($args['config']));
        }

        $mail_entity = unserialize($args['mail']);

        $mail = new \Laminas\Mail\Message();
        $mail->setFrom($mail_entity->getFromMail(), $mail_entity->getFromAlias());
        $mail->addTo($mail_entity->getReceiverMail());
        $mail->setSubject($mail_entity->getSubject());
        $mail->setEncoding('utf-8');

        $html = new Part($mail_entity->getBody());
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new Message();
        $body->setParts([$html]);

        $mail->setBody($body);

        $transport->send($mail);
    }

    private function getFileTransport()
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

        return $transport;
    }

    /**
     * @param array $config
     *
     * @return Smtp
     */
    private function getSmtpTransport($config)
    {
        // Setup SMTP transport using LOGIN authentication
        $transport = new Smtp();
        $options = new SmtpOptions(
            array(
                'host'              => $config['transport']['host'],
                'connection_class'  => $config['transport']['connection_class'],
                'connection_config' => array(
                    'username' => $config['transport']['username'],
                    'password' => $config['transport']['password'],
                    'ssl'      => $config['transport']['ssl'],
                ),
            )
        );
        $transport->setOptions($options);

        return $transport;
    }

    public function tearDown()
    {
        // Remove environment for this job
    }

}