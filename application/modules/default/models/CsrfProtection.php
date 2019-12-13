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
class Default_Model_CsrfProtection
{
    const validity = 3600;

    /**
     * @param Zend_Form $form
     * @param string    $csrf_salt_name
     * @param string    $field_name
     *
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     */
    public static function createCsrf($form, $csrf_salt_name, $field_name = "csrf")
    {
        /** @var Zend_Form_Element_Hash $element */
        $element = $form->createElement('hash', $field_name, array(
            'salt' => $csrf_salt_name
        ));
        //Create unique ID if you need to use some Javascript on the CSRF Element
        $element->setAttrib('id', $form->getName() . '_' . $element->getId());
        $element->setDecorators(array('ViewHelper'));
        $element->setSession(new Zend_Session_Namespace('login_csrf'));
        $form->addElement($element);

        return $element;
    }

    /**
     * @param $hash
     *
     * @return bool
     * @throws Zend_Exception
     * @throws Zend_Session_Exception
     */
    public static function validateCsrfToken($hash)
    {
        $session = new Zend_Session_Namespace();

        if (false === function_exists("hash_equals")) {
            $valid = self::hash_equals($session->crsf_token, $hash);
            Zend_Registry::get('logger')->debug(__METHOD__
                . PHP_EOL . ' - session csrf token: ' . print_r($session->crsf_token, true)
                . PHP_EOL . ' - form csrf token: ' . print_r($hash, true)
                . PHP_EOL . ' - crsf validation result: ' . (($valid === true) ? 'true' : 'false'));

            return $valid;
        }

        if (empty($session->crsf_token)) {
            return false;
        }
        $valid = hash_equals($session->crsf_token, $hash);
        Zend_Registry::get('logger')->debug(__METHOD__
                . PHP_EOL . ' - session csrf token: ' . print_r($session->crsf_token, true)
                . PHP_EOL . ' - form csrf token: ' . print_r($hash, true)
                . PHP_EOL . ' - crsf validation result: ' . (($valid === true) ? 'true' : 'false'));

        return $valid;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return bool
     * @author http://php.net/manual/en/function.hash-equals.php#usernotes  Cedric Van Bockhaven
     */
    private static function hash_equals($a, $b)
    {
        $ret = strlen($a) ^ strlen($b);
        $ret |= array_sum(unpack("C*", $a ^ $b));

        return !$ret;
    }

    /**
     * @return Zend_Form_Element_Hidden
     * @throws Zend_Form_Exception
     * @throws Zend_Session_Exception
     */
    public static function getFormCsrf($name = 'crsf_token')
    {
        $form_crsf = new Zend_Form_Element_Hidden($name);
        $form_crsf->setFilters(array('StringTrim'));
        $form_crsf->setRequired(true);
        $form_crsf->setDecorators(array('ViewHelper'));
        $form_crsf->setValue(self::getCsrfToken());

        return $form_crsf;
    }

    /**
     * @return mixed|string
     * @throws Zend_Session_Exception
     */
    public static function getCsrfToken()
    {
        $session = new Zend_Session_Namespace();
        if ($session->crsf_token AND $session->crsf_expire AND ($session->crsf_expire > microtime(true))) {
            return $session->crsf_token;
        }
        $session->crsf_expire = microtime(true) + self::validity;
        if (function_exists('mcrypt_create_iv')) {
            $session->crsf_token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        } else {
            $session->crsf_token = bin2hex(openssl_random_pseudo_bytes(32));
        }

        return $session->crsf_token;
    }

}