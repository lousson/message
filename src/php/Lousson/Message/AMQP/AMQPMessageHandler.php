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

/** Interfaces: */
use Lousson\Message\AnyMessageFactory;
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURIFactory;
use Lousson\URI\AnyURI;

/** Dependencies: */
use AMQPExchange;
use Lousson\Message\AbstractMessageHandler;

/** Exceptions: */
use Lousson\Message\Error\RuntimeMessageError;

/**
 *  An AMQP message handler implementation
 *
 *  The Lousson\Message\AMQP\AMQPMessageHandler is a message handler
 *  implementation based on AMQP exchanges.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPMessageHandler extends AbstractMessageHandler
{
    /**
     *  Create a handler instance
     *
     *  Beside the required AMQP exchange and routing key, the constructor
     *  allows the provisioning of custom message and URI factory instances
     *  for the provider to operate on - instead of the builtin defaults.
     *
     *  @param  AMQPExchange        $exchange       The AMQP exchange
     *  @param  string              $routingKey     The AMQP routing key
     *  @param  AnyMessageFactory   $messageFactory The message factory
     *  @param  AnyURIFactory       $uriFactory     The URI factory
     */
    public function __construct(
        AMQPExchange $exchange,
        $routingKey,
        AnyMessageFactory $messageFactory = null,
        AnyURIFactory $uriFactory = null
    ) {
        parent::__construct($messageFactory, $uriFactory);

        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
    }

    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  @param  string              $uri        The event URI
     *  @param  AnyMessage          $message    The message instance
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case an argument is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    public function processMessage($uri, AnyMessage $message)
    {
        $uri = $this->fetchURI($uri);
        $attributes = $this->getAttributes($uri, $message);
        $content = $message->getContent();

        try {
            $status = $this->exchange->publish(
                $content,
                $this->routingKey,
                AMQP_NOPARAM,
                $attributes
            );
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $notice = "Could not process AMQP message: Caught $error";
            $code = RuntimeMessageError::E_UNKNOWN;
            throw new RuntimeMessageError($notice, $code, $error);
        }
    }

    /**
     *  Create message attributes
     *
     *  The getAttributes() method is used internally to prepare the array
     *  of attributes that is passed to the AMQPExchange::publish() method
     *  as 4th parameter.
     *
     *  @param  AnyURI              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message itself
     *
     *  @return array
     *          An array of message attributes is returned on success
     */
    protected function getAttributes(AnyURI $uri, AnyMessage $message)
    {
        $attributes = array(
            "content_type" => (string) $message->getType(),
            "x_resource_id" => (string) $uri->getLexical(),
        );

        return $attributes;
    }

    /**
     *  The AMQP exchange to operate on
     *
     *  @var \AMQPExchange
     */
    private $exchange;

    /**
     *  The AMQP routing key for publishing messages
     *
     *  @var string
     */
    private $routingKey;
}
