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

namespace Application\Model\Interfaces;


use Library\Payment\ResponseInterface;

interface PlingsInterface extends BaseInterface
{
    /**
     * Pling a project.
     *
     * @param ResponseInterface $payment_response
     * @param int               $member_id  Id of the Sender
     * @param int               $project_id Id of the receiving project
     * @param float             $amount     amount plings/dollars
     * @param string|null       $comment    Comment from the buyer
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function createNewPlingFromResponse($payment_response, $member_id, $project_id, $amount, $comment = null);

    /**
     * Mark plings as payed.
     * So they can be used to pling.
     *
     * @param ResponseInterface $payment_response
     *
     */
    public function activatePlingsFromResponse($payment_response);

    /**
     * @param ResponseInterface $payment_response
     */
    public function deactivatePlingsFromResponse($payment_response);

    /**
     * @param ResponseInterface $payment_response
     *
     */
    public function fetchPlingFromResponse($payment_response);

    /**
     * @param ResponseInterface $payment_response
     */
    public function updatePlingTransactionStatusFromResponse($payment_response);

    /**
     * pling a project.
     *
     * @param int $member_id
     *            Pling-Geber
     * @param int $project_id
     *            Pling-Empfänger
     * @param int $amount
     *
     * @return mixed
     */
    public function pling($member_id, $project_id, $amount = 0);

    /**
     * @param int $projectId
     *
     * @return int
     */
    public function getCountPlingsForProject($projectId);

    /**
     * @param int $projectId
     *
     * @return float
     */
    public function getAmountPlingsForProject($projectId);

    /**
     * @param int $projectId
     *
     */
    public function getSupporterForProjectId($projectId);

    /**
     * @param int $projectId
     *
     */
    public function getSupporterWithPlingsForProjectId($projectId);

    /**
     * @param int $project_id
     *
     * @return mixed
     */
    public function getPlingersCountForProject($project_id);

    /**
     * @param int $_projectId
     *
     * @return mixed
     */
    public function getCount($_projectId);

    /**
     * @param int        $projectId
     * @param null       $limit
     * @param null|array $forbidden
     *
     * @deprecated
     */
    public function getCommentsForProject($projectId, $limit = null);

    /**
     * @param int      $projectId
     * @param int|null $limit
     *
     * @return null|array
     */
    public function getDonationsForProject($projectId, $limit = null);

    /**
     * @param int  $projectId
     * @param null $limit
     * @param bool $randomizeOrder
     *
     */
    public function getProjectSupporters($projectId, $limit = null, $randomizeOrder = false);

    /**
     * @param int $projectId
     *
     * @return int
     */
    public function getCountSupporters($projectId);

    /**
     * @param $projectId
     *
     * @deprecated
     */
    public function getLatestPling($projectId);

    public function fetchTotalAmountSupported();

    /**
     * @param $limit
     *
     */
    public function fetchRecentDonations($limit = null);

    /**
     * @param int $member_id
     *
     */
    public function fetchRecentDonationsForUser($member_id);

    public function setAllPlingsForUserDeleted($member_id);

    public function setAllPlingsForUserActivated($member_id);
}