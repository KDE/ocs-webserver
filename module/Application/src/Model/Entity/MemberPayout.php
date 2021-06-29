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

namespace Application\Model\Entity;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class MemberPayout implements InputFilterAwareInterface
{
    public $id;
    public $yearmonth;
    public $member_id;
    public $mail;
    public $paypal_mail;
    public $num_downloads;
    public $num_points;
    public $amount;
    public $status;
    public $created_at;
    public $updated_at;
    public $timestamp_masspay_start;
    public $timestamp_masspay_last_ipn;
    public $last_paypal_ipn;
    public $last_paypal_status;
    public $payment_reference_key;
    public $payment_transaction_id;
    public $payment_raw_message;
    public $payment_raw_error;
    public $payment_status;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->yearmonth = !empty($data['yearmonth']) ? $data['yearmonth'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->mail = !empty($data['mail']) ? $data['mail'] : null;
        $this->paypal_mail = !empty($data['paypal_mail']) ? $data['paypal_mail'] : null;
        $this->num_downloads = !empty($data['num_downloads']) ? $data['num_downloads'] : null;
        $this->num_points = !empty($data['num_points']) ? $data['num_points'] : null;
        $this->amount = !empty($data['amount']) ? $data['amount'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : null;
        $this->timestamp_masspay_start = !empty($data['timestamp_masspay_start']) ? $data['timestamp_masspay_start'] : null;
        $this->timestamp_masspay_last_ipn = !empty($data['timestamp_masspay_last_ipn']) ? $data['timestamp_masspay_last_ipn'] : null;
        $this->last_paypal_ipn = !empty($data['last_paypal_ipn']) ? $data['last_paypal_ipn'] : null;
        $this->last_paypal_status = !empty($data['last_paypal_status']) ? $data['last_paypal_status'] : null;
        $this->payment_reference_key = !empty($data['payment_reference_key']) ? $data['payment_reference_key'] : null;
        $this->payment_transaction_id = !empty($data['payment_transaction_id']) ? $data['payment_transaction_id'] : null;
        $this->payment_raw_message = !empty($data['payment_raw_message']) ? $data['payment_raw_message'] : null;
        $this->payment_raw_error = !empty($data['payment_raw_error']) ? $data['payment_raw_error'] : null;
        $this->payment_status = !empty($data['payment_status']) ? $data['payment_status'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id'                         => $this->id,
            'yearmonth'                  => $this->yearmonth,
            'member_id'                  => $this->member_id,
            'mail'                       => $this->mail,
            'paypal_mail'                => $this->paypal_mail,
            'num_downloads'              => $this->num_downloads,
            'num_points'                 => $this->num_points,
            'amount'                     => $this->amount,
            'status'                     => $this->status,
            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at,
            'timestamp_masspay_start'    => $this->timestamp_masspay_start,
            'timestamp_masspay_last_ipn' => $this->timestamp_masspay_last_ipn,
            'last_paypal_ipn'            => $this->last_paypal_ipn,
            'last_paypal_status'         => $this->last_paypal_status,
            'payment_reference_key'      => $this->payment_reference_key,
            'payment_transaction_id'     => $this->payment_transaction_id,
            'payment_raw_message'        => $this->payment_raw_message,
            'payment_raw_error'          => $this->payment_raw_error,
            'payment_status'             => $this->payment_status,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(
            sprintf(
                '%s does not allow injection of an alternate input filter', __CLASS__
            )
        );
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //-----------------> hier come inputfiler
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }
}