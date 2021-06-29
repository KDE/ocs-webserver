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

class PaypalIpn implements InputFilterAwareInterface
{
    // attributes
    public $id;
    public $created_at;
    public $txn_type;
    public $ipn_track_id;
    public $txn_id;
    public $payer_email;
    public $payer_id;
    public $auth_amount;
    public $mc_currency;
    public $mc_fee;
    public $mc_gross;
    public $memo;
    public $payer_status;
    public $payment_date;
    public $payment_fee;
    public $payment_status;
    public $payment_type;
    public $pending_reason;
    public $reason_code;
    public $custom;
    public $raw;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->txn_type = !empty($data['txn_type']) ? $data['txn_type'] : null;
        $this->ipn_track_id = !empty($data['ipn_track_id']) ? $data['ipn_track_id'] : null;
        $this->txn_id = !empty($data['txn_id']) ? $data['txn_id'] : null;
        $this->payer_email = !empty($data['payer_email']) ? $data['payer_email'] : null;
        $this->payer_id = !empty($data['payer_id']) ? $data['payer_id'] : null;
        $this->auth_amount = !empty($data['auth_amount']) ? $data['auth_amount'] : null;
        $this->mc_currency = !empty($data['mc_currency']) ? $data['mc_currency'] : null;
        $this->mc_fee = !empty($data['mc_fee']) ? $data['mc_fee'] : null;
        $this->mc_gross = !empty($data['mc_gross']) ? $data['mc_gross'] : null;
        $this->memo = !empty($data['memo']) ? $data['memo'] : null;
        $this->payer_status = !empty($data['payer_status']) ? $data['payer_status'] : null;
        $this->payment_date = !empty($data['payment_date']) ? $data['payment_date'] : null;
        $this->payment_fee = !empty($data['payment_fee']) ? $data['payment_fee'] : null;
        $this->payment_status = !empty($data['payment_status']) ? $data['payment_status'] : null;
        $this->payment_type = !empty($data['payment_type']) ? $data['payment_type'] : null;
        $this->pending_reason = !empty($data['pending_reason']) ? $data['pending_reason'] : null;
        $this->reason_code = !empty($data['reason_code']) ? $data['reason_code'] : null;
        $this->custom = !empty($data['custom']) ? $data['custom'] : null;
        $this->raw = !empty($data['raw']) ? $data['raw'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'id'             => $this->id,
            'created_at'     => $this->created_at,
            'txn_type'       => $this->txn_type,
            'ipn_track_id'   => $this->ipn_track_id,
            'txn_id'         => $this->txn_id,
            'payer_email'    => $this->payer_email,
            'payer_id'       => $this->payer_id,
            'auth_amount'    => $this->auth_amount,
            'mc_currency'    => $this->mc_currency,
            'mc_fee'         => $this->mc_fee,
            'mc_gross'       => $this->mc_gross,
            'memo'           => $this->memo,
            'payer_status'   => $this->payer_status,
            'payment_date'   => $this->payment_date,
            'payment_fee'    => $this->payment_fee,
            'payment_status' => $this->payment_status,
            'payment_type'   => $this->payment_type,
            'pending_reason' => $this->pending_reason,
            'reason_code'    => $this->reason_code,
            'custom'         => $this->custom,
            'raw'            => $this->raw,

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