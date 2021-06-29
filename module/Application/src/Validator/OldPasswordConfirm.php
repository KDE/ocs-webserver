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

namespace Application\Validator;

use Laminas\Validator\AbstractValidator;
use Library\Tools\PasswordEncrypt;

/**
 * Class OldPasswordConfirm
 *
 * @package Application\Validator
 */
class OldPasswordConfirm extends AbstractValidator
{
    const NOT_MATCH = 'notmatch';
    const IS_EMPTY = 'empty';
    /**
     * Validation failure messages.
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_MATCH => 'Current password isn\'t correct',
        self::IS_EMPTY  => 'Please enter your old Password',
    );

    protected $options = array(
        'memberRepository' => null,
    );

    /**
     * Constructor.
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        // Set filter options (if provided).
        if (is_array($options)) {
            if (isset($options['memberRepository'])) {
                $this->options['memberRepository'] = $options['memberRepository'];
            }
        }
        // Call the parent class constructor
        parent::__construct($options);
    }

    /**
     * Check if user exists.
     */
    public function isValid($value)
    {
        if (!isset($value) || $value == '') {
            $this->error(self::IS_EMPTY);

            return false;
        }

        $value = (string)$value;
        $memberRepository = $this->options['memberRepository'];
        $user = $memberRepository->fetchById($GLOBALS['ocs_user']->member_id);
        if (PasswordEncrypt::get($value, $user->password_type) == $user->password) {
            return true;
        }
        $this->error(self::NOT_MATCH);

        return false;
    }
}
