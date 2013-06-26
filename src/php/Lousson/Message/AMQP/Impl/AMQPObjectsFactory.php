<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 textwidth=75: *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Copyright (c) 2013, The Lousson Project                               *
 *                                                                       *
 * All rights reserved.                                                  *
 *                                                                       *
 * Redistribution and use in source and binary forms, with or without    *
 * modification, are permitted provided that the following conditions    *
 * are met:                                                              *
 *                                                                       *
 * 1) Redistributions of source code must retain the above copyright     *
 *    notice, this list of conditions and the following disclaimer.      *
 * 2) Redistributions in binary form must reproduce the above copyright  *
 *    notice, this list of conditions and the following disclaimer in    *
 *    the documentation and/or other materials provided with the         *
 *    distribution.                                                      *
 *                                                                       *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   *
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     *
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS     *
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE        *
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,            *
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES    *
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR    *
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)    *
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,   *
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)         *
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED   *
 * OF THE POSSIBILITY OF SUCH DAMAGE.                                    *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 *  Lousson\Message\AMQP\Impl\AMQPObjectsFactory class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Benjamin Schneider <benjamin.schneider.de at gmail.com>
 *  @filesource
 */
namespace Lousson\Message\AMQP\Impl;

use Lousson\Message\AMQP\Error\AMQPRuntimeError;

/**
 *  A factory for creating the AMQP PECL class instances.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPObjectsFactory
{
    /**
     *  @param  \AMQPConnection  $connection
     */
    public function __construct(\AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     *  Create a new AMQP channel.
     *
     *  @return \AMQPChannel
     *
     *  @throws AMQPRuntimeError
     *          Raised in case an internal AMQP error occurred.
     */
    public function createChannel()
    {
        try {

            return new \AMQPChannel($this->connection);
        }
        catch (\AMQPConnectionException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }
    }

    /**
     *  Create a new AMQP exchange with a given name.
     *
     *  @param  string  $name
     *
     *  @return \AMQPExchange
     *
     *  @throws AMQPRuntimeError
     *          Raised in case an internal AMQP error occurred.
     */
    public function createExchange($name)
    {
        $channel = $this->createChannel();

        try {
            $exchange = new \AMQPExchange($channel);
            $exchange->setName($name);
            // declare not allowed
            //$exchange->declareExchange();
        }
        catch (\AMQPChannelException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }
        catch (\AMQPConnectionException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }
        catch (\AMQPExchangeException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }

        return $exchange;
    }

    /**
     *  Create a new AMQP queue with a given name.
     *
     *  @param  string  $name
     *
     *  @return \AMQPQueue
     *
     *  @throws AMQPRuntimeError
     *          Raised in case an internal AMQP error occurred.
     */
    public function createQueue($name)
    {
        $channel = $this->createChannel();

        try {
            $queue = new \AMQPQueue($channel);
            $queue->setName($name);
        }
        catch (\AMQPChannelException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }
        catch (\AMQPConnectionException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }
        catch (\AMQPQueueException $error) {
            $message = 'Internal AMQP error';
            $code = AMQPRuntimeError::E_INTERNAL_ERROR;
            throw new AMQPRuntimeError($message, $code, $error);
        }

        return $queue;
    }

    /**
     *  @var \AMQPConnection
     */
    private $connection;
}
