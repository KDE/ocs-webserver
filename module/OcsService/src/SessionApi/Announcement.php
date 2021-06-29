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

namespace OcsService\SessionApi;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilter;
use Psr\Http\Message\ServerRequestInterface;

class Announcement implements MiddlewareInterface
{
    /**
     * @var \Laminas\Session\AbstractContainer
     */
    private $session;

    public function __construct(\Laminas\Session\AbstractContainer $session)
    {
        /** @var \Laminas\Session\Container session */
        $this->session = $session;
    }

    public static function validateMd5($hash)
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $dummy = $request->getParsedBody();
        $inputFilter = $this->getInputFilter();
        $inputFilter->setData($dummy['announcement']);
        if ($inputFilter->isValid()) {
            $this->session->announcement_dismiss = $inputFilter->get('checksum')->getValue();

            return new JsonResponse(['status' => 'ok', 'msg' => 'success']);
        }

        return new JsonResponse(['status' => 'error', 'msg' => 'failure']);
    }

    /**
     * @return InputFilter
     */
    protected function getInputFilter()
    {
        $inputFilter = new InputFilter();

        $inputFilter->add(
            [
                'name'       => 'dismissed',
                'required'   => true,
                'filters'    => [
                    ['name' => 'Boolean'],
                ],
                'validators' => [],
            ]
        );

        $inputFilter->add(
            [
                'name'       => 'checksum',
                'required'   => true,
                'filters'    => [],
                'validators' => [
                    [
                        'name'    => 'Callback',
                        'options' => [
                            'callback' => [Announcement::class, 'validateMd5'],
                        ],
                    ],
                ],
            ]
        );

        return $inputFilter;
    }

}