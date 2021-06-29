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

namespace Application\Controller\Plugin;

use Application\Model\Entity\CurrentUser;
use Application\Model\Repository\MemberSettingValueRepository;
use Application\Model\Service\AuthManager;
use Exception;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use phpDocumentor\Reflection\Types\Integer;

/**
 * Class CurrentUserPlugin
 * This controller plugin is designed to let you get the currently logged in User entity
 * inside your controller.
 *
 * @package Application\Controller\Plugin
 */
class CurrentUserPlugin extends AbstractPlugin
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
    public function __construct(
        AuthManager $currentUserService,
        MemberSettingValueRepository $memberSetting
    ) {
        $this->auth_manager = $currentUserService;
        $this->member_setting = $memberSetting;
    }

    /**
     * Returns the current user or an empty user object if not logged in.
     *
     * @param bool $useCachedUser If true, the User entity is fetched only on the first call (and cached on subsequent
     *                            calls).
     *
     * @return CurrentUser
     * @throws Exception
     */
    public function __invoke($useCachedUser = true)
    {
        return $this->auth_manager->getCurrentUser($useCachedUser);
    }

    /**
     * Returns the current user or an empty user object if not logged in.
     *
     * @param bool $useCachedUser If true, the User entity is fetched only on the first call (and cached on subsequent
     *                            calls).
     *
     * @return CurrentUser
     * @throws Exception
     */
    public function get($useCachedUser = true)
    {
        return $this->auth_manager->getCurrentUser($useCachedUser);
    }

    /**
     * @param int $setting_id
     *
     * @return array
     * @throws Exception
     */
    public function fetchMemberSettingItem($setting_id)
    {
        return $this->member_setting->fetchMemberSettingItem(
            $this->auth_manager->getCurrentUser()->member_id, $setting_id
        );
    }

}