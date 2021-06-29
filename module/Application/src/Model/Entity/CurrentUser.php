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


use Exception;
use Laminas\Config\Config;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterInterface;

/**
 * This class represents a registered user.
 */
class CurrentUser extends Member
{

    public $email_verification_value;
    public $email_checked;
    public $external_id;
    public $roleName;
    public $projects;
    public $isSupporter;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->uuid = !empty($data['uuid']) ? $data['uuid'] : null;
        $this->username = !empty($data['username']) ? $data['username'] : null;
        $this->mail = !empty($data['mail']) ? $data['mail'] : null;
        $this->password = !empty($data['password']) ? $data['password'] : null;
        $this->password_type = !empty($data['password_type']) ? $data['password_type'] : null;
        $this->roleId = !empty($data['roleId']) ? $data['roleId'] : null;
        $this->avatar = !empty($data['avatar']) ? $data['avatar'] : null;
        $this->avatar_type_id = !empty($data['avatar_type_id']) ? $data['avatar_type_id'] : null;
        $this->type = !empty($data['type']) ? $data['type'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->mail_checked = !empty($data['mail_checked']) ? $data['mail_checked'] : null;
        $this->agb = !empty($data['agb']) ? $data['agb'] : null;
        $this->newsletter = !empty($data['newsletter']) ? $data['newsletter'] : null;
        $this->login_method = !empty($data['login_method']) ? $data['login_method'] : null;
        $this->firstname = !empty($data['firstname']) ? $data['firstname'] : null;
        $this->lastname = !empty($data['lastname']) ? $data['lastname'] : null;
        $this->street = !empty($data['street']) ? $data['street'] : null;
        $this->zip = !empty($data['zip']) ? $data['zip'] : null;
        $this->city = !empty($data['city']) ? $data['city'] : null;
        $this->country = !empty($data['country']) ? $data['country'] : null;
        $this->phone = !empty($data['phone']) ? $data['phone'] : null;
        $this->last_online = !empty($data['last_online']) ? $data['last_online'] : null;
        $this->biography = !empty($data['biography']) ? $data['biography'] : null;
        $this->paypal_mail = !empty($data['paypal_mail']) ? $data['paypal_mail'] : null;
        $this->paypal_valid_status = !empty($data['paypal_valid_status']) ? $data['paypal_valid_status'] : null;
        $this->wallet_address = !empty($data['wallet_address']) ? $data['wallet_address'] : null;
        $this->dwolla_id = !empty($data['dwolla_id']) ? $data['dwolla_id'] : null;
        $this->main_project_id = !empty($data['main_project_id']) ? $data['main_project_id'] : null;
        $this->profile_image_url = !empty($data['profile_image_url']) ? $data['profile_image_url'] : null;
        $this->profile_image_url_bg = !empty($data['profile_image_url_bg']) ? $data['profile_image_url_bg'] : null;
        $this->profile_img_src = !empty($data['profile_img_src']) ? $data['profile_img_src'] : null;
        $this->social_username = !empty($data['social_username']) ? $data['social_username'] : null;
        $this->social_user_id = !empty($data['social_user_id']) ? $data['social_user_id'] : null;
        $this->gravatar_email = !empty($data['gravatar_email']) ? $data['gravatar_email'] : null;
        $this->facebook_username = !empty($data['facebook_username']) ? $data['facebook_username'] : null;
        $this->twitter_username = !empty($data['twitter_username']) ? $data['twitter_username'] : null;
        $this->link_facebook = !empty($data['link_facebook']) ? $data['link_facebook'] : null;
        $this->link_twitter = !empty($data['link_twitter']) ? $data['link_twitter'] : null;
        $this->link_website = !empty($data['link_website']) ? $data['link_website'] : null;
        $this->link_google = !empty($data['link_google']) ? $data['link_google'] : null;
        $this->link_github = !empty($data['link_github']) ? $data['link_github'] : null;
        $this->validated_at = !empty($data['validated_at']) ? $data['validated_at'] : null;
        $this->validated = !empty($data['validated']) ? $data['validated'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
        $this->source_id = !empty($data['source_id']) ? $data['source_id'] : null;
        $this->source_pk = !empty($data['source_pk']) ? $data['source_pk'] : null;
        $this->pling_excluded = !empty($data['pling_excluded']) ? $data['pling_excluded'] : null;
        $this->password_old = !empty($data['password_old']) ? $data['password_old'] : null;
        $this->password_type_old = !empty($data['password_type_old']) ? $data['password_type_old'] : null;
        $this->username_old = !empty($data['username_old']) ? $data['username_old'] : null;
        $this->mail_old = !empty($data['mail_old']) ? $data['mail_old'] : null;

        $this->email_verification_value = !empty($data['email_verification_value']) ? $data['email_verification_value'] : null;
        $this->email_checked = !empty($data['email_checked']) ? $data['email_checked'] : null;
        $this->external_id = !empty($data['external_id']) ? $data['external_id'] : null;
        $this->roleName = !empty($data['roleName']) ? $data['roleName'] : null;
        $this->projects = !empty($data['projects']) ? $data['projects'] : null;
        $this->isSupporter = !empty($data['isSupporter']) ? $data['isSupporter'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'member_id'                => $this->member_id,
            'uuid'                     => $this->uuid,
            'username'                 => $this->username,
            'mail'                     => $this->mail,
            'password'                 => $this->password,
            'password_type'            => $this->password_type,
            'roleId'                   => $this->roleId,
            'avatar'                   => $this->avatar,
            'avatar_type_id'           => $this->avatar_type_id,
            'type'                     => $this->type,
            'is_active'                => $this->is_active,
            'is_deleted'               => $this->is_deleted,
            'mail_checked'             => $this->mail_checked,
            'agb'                      => $this->agb,
            'newsletter'               => $this->newsletter,
            'login_method'             => $this->login_method,
            'firstname'                => $this->firstname,
            'lastname'                 => $this->lastname,
            'street'                   => $this->street,
            'zip'                      => $this->zip,
            'city'                     => $this->city,
            'country'                  => $this->country,
            'phone'                    => $this->phone,
            'last_online'              => $this->last_online,
            'biography'                => $this->biography,
            'paypal_mail'              => $this->paypal_mail,
            'paypal_valid_status'      => $this->paypal_valid_status,
            'wallet_address'           => $this->wallet_address,
            'dwolla_id'                => $this->dwolla_id,
            'main_project_id'          => $this->main_project_id,
            'profile_image_url'        => $this->profile_image_url,
            'profile_image_url_bg'     => $this->profile_image_url_bg,
            'profile_img_src'          => $this->profile_img_src,
            'social_username'          => $this->social_username,
            'social_user_id'           => $this->social_user_id,
            'gravatar_email'           => $this->gravatar_email,
            'facebook_username'        => $this->facebook_username,
            'twitter_username'         => $this->twitter_username,
            'link_facebook'            => $this->link_facebook,
            'link_twitter'             => $this->link_twitter,
            'link_website'             => $this->link_website,
            'link_google'              => $this->link_google,
            'link_github'              => $this->link_github,
            'validated_at'             => $this->validated_at,
            'validated'                => $this->validated,
            'created_at'               => $this->created_at,
            'changed_at'               => $this->changed_at,
            'deleted_at'               => $this->deleted_at,
            'source_id'                => $this->source_id,
            'source_pk'                => $this->source_pk,
            'pling_excluded'           => $this->pling_excluded,
            'password_old'             => $this->password_old,
            'password_type_old'        => $this->password_type_old,
            'username_old'             => $this->username_old,
            'mail_old'                 => $this->mail_old,
            'email_verification_value' => $this->email_verification_value,
            'email_checked'            => $this->email_checked,
            'external_id'              => $this->external_id,
            'roleName'                 => $this->roleName,
            'projects'                 => $this->projects,
            'isSupporter'              => $this->isSupporter,
        ];
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new Exception(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }

    public function isAdmin()
    {
        return 'admin' == strtolower($this->roleName);
    }

    public function isModerator()
    {
        return 'moderator' == strtolower($this->roleName);
    }

    public function hasIdentity()
    {
        return empty($this->member_id) ? false : true;
    }

    public function isOwner($project_id)
    {
        if (!is_array($this->projects)) {
            return false;
        }
        if (array_key_exists($project_id, $this->projects)) {
            return true;
        }

        return false;
    }

    public function hasHivePassword()
    {
        return $this->password_type == 1;
    }

    /**
     * @param int|string $member_ident
     * @param string     $action
     * @param array      $params
     *
     * @return string
     */
    public function buildMemberUrl($member_ident, $action = '', $params = null)
    {
        /** @var Config $config */
        $config = $GLOBALS['ocs_config'];

        /** @var CurrentStore $store */
        $store = $GLOBALS['ocs_store'];
        $storeConfig = $store->getConfig();

        $uri_components = $this->parse_request_uri();
        $baseurl = '';

        $http_host = $uri_components['host'];
        $http_scheme = $uri_components['scheme'];
        $host = $http_scheme . '://' . $http_host;

        $storeId = null;
        if (isset($uri_components['store_id']) and !empty($uri_components['store_id'])) {
            $storeId = '/s/' . $uri_components['store_id'];
        }

        //20190214 ronald: removed to stay in context, if set in store config
        if (null != $storeConfig && $storeConfig->stay_in_context == false) {
            $baseurl = $config->settings->client->default->baseurl_member;
        } else {
            //20191125 but if the url is a real domain, then we do not need the /s/STORE_NAME
            if ($storeConfig->is_show_real_domain_as_url == true) {
                $baseurl = "{$host}";
            } else {
                //otherwiese send to baseurl_member
                //$baseurl = "{$host}{$storeId}";
                $baseurl = $config->settings->client->default->baseurl_member;
            }
        }


        $url_param = '';
        if (is_array($params)) {
            array_walk($params, create_function('&$i,$k', '$i="$k/$i/";'));
            $url_param = implode('/', $params);
        }

        if ($action != '') {
            $action = $action . '/';
        }

        $member_ident = strtolower($member_ident);
        $member_ident = urlencode($member_ident);

        $memberLink = "u";
        if (is_int($member_ident)) {
            $memberLink = "member";
        }

        return "{$baseurl}/{$memberLink}/{$member_ident}/{$action}{$url_param}";
    }

    private function parse_request_uri()
    {
        $ssl = (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true));
        $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');

        $port = $_SERVER['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;

        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;

        $store_id = null;
        $match = array();
        $success = preg_match('|/s/([\w-\.\s]*)/?|', urldecode($_SERVER['REQUEST_URI']), $match);
        if (1 == $success) {
            $store_id = $match[1];
        }

        return array('scheme' => $protocol, 'host' => $host, 'port' => $port, 'store_id' => $store_id);
    }

}
