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

namespace Application\Model\Service\Entity;


class MailEntity
{
    private $body;
    private $fromMail;
    private $fromAlias;
    private $subject;
    private $receiverMail;
    private $receiverAlias;

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     *
     * @return MailEntity
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromMail()
    {
        return $this->fromMail;
    }

    /**
     * @param mixed $fromMail
     *
     * @return MailEntity
     */
    public function setFromMail($fromMail)
    {
        $this->fromMail = $fromMail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromAlias()
    {
        return $this->fromAlias;
    }

    /**
     * @param mixed $fromAlias
     *
     * @return MailEntity
     */
    public function setFromAlias($fromAlias)
    {
        $this->fromAlias = $fromAlias;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     *
     * @return MailEntity
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReceiverMail()
    {
        return $this->receiverMail;
    }

    /**
     * @param mixed $receiverMail
     *
     * @return MailEntity
     */
    public function setReceiverMail($receiverMail)
    {
        $this->receiverMail = $receiverMail;

        return $this;
    }

    public function getReceiverAlias()
    {
        return $this->receiverAlias;
    }

    public function setReceiverAlias($receiverAlias)
    {
        $this->receiverAlias = $receiverAlias;

        return $this;
    }
}