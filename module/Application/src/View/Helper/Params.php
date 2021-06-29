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

namespace Application\View\Helper;

use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\RequestInterface;
use Laminas\View\Helper\AbstractHelper;

class Params extends AbstractHelper
{
    protected $request;

    protected $event;

    public function __construct(RequestInterface $request, MvcEvent $event)
    {
        $this->request = $request;
        $this->event = $event;
    }

    public function fromPost($param = null, $default = null)
    {
        if ($param === null) {
            return $this->request->getPost($param, $default)->toArray();
        }

        return $this->request->getPost($param, $default);
    }

    public function fromQuery($param = null, $default = null)
    {
        if ($param === null) {
            return $this->request->getQuery($param, $default)->toArray();
        }

        return $this->request->getQuery($param, $default);
    }

    public function fromRoute($param = null, $default = null)
    {
        if ($param === null) {
            return $this->event->getRouteMatch()->getParams();
        }

        if ($this->event->getRouteMatch()) {
            return $this->event->getRouteMatch()->getParam($param, $default);
        }

        return null;
    }
}