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


use Application\Model\Service\Interfaces\EmailBuilderInterface;
use Exception;
use JobQueue\Jobs\EmailJob;
use JobQueue\Jobs\JobBuilder;
use Laminas\Db\ResultSet\ResultSet;
use RuntimeException;

class RegisterManager
{
    /** @var MemberService */
    private $member_service;
    /** @var bool */
    private $double_opt_in;
    /** @var EmailBuilder */
    private $member_email_service;
    /**
     * @var array
     */
    private $mail_config;

    public function __construct(
        MemberService $member_service,
        array $config,
        EmailBuilderInterface $member_email_service
    ) {
        $this->mail_config = $config['ocs_config']['settings']['mail'];
        $this->member_service = $member_service;
        $this->double_opt_in = (boolean)$config['ocs_config']['settings']['double_opt_in']['active'];
        $this->member_email_service = $member_email_service;
    }

    /**
     * @param $user_data
     *
     * @return array
     * @throws Exception
     */
    public function register($user_data)
    {
        if (false === $this->double_opt_in) {
            $user_data['mail_checked'] = 1;
            $user_data['is_active'] = 1;
            $user_data['is_deleted'] = 0;
        }
        $userTable = $this->member_service;
        $user_data = $userTable->createNewUser($user_data);

        if ($this->double_opt_in) {
            $this->sendConfirmationMail($user_data, $user_data['verificationVal']);
        }

        return $user_data;
    }

    /**
     * @param array  $val
     * @param string $verificationVal
     */
    protected function sendConfirmationMail($val, $verificationVal)
    {
        $protocol = 'http';
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https';
        }
        $url = "{$protocol}://{$_SERVER['HTTP_HOST']}/register/confirm/vid/{$verificationVal}";
        $confirmMail = $this->member_email_service;
        $mail = $confirmMail->withTemplate('tpl_verify_user')->setTemplateVar('servername', $_SERVER['HTTP_HOST'])
                            ->setTemplateVar('username', $val['username'])
                            ->setTemplateVar('verificationlinktext', '<a href="' . $url . '">Click here to verify your email address</a>')
                            ->setTemplateVar('verificationlink', '<a href="' . $url . '">' . $url . '</a>')
                            ->setTemplateVar('verificationurl', $url)->setReceiverMail($val['mail'])
                            ->setFromMail('registration@opendesktop.org')->build();

        JobBuilder::getJobBuilder()->withJobClass(EmailJob::class)->withParam('mail', serialize($mail))
                  ->withParam('withFileTransport', $this->mail_config['transport']['withFileTransport'])
                  ->withParam('withSmtpTransport', $this->mail_config['transport']['withSmtpTransport'])
                  ->withParam('config', serialize($this->mail_config))->build();
    }

    /**
     * @param $token
     *
     * @return ResultSet
     */
    public function finishConfirmedRegistration($token)
    {
        $result = $this->member_service->findRegisterToken($token);
        if ($result->count() == 1) {
            $member = $result->current();
            if ($member->email_checked) {
                throw new RuntimeException('user has already been activated.');
            }
            $activated = $this->member_service->activateMemberFromVerification($member->member_id, $token);
            if ($activated != 1) {
                throw new RuntimeException('Could not active member after register: ' . $member->member_id);
            }
        }
        if ($result->count() > 1) {
            throw new RuntimeException('register token is ambiguous: ' . $token);
        }
        if ($result->count() == 0) {
            throw new RuntimeException('cannot find valid register token: ' . $token);
        }
        // ensure that we have the current data
        $result = $this->member_service->findRegisterToken($token);

        return $result;
    }

    /**
     * @param string $member_mail
     *
     * @return bool
     * @throws Exception
     */
    public function resendConfirmation($member_mail)
    {
        $user_data = $this->member_service->getMemberRepository()->findOneBy(['mail'=>$member_mail]);

        if (false === $user_data->hasIdentity()) {
            throw new Exception('could not found data for member_id: ' . print_r($member_mail, true));
        }

        $validation_value = $this->getMemberService()->getMemberEmailService()
                                 ->getValidationValue($user_data->member_id);
        if (empty($validation_value)) {
            throw new Exception('could not found validation value for member_id: ' . print_r($user_data->member_id, true));
        }
        $this->sendConfirmationMail($user_data->getArrayCopy(), $validation_value);

        return true;
    }

    /**
     * @return MemberService
     */
    public function getMemberService()
    {
        return $this->member_service;
    }

}