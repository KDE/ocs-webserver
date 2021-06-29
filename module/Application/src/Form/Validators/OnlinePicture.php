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
 *
 */

namespace Application\Form\Validators;


use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\ErrorHandler;
use Laminas\Validator\Exception;
use Traversable;

class OnlinePicture extends \Laminas\Validator\AbstractValidator
{

    const INVALID = 'regexInvalid';
    const NOT_MATCH = 'regexNotMatch';
    const ERROROUS = 'regexErrorous';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given. Array expected",
        self::NOT_MATCH => "The input does not match against pattern '%pattern%'",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    ];

    /**
     * Sets validator options
     *
     * @param string|array|Traversable $pattern
     *
     * @throws Exception\InvalidArgumentException On missing 'pattern' parameter
     */
    public function __construct($pattern)
    {
        if (is_string($pattern)) {
            $this->setPattern($pattern);
            parent::__construct([]);

            return;
        }

        if ($pattern instanceof Traversable) {
            $pattern = ArrayUtils::iteratorToArray($pattern);
        }

        if (!is_array($pattern)) {
            throw new Exception\InvalidArgumentException('Invalid options provided to constructor');
        }

        if (!array_key_exists('pattern', $pattern)) {
            throw new Exception\InvalidArgumentException("Missing option 'pattern'");
        }

        $this->setPattern($pattern['pattern']);
        unset($pattern['pattern']);
        parent::__construct($pattern);
    }

    /**
     * Sets the pattern option
     *
     * @param string $pattern
     *
     * @return $this Provides a fluent interface
     * @throws Exception\InvalidArgumentException|\ErrorException if there is a fatal error in pattern matching
     */
    public function setPattern($pattern)
    {
        ErrorHandler::start();
        $this->pattern = (string)$pattern;
        $status = preg_match($this->pattern, "Test");
        $error = ErrorHandler::stop();

        if (false === $status) {
            throw new Exception\InvalidArgumentException(
                "Internal error parsing the pattern '{$this->pattern}'", 0, $error
            );
        }

        return $this;
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        if (!is_array($value)) {
            $this->error(self::INVALID);

            return false;
        }

        $this->setValue($value);
        $status = null;

        ErrorHandler::start();
        foreach ($value as $item) {
            $status = preg_match($this->pattern, $item);
            if (false === $status) {
                break;
            }
        }
        ErrorHandler::stop();
        if (false === $status) {
            $this->error(self::ERROROUS);

            return false;
        }

        if (!$status) {
            $this->error(self::NOT_MATCH);

            return false;
        }

        return true;
    }
}