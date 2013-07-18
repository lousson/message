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
 *  Lousson\Message\Generic\GenericMessageBroker class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Interfaces: */
use Lousson\Message\AnyMessageBroker;
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessageProvider;
use Lousson\Message\AnyMessage;

/**
 *  A generic message broker implementation
 *
 *  The Lousson\Message\Generic\GenericMessageBroker is an implementation
 *  of the AnyMessageBroker interface. It acts as a combined decorator for
 *  both; a message provider and a handler instance.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class GenericMessageBroker implements AnyMessageBroker
{
    /**
     *  Create a broker instance
     *
     *  The constructor requires the caller to provide both, a message
     *  provider and a handler instance, for the broker to delegate any
     *  invocation to.
     *
     *  @param  AnyMessageProvider  $provider   The message provider
     *  @param  AnyMessageHandler   $handler    The message handler
     */
    public function __construct(
        AnyMessageProvider $provider,
        AnyMessageHandler $handler
    ) {
        $this->provider = $provider;
        $this->handler = $handler;
    }

    /**
     *  Process message data
     *
     *  The process() method is used to invoke the logic that processes
     *  the message $data, a byte sequence of the given mime- or media-
     *  $type, according to the event $uri provided. If the $type is not
     *  provided, implementations should assume "application/octet-stream"
     *  or may attempt to detect it.
     *
     *  @param  string              $uri        The event URI
     *  @param  string              $data       The message data
     *  @param  string              $type       The media type
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
    public function process($uri, $data, $type = null)
    {
        $this->handler->process($uri, $data, $type);
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
        $this->handler->processMessage($uri, $message);
    }

    /**
     *  Retrieve message instances
     *
     *  The fetch() method is used to obtain the next message that is
     *  associated with the given event $uri. The $flags parameter can
     *  be used to request special behavior:
     *
     *- AnyMessageProvider::FETCH_CONFIRM
     *  Populate the $token reference for acknowledge() or discard()
     *
     *  @param  string              $uri        The event URI
     *  @param  int                 $flags      The option bitmask
     *  @param  mixed               $token      The delivery token
     *
     *  @return \Lousson\Message\AnyMessage
     *          A message instance is returned on success, or NULL in
     *          case no more messages are available for the given $uri
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
    public function fetch(
        $uri, $flags = self::FETCH_DEFAULT, &$token = null
    ) {
        $message = $this->provider->fetch($uri, $flags, $token);
        return $message;
    }

    /**
     *  Acknowledge a message
     *
     *  The acknowledge() method is used to tag a message (formerly
     *  received via fetchToken() on the same instance) as acknowledged.
     *  This is usually done after the processing of the message, when one
     *  knows that no further errors can occur.
     *
     *  The optional $flags parameter can be used to request special
     *  behavior (none defined yet).
     *
     *  @param  mixed               $token      The delivery token
     *  @param  int                 $flags      The option bitmask
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
    public function acknowledge($token, $flags = self::ACK_DEFAULT)
    {
        $this->provider->acknowledge($token, $flags);
    }

    /**
     *  Discard a message
     *
     *  The discard() method is used to tag a message (formerly received
     *  via fetch() or fetchToken() on the same instance) as discarded.
     *  This is usually done if either the message was invalid or some
     *  internal error occured (in combination with DISC_REQUEUE).
     *  behavior:
     *
     *- AnyMessageProvider::DISC_REQUEUE
     *  Re-queue the message for the next call to fetch()
     *
     *  @param  mixed               $token      The delivery token
     *  @param  int                 $flags      The option bitmask
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
    public function discard($token, $flags = self::DISC_DEFAULT)
    {
        $this->provider->discard($token, $flags);
    }
}

