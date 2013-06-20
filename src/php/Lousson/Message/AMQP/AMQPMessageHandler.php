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
 *  Lousson\Message\AMQP\AMQPMessageHandler class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Benjamin Schneider <benjamin.schneider.de at gmail.com>
 *  @filesource
 */
namespace Lousson\Message\AMQP;

/** Dependencies: */
use Lousson\Message\AMQP\Impl\AMQPObjectsFactory;
use Lousson\Message\AMQP\URI\AMQPURIParser;
use Lousson\Message\Generic\GenericMessageHandler;

/** Interfaces: */
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURI;
use Lousson\URI\AnyURIException;

/** Exceptions: */
use Lousson\Message\AMQP\Error\AMQPRuntimeError;
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;

/**
 *  Message handler implementation for publishing messages into an AMQP exchange
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPMessageHandler extends GenericMessageHandler
{
    /**
     *  Create a new instance of the AMQP message handler.
     *
     *  @param  AnyURI              $uri
     *  @param  AMQPURIParser       $amqpURIParser
     *  @param  AMQPObjectsFactory  $amqpObjectsFactory
     */
    public function __construct(
        AnyURI $uri,
        AMQPURIParser $amqpURIParser,
        AMQPObjectsFactory $amqpObjectsFactory
    )
    {
        $this->uri = $uri;
        $this->amqpURIParser = $amqpURIParser;
        $this->amqpObjectsFactory = $amqpObjectsFactory;
    }

    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  @param  string     $uri        The event URI
     *  @param  AnyMessage $message    The message instance
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case processing the message has failed
     */
    public function processMessage($uri, AnyMessage $message)
    {
        $uri = $this->fetchURI($uri);

        try {
            $amqpURI = $this->amqpURIParser->parseAnyURI($uri);
        }
        catch (AnyURIException $error) {
            $message = "Parsing the URI $uri failed";
            $code = AnyURIException::E_INVALID;
            throw new InvalidMessageError($message, $code, $error);
        }

        $exchangeName = $amqpURI->getExchange();
        if (!$exchangeName) {
            $message = "The given URI $uri contains no exchange information.";
            throw new InvalidMessageError($message);
        }

        $routingKey = $amqpURI->getRoutingKey();
        if (!$routingKey) {
            $message = "The given URI $uri contains no routing key.";
            throw new InvalidMessageError($message);
        }

        try {
            $exchange = $this->amqpObjectsFactory->createExchange($exchangeName);
            if (!$exchange->publish($message->getContent(), $routingKey)) {
                $message = 'Publishing the message failed';
                throw new RuntimeMessageError($message);
            }
        }
        catch (AMQPRuntimeError $error) {
            $message = 'Impl AMQP error';
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }

    }

    /**
     *  @var AMQPObjectsFactory
     */
    private $amqpObjectsFactory;

    /**
     *  @var AMQPURIParser
     */
    private $amqpURIParser;

    /**
     *  @var AnyURI
     */
    private $uri;
}