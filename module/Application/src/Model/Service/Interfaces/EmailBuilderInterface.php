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

namespace Application\Model\Service\Interfaces;

interface EmailBuilderInterface
{
    /**
     * @param string $templateName
     *
     * @return $this
     */
    public function withTemplate($templateName);

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setBodyText($text);

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject);

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setTemplateVar($name, $value);

    /**
     * @param string $mail
     *
     * @return $this
     */
    public function setReceiverMail($mail);

    /**
     * @param string $mail
     *
     * @return $this
     */
    public function setFromMail($mail);

    public function build();
}