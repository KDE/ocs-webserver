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
class Queue_CommandController extends Local_Controller_Action_CliAbstract
{

    const DEFAULT_MSG_TIMEOUT = 600000; // default timeout in microseconds
    const DEFAULT_MSG_COUNT = 1;
    const QUEUE_TYPE = 'validate';

    /** @var  Zend_Queue */
    protected $queue;
    /** @var  Zend_Config */
    protected $config;
    /** @var int $timeout */
    protected $timeout;
    /** @var int $message_count */
    protected $message_count;

    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array()
    ) {
        parent::__construct($request, $response, $invokeArgs);
        $this->config = Zend_Registry::get('config');
        $this->timeout = isset($this->config->queue->general->timeout) ? $this->config->queue->general->timeout
            : self::DEFAULT_MSG_TIMEOUT;
        $this->message_count =
            isset($this->config->queue->general->message_count) ? $this->config->queue->general->message_count
                : self::DEFAULT_MSG_COUNT;
    }

    public function runAction()
    {
        $queue = $this->initQueue($this->getParam('q'));

        /** @var Zend_Queue_Message_Iterator $messages */
        $messages = $queue->receive($this->message_count, $this->timeout);
        /** @var Zend_Queue_Message $message */
        foreach ($messages as $message) {
            $cmdObject = unserialize($message->body);
            if ($cmdObject instanceof Local_Queue_CommandInterface) {
                try {
                    $cmdObject->doCommand();
                } catch (Exception $e) {
                    Zend_Registry::get('logger')->err(__METHOD__ . " - " . PHP_EOL . 'MESSAGE::    ' . $e->getMessage()
                        . PHP_EOL . 'ENVIRONMENT::' . APPLICATION_ENV . PHP_EOL . 'TRACE_STRING::' . PHP_EOL
                        . $e->getTraceAsString() . print_r($cmdObject, true) . PHP_EOL)
                    ;
                }
                $queue->deleteMessage($message);
            } else {
                Zend_Registry::get('logger')->err(__METHOD__ . " - Unknown command - " . print_r($message->body, true)
                    . PHP_EOL)
                ;

                $queue->deleteMessage($message);
                trigger_error('Unknown command: ' . print_r($message->body, true), E_USER_ERROR);
            }
        }
    }

    /**
     * @param string $identifier
     *
     * @return Zend_Queue
     */
    protected function initQueue($identifier)
    {
        return Local_Queue_Factory::getQueue($identifier);
    }

}