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

namespace Application\Model\Repository;

use Application\Model\Entity\PayoutStatus;
use Application\Model\Interfaces\PaypalIpnInterface;
use Laminas\Db\Adapter\AdapterInterface;


class PaypalIpnRepository extends BaseRepository implements PaypalIpnInterface
{
    public function __construct(
        AdapterInterface $db
    ) {
        parent::__construct($db);
        $this->_name = "paypal_ipn";
        $this->_key = "id";
        $this->_prototype = PayoutStatus::class;
    }

    public function addFromIpnMessage($ipnArray, $raw)
    {
        $data = array();

        if (array_key_exists('txn_type', $ipnArray)) {
            if (array_key_exists('txn_type', $ipnArray)) {
                $data['txn_type'] = $ipnArray['txn_type'];
            }
            if (array_key_exists('ipn_track_id', $ipnArray)) {
                $data['ipn_track_id'] = $ipnArray['ipn_track_id'];
            }
            if (array_key_exists('txn_id', $ipnArray)) {
                $data['txn_id'] = $ipnArray['txn_id'];
            }
            if (array_key_exists('payer_email', $ipnArray)) {
                $data['payer_email'] = $ipnArray['payer_email'];
            }
            if (array_key_exists('payer_id', $ipnArray)) {
                $data['payer_id'] = $ipnArray['payer_id'];
            }
            if (array_key_exists('auth_amount', $ipnArray)) {
                $data['auth_amount'] = $ipnArray['auth_amount'];
            }
            if (array_key_exists('mc_currency', $ipnArray)) {
                $data['mc_currency'] = $ipnArray['mc_currency'];
            }
            if (array_key_exists('mc_fee', $ipnArray)) {
                $data['mc_fee'] = $ipnArray['mc_fee'];
            }
            if (array_key_exists('mc_gross', $ipnArray)) {
                $data['mc_gross'] = $ipnArray['mc_gross'];
            }
            if (array_key_exists('memo', $ipnArray)) {
                $data['memo'] = $ipnArray['memo'];
            }
            if (array_key_exists('payer_status', $ipnArray)) {
                $data['payer_status'] = $ipnArray['payer_status'];
            }
            if (array_key_exists('payment_date', $ipnArray)) {
                $data['payment_date'] = $ipnArray['payment_date'];
            }
            if (array_key_exists('payment_fee', $ipnArray)) {
                $data['payment_fee'] = $ipnArray['payment_fee'];
            }
            if (array_key_exists('payment_status', $ipnArray)) {
                $data['payment_status'] = $ipnArray['payment_status'];
            }
            if (array_key_exists('payment_type', $ipnArray)) {
                $data['payment_type'] = $ipnArray['payment_type'];
            }
            if (array_key_exists('pending_reason', $ipnArray)) {
                $data['pending_reason'] = $ipnArray['pending_reason'];
            }
            if (array_key_exists('reason_code', $ipnArray)) {
                $data['reason_code'] = $ipnArray['reason_code'];
            }
            if (array_key_exists('custom', $ipnArray)) {
                $data['custom'] = $ipnArray['custom'];
            }
            $data['raw'] = $raw;

        } else {
            if (array_key_exists('action_type', $ipnArray) && $ipnArray['action_type'] == 'PAY') {
                if (array_key_exists('transaction_type', $ipnArray)) {
                    $data['txn_type'] = $ipnArray['transaction_type'];
                }
                if (array_key_exists('tracking_id', $ipnArray)) {
                    $data['ipn_track_id'] = $ipnArray['tracking_id'];
                }
                if (array_key_exists('transaction[0].id', $ipnArray)) {
                    $data['txn_id'] = $ipnArray['transaction[0].id'];
                }
                if (array_key_exists('transaction[0].receiver', $ipnArray)) {
                    $data['payer_email'] = $ipnArray['transaction[0].receiver'];
                }
                if (array_key_exists('transaction[0].id_for_sender_txn', $ipnArray)) {
                    $data['payer_id'] = $ipnArray['transaction[0].id_for_sender_txn'];
                }
                if (array_key_exists('transaction[0].amount', $ipnArray)) {
                    $data['auth_amount'] = $ipnArray['transaction[0].amount'];
                }
                if (array_key_exists('memo', $ipnArray)) {
                    $data['memo'] = $ipnArray['memo'];
                }
                if (array_key_exists('transaction[0].status_for_sender_txn', $ipnArray)) {
                    $data['payer_status'] = $ipnArray['transaction[0].status_for_sender_txn'];
                }
                if (array_key_exists('reversal_date', $ipnArray)) {
                    $data['payment_date'] = $ipnArray['reversal_date'];
                }
                if (array_key_exists('transaction[0].status', $ipnArray)) {
                    $data['payment_status'] = $ipnArray['transaction[0].status'];
                }
                if (array_key_exists('transaction[0].paymentType', $ipnArray)) {
                    $data['payment_type'] = $ipnArray['transaction[0].paymentType'];
                }
                if (array_key_exists('transaction[0].pending_reason', $ipnArray)) {
                    $data['pending_reason'] = $ipnArray['transaction[0].pending_reason'];
                }
                if (array_key_exists('reason_code', $ipnArray)) {
                    $data['reason_code'] = $ipnArray['reason_code'];
                }
                if (array_key_exists('custom', $ipnArray)) {
                    $data['custom'] = $ipnArray['custom'];
                }
                $data['raw'] = $raw;
            } else {
                $data['raw'] = $raw;
            }
        }

        $this->insertOrUpdate($data);

    }

}
