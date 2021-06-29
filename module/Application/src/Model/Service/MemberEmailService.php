<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

/** @noinspection SqlResolve */

namespace Application\Model\Service;

use Application\Model\Entity\Member;
use Application\Model\Interfaces\MemberEmailInterface;
use Application\Model\Interfaces\MemberInterface;
use Application\Model\Repository\MemberEmailRepository;
use Application\Model\Service\Interfaces\MemberEmailServiceInterface;
use JobQueue\Jobs\EmailJob;
use JobQueue\Jobs\JobBuilder;
use Laminas\Db\Sql\Expression;
use Laminas\View\Helper\ServerUrl;

class MemberEmailService extends BaseService implements MemberEmailServiceInterface
{
    const CASE_INSENSITIVE = 1;

    /**
     * @var MemberEmailRepository
     */
    protected $memberEmailRepository;
    /**
     * @var Member
     */
    protected $memberRepository;
    protected $serverUrl;
    /**
     * @var EmailBuilder
     */
    protected $mailer;

    /**
     * MemberEmailService constructor.
     *
     * @param MemberEmailInterface $memberEmailRepository
     * @param MemberInterface      $memberRepository
     * @param ServerUrl            $serverUrl
     * @param EmailBuilder         $mailer
     */
    public function __construct(
        MemberEmailInterface $memberEmailRepository,
        MemberInterface $memberRepository,
        ServerUrl $serverUrl,
        EmailBuilder $mailer
    ) {
        $this->memberEmailRepository = $memberEmailRepository;
        $this->memberRepository = $memberRepository;
        $this->serverUrl = $serverUrl;
        $this->mailer = $mailer;
    }

    public static function getHashForMailAddress($mail_address)
    {
        return md5($mail_address);
    }

    /**
     * @param int  $member_id
     * @param bool $email_deleted
     *
     * @return
     */
    public function fetchAllMailAddresses($member_id, $email_deleted = false)
    {
        $deleted = $email_deleted === true ? MemberEmailRepository::EMAIL_DELETED : MemberEmailRepository::EMAIL_NOT_DELETED;

        return $this->memberEmailRepository->fetchAllRows(
            [
                'email_member_id' => $member_id,
                'email_deleted'   => $deleted,
            ]
        );
    }

    /**
     * @param $emailId
     * @param $member_id
     *
     * @return bool
     */
    public function setDefaultEmail($emailId, $member_id)
    {
        $result = $this->resetDefaultMailAddress($member_id);
        $this->memberEmailRepository->setPrimary($emailId);
        $this->updateMemberPrimaryMail($member_id); /* if we change the mail in member table, we change the login. */
        ActivityLogService::logActivity($member_id, null, $member_id, ActivityLogService::MEMBER_EMAIL_CHANGED);

        return true;
    }

    /**
     * @param $member_id
     *
     * @return bool
     */
    private function resetDefaultMailAddress($member_id)
    {
        $sql = "UPDATE `member_email` SET `email_primary` = 0 WHERE `email_member_id` = :member_id AND `email_primary` = 1";
        $stmt = $this->memberEmailRepository->query($sql, array('member_id' => $member_id));

        return $stmt->count() > 0;
    }

    /**
     * @param $member_id
     *
     * @return mixed
     */
    private function updateMemberPrimaryMail($member_id)
    {
        $dataEmail = $this->fetchMemberPrimaryMail($member_id);

        return $this->saveMemberPrimaryMail($member_id, $dataEmail);
    }

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchMemberPrimaryMail($member_id)
    {
        $sql = "SELECT * FROM {$this->memberEmailRepository->getName()} WHERE email_member_id = :member_id AND email_primary = 1";
        $dataEmail = $this->memberEmailRepository->fetchRow($sql, array('member_id' => $member_id));

        return $dataEmail;
    }

    /**
     * @param integer $member_id
     * @param array   $dataEmail
     *
     * @return mixed
     */
    protected function saveMemberPrimaryMail($member_id, $dataEmail)
    {
        /** @var Member $dataMember */
        $dataMember = $this->memberRepository->findById($member_id);
        $dataMember->mail = $dataEmail['email_address'];
        $dataMember->mail_checked = isset($dataEmail['email_checked']) ? 1 : 0;
        $this->memberRepository->update($dataMember->getArrayCopy());
    }

    /**
     * @param string $verification
     *
     * @return int count of updated rows
     */
    public function verificationEmail($verification)
    {
        $sql = "UPDATE `member_email` SET `email_checked` = NOW() WHERE `email_verification_value` = :verification AND `email_deleted` = 0 AND `email_checked` IS NULL";
        $stmnt = $this->memberEmailRepository->query($sql, array('verification' => $verification));

        return $stmnt->getAffectedRows();
    }

    /**
     * @param int         $user_id
     * @param string      $user_mail
     * @param null|string $user_verification
     *
     * @return int
     */
    public function saveEmail($user_id, $user_mail, $user_verification = null)
    {
        $data = array();
        $data['email_member_id'] = $user_id;
        $data['email_address'] = $user_mail;
        $data['email_hash'] = md5($user_mail);
        $data['email_verification_value'] = empty($user_verification) ? self::getVerificationValue($user_id, $user_mail) : $user_verification;


        ActivityLogService::logActivity(
            $user_id, null, $user_id, ActivityLogService::MEMBER_EMAIL_CHANGED, array('description' => 'user saved new mail address: ' . $user_mail)
        );

        return $this->memberEmailRepository->insertOrUpdate($data);
    }

    /**
     * @param int    $user_name
     * @param string $member_email
     *
     * @return string
     */
    public static function getVerificationValue($user_name, $member_email)
    {
        return md5($user_name . $member_email . time());
    }

    /**
     * @param int         $user_id
     * @param string      $user_mail
     * @param int         $user_mail_checked
     * @param null|string $user_verification
     *
     * @return array|bool
     */
    public function saveEmailAsPrimary($user_id, $user_mail, $user_mail_checked = 0, $user_verification = null)
    {
        if (empty($user_id) or empty($user_mail)) {
            return false;
        }

        $data = array();
        $data['email_member_id'] = $user_id;
        $data['email_address'] = $user_mail;
        $data['email_hash'] = MD5($user_mail);
        $data['email_checked'] = $user_mail_checked == 1 ? new Expression('Now()') : new Expression('NULL');
        $data['email_verification_value'] = empty($user_verification) ? self::getVerificationValue($user_id, $user_mail) : $user_verification;
        $data['email_primary'] = MemberEmailRepository::EMAIL_PRIMARY;

        $result = $this->memberEmailRepository->insertOrUpdate($data);

        $this->resetOtherPrimaryEmail($user_id, $user_mail);

        $this->updateMemberPrimaryMail($user_id);


        ActivityLogService::logActivity(
            $user_id, null, $user_id, ActivityLogService::MEMBER_EMAIL_CHANGED, array('description' => 'user saved new primary mail address: ' . $user_mail)
        );

        return $data;
    }

    private function resetOtherPrimaryEmail($user_id, $user_mail)
    {
        $sql = "
                UPDATE `member_email`
                SET `email_primary` = 0
                WHERE `email_member_id` = :user_id AND `email_address` <> :user_mail; 
                ";
        $this->memberEmailRepository->query($sql, array('user_id' => $user_id, 'user_mail' => $user_mail));

    }

    /**
     * @param $member_id
     *
     * @return string
     */
    public function getValidationValue($member_id)
    {
        $memberData = $this->fetchMemberPrimaryMail($member_id);
        if (count($memberData) == 0) {
            return '';
        }

        return $memberData['email_verification_value'];
    }

    /**
     * @param string $value
     * @param int    $test_case_sensitive
     * @param array  $omitMember
     *
     * @return mixed
     */
    public function findMailAddress($value, $test_case_sensitive = self::CASE_INSENSITIVE, $omitMember = array())
    {
        $sql = "
            SELECT *
            FROM `member_email`
            WHERE
        ";
        if ($test_case_sensitive == self::CASE_INSENSITIVE) {
            $sql .= " LCASE(member_email.email_address) = LCASE(:mail_address)";
        } else {
            $sql .= " member_email.email_address = :mail_address";
        }

        if (count($omitMember) > 0) {
            $sql .= " AND member_email.email_member_id NOT IN (" . implode(',', $omitMember) . ")";
        }

        return $this->memberEmailRepository->fetchAll($sql, array('mail_address' => $value));
    }

    /**
     * @param array  $val
     * @param string $verificationVal
     */
    public function sendConfirmationMail($val, $verificationVal)
    {
        $servername = $this->getServerName();
        $url = "https://{$servername}/register/confirm/{$verificationVal}";

        $confirmMail = $this->mailer;
        $mail = $confirmMail->withTemplate('tpl_verify_user')->setTemplateVar('servername', $servername)
                            ->setTemplateVar('username', $val['username'])
                            ->setTemplateVar('verificationlinktext', '<a href="' . $url . '">Click here to verify your email address</a>')
                            ->setTemplateVar('verificationlink', '<a href="' . $url . '">' . $url . '</a>')
                            ->setTemplateVar('verificationurl', $url)->setReceiverMail($val['mail'])
                            ->setFromMail('registration@opendesktop.org')->build();

        $mail_config = $GLOBALS['ocs_config']['settings']['mail'];
        JobBuilder::getJobBuilder()->withJobClass(EmailJob::class)->withParam('mail', serialize($mail))
                  ->withParam('withFileTransport', $mail_config['transport']['withFileTransport'])
                  ->withParam('withSmtpTransport', $mail_config['transport']['withSmtpTransport'])
                  ->withParam('config', serialize($mail_config))->build();

    }

    private function getServerName()
    {
        return $this->serverUrl->setUseProxy(true)->getHost();
    }

}