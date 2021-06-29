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

namespace Application\View\Helper;

use Application\Model\Repository\MemberSettingValueRepository;
use Application\Model\Service\AuthManager;
use Exception;
use Laminas\View\Helper\AbstractHelper;

class MemberSettingItem extends AbstractHelper
{
    /**
     * @var AuthManager
     */
    private $auth_manager;

    /**
     * @var MemberSettingValueRepository
     */
    private $member_setting;

    /**
     * Constructor.
     *
     * @param AuthManager                  $currentUserService
     * @param MemberSettingValueRepository $memberSetting
     */
    public function __construct(AuthManager $currentUserService, MemberSettingValueRepository $memberSetting)
    {
        $this->auth_manager = $currentUserService;
        $this->member_setting = $memberSetting;
    }

    /**
     * @param integer $setting_id
     * @param bool    $use_cached_User
     *
     * @return array
     * @throws Exception
     */
    public function __invoke($setting_id, $use_cached_User = true)
    {
        return $this->member_setting->fetchMemberSettingItem($this->auth_manager->getCurrentUser($use_cached_User)->member_id, $setting_id);
    }

}