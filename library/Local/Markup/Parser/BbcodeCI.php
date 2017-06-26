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
 *
 * Created: 23.06.2017
 */
class Local_Markup_Parser_BbcodeCI extends Zend_Markup_Parser_Bbcode
{
    /**
     * @inheritDoc
     */
    protected function _tokenize()
    {
        $attribute = '';

        while ($this->_pointer < $this->_valueLen) {
            switch ($this->_state) {
                case self::STATE_SCAN:
                    $matches = array();
                    $regex = '#\G(?<text>[^\[]*)(?<open>\[(?<name>[' . self::NAME_CHARSET . ']+)?)?#';
                    preg_match($regex, $this->_value, $matches, null, $this->_pointer);

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['text'])) {
                        $this->_buffer .= $matches['text'];
                    }

                    if (!isset($matches['open'])) {
                        // great, no tag, we are ending the string
                        break;
                    }
                    if (!isset($matches['name'])) {
                        $this->_buffer .= $matches['open'];
                        break;
                    }

                    $this->_temp = array(
                        'tag'        => '[' . strtolower($matches['name']),
                        'name'       => strtolower($matches['name']),
                        'attributes' => array()
                    );

                    if ($this->_pointer >= $this->_valueLen) {
                        // damn, no tag
                        $this->_buffer .= $this->_temp['tag'];
                        break 2;
                    }

                    if ($this->_value[$this->_pointer] == '=') {
                        $this->_pointer++;

                        $this->_temp['tag'] .= '=';
                        $this->_state = self::STATE_PARSEVALUE;
                        $attribute = $this->_temp['name'];
                    } else {
                        $this->_state = self::STATE_SCANATTRS;
                    }
                    break;
                case self::STATE_SCANATTRS:
                    $matches = array();
                    $regex = '#\G((?<end>\s*\])|\s+(?<attribute>[' . self::NAME_CHARSET . ']+)(?<eq>=?))#';
                    if (!preg_match($regex, $this->_value, $matches, null, $this->_pointer)) {
                        break 2;
                    }

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['end'])) {
                        if (!empty($this->_buffer)) {
                            $this->_tokens[] = array(
                                'tag'  => $this->_buffer,
                                'type' => Zend_Markup_Token::TYPE_NONE
                            );
                            $this->_buffer = '';
                        }
                        $this->_temp['tag'] .= $matches['end'];
                        $this->_temp['type'] = Zend_Markup_Token::TYPE_TAG;

                        $this->_tokens[] = $this->_temp;
                        $this->_temp = array();

                        $this->_state = self::STATE_SCAN;
                    } else {
                        // attribute name
                        $attribute = $matches['attribute'];

                        $this->_temp['tag'] .= $matches[0];

                        $this->_temp['attributes'][$attribute] = '';

                        if (empty($matches['eq'])) {
                            $this->_state = self::STATE_SCANATTRS;
                        } else {
                            $this->_state = self::STATE_PARSEVALUE;
                        }
                    }
                    break;
                case self::STATE_PARSEVALUE:
                    $matches = array();
                    $regex = '#\G((?<quote>"|\')(?<valuequote>.*?)\\2|(?<value>[^\]\s]+))#';
                    if (!preg_match($regex, $this->_value, $matches, null, $this->_pointer)) {
                        $this->_state = self::STATE_SCANATTRS;
                        break;
                    }

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['quote'])) {
                        $this->_temp['attributes'][$attribute] = $matches['valuequote'];
                    } else {
                        $this->_temp['attributes'][$attribute] = $matches['value'];
                    }
                    $this->_temp['tag'] .= $matches[0];

                    $this->_state = self::STATE_SCANATTRS;
                    break;
            }
        }

        if (!empty($this->_buffer)) {
            $this->_tokens[] = array(
                'tag'  => $this->_buffer,
                'type' => Zend_Markup_Token::TYPE_NONE
            );
        }
    }

}