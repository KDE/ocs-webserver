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

/**
 * Class Plings
 *
 * @package Application\Model\Entity
 */
class Plings implements InputFilterAwareInterface
{
    // attributes
    public $id;
    public $member_id;
    public $project_id;
    public $status_id;
    public $create_time;
    public $pling_time;
    public $active_time;
    public $delete_time;
    public $amount;
    public $comment;
    public $payment_provider;
    public $payment_reference_key;
    public $payment_transaction_id;
    public $payment_raw_message;
    public $payment_raw_error;
    public $payment_status;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->status_id = !empty($data['status_id']) ? $data['status_id'] : null;
        $this->create_time = !empty($data['create_time']) ? $data['create_time'] : null;
        $this->pling_time = !empty($data['pling_time']) ? $data['pling_time'] : null;
        $this->active_time = !empty($data['active_time']) ? $data['active_time'] : null;
        $this->delete_time = !empty($data['delete_time']) ? $data['delete_time'] : null;
        $this->amount = !empty($data['amount']) ? $data['amount'] : null;
        $this->comment = !empty($data['comment']) ? $data['comment'] : null;
        $this->payment_provider = !empty($data['payment_provider']) ? $data['payment_provider'] : null;
        $this->payment_reference_key = !empty($data['payment_reference_key']) ? $data['payment_reference_key'] : null;
        $this->payment_transaction_id = !empty($data['payment_transaction_id']) ? $data['payment_transaction_id'] : null;
        $this->payment_raw_message = !empty($data['payment_raw_message']) ? $data['payment_raw_message'] : null;
        $this->payment_raw_error = !empty($data['payment_raw_error']) ? $data['payment_raw_error'] : null;
        $this->payment_status = !empty($data['payment_status']) ? $data['payment_status'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'id'                     => $this->id,
            'member_id'              => $this->member_id,
            'project_id'             => $this->project_id,
            'status_id'              => $this->status_id,
            'create_time'            => $this->create_time,
            'pling_time'             => $this->pling_time,
            'active_time'            => $this->active_time,
            'delete_time'            => $this->delete_time,
            'amount'                 => $this->amount,
            'comment'                => $this->comment,
            'payment_provider'       => $this->payment_provider,
            'payment_reference_key'  => $this->payment_reference_key,
            'payment_transaction_id' => $this->payment_transaction_id,
            'payment_raw_message'    => $this->payment_raw_message,
            'payment_raw_error'      => $this->payment_raw_error,
            'payment_status'         => $this->payment_status,


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