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

namespace Application\Model\Service\Interfaces;

use Application\Model\Service\MemberEmailService;

interface MemberEmailServiceInterface
{
    /**
     * @param int    $user_name
     * @param string $member_email
     *
     * @return string
     */
    public static function getVerificationValue($user_name, $member_email);

    public static function getHashForMailAddress($mail_address);

    /**
     * @param int  $member_id
     * @param bool $email_deleted
     */
    public function fetchAllMailAddresses($member_id, $email_deleted = false);

    /**
     * @param $emailId
     * @param $member_id
     *
     * @return bool
     */
    public function setDefaultEmail($emailId, $member_id);

    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function fetchMemberPrimaryMail($member_id);

    /**
     * @param string $verification
     *
     * @return int count of updated rows
     */
    public function verificationEmail($verification);

    /**
     * @param int         $user_id
     * @param string      $user_mail
     * @param null|string $user_verification
     */
    public function saveEmail($user_id, $user_mail, $user_verification = null);

    /**
     * @param int         $user_id
     * @param string      $user_mail
     * @param int         $user_mail_checked
     * @param null|string $user_verification
     */
    public function saveEmailAsPrimary($user_id, $user_mail, $user_mail_checked = 0, $user_verification = null);

    /**
     * @param $member_id
     *
     * @return null
     */
    public function getValidationValue($member_id);

    /**
     * @param string $value
     * @param int    $test_case_sensitive
     *
     * @param array  $omitMember
     *
     * @return mixed
     */
    public function findMailAddress(
        $value,
        $test_case_sensitive = MemberEmailService::CASE_INSENSITIVE,
        $omitMember = array()
    );

    /**
     * @param $val
     * @param $verificationVal
     *
     * @return mixed
     */
    public function sendConfirmationMail($val, $verificationVal);
}