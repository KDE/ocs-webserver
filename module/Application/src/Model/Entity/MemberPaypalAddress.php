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

class MemberPaypalAddress implements InputFilterAwareInterface
{
    // attributes

    public $id;
    public $member_id;
    public $paypal_address;
    public $is_active;
    public $name;
    public $address;
    public $currency;
    public $country_code;
    public $last_payment_status;
    public $last_payment_amount;
    public $last_transaction_id;
    public $last_transaction_event_code;
    public $created_at;
    public $changed_at;

    public function exchangeArray(array $data)
    {

        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->paypal_address = !empty($data['paypal_address']) ? $data['paypal_address'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->address = !empty($data['address']) ? $data['address'] : null;
        $this->currency = !empty($data['currency']) ? $data['currency'] : null;
        $this->country_code = !empty($data['country_code']) ? $data['country_code'] : null;
        $this->last_payment_status = !empty($data['last_payment_status']) ? $data['last_payment_status'] : null;
        $this->last_payment_amount = !empty($data['last_payment_amount']) ? $data['last_payment_amount'] : null;
        $this->last_transaction_id = !empty($data['last_transaction_id']) ? $data['last_transaction_id'] : null;
        $this->last_transaction_event_code = !empty($data['last_transaction_event_code']) ? $data['last_transaction_event_code'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'id'                          => $this->id,
            'member_id'                   => $this->member_id,
            'paypal_address'              => $this->paypal_address,
            'is_active'                   => $this->is_active,
            'name'                        => $this->name,
            'address'                     => $this->address,
            'currency'                    => $this->currency,
            'country_code'                => $this->country_code,
            'last_payment_status'         => $this->last_payment_status,
            'last_payment_amount'         => $this->last_payment_amount,
            'last_transaction_id'         => $this->last_transaction_id,
            'last_transaction_event_code' => $this->last_transaction_event_code,
            'created_at'                  => $this->created_at,
            'changed_at'                  => $this->changed_at,

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