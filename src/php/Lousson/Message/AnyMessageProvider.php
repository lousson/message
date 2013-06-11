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
 *  Lousson\Message\AnyMessageProvider interface definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Dependencies: */
use Lousson\Message\AnyMessage;

/**
 *  An interface for message provider
 *
 *  The AnyMessageProvider interface declares an API for message provider
 *  implementations in general.
 *
 *  Message providers are data sources to access messages with a pull API
 *  (rather than message handlers, which expect to get the data pushed to
 *  them). They also implement a reporting mechanism to flag messages as
 *  acknowledged or discarded, which allows e.g. an implementation-specific
 *  error handling to restore status or report back to another entity.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
interface AnyMessageProvider
{
    /**
     *  The bitmask fetch() and fetchMessage() use by default
     */
    const FETCH_DEFAULT = 0x00;

    /**
     *  A bitmask to request a token-based transaction
     *
     *  @var int
     */
    const FETCH_CONFIRM = 0x02;

    /**
     *  The bitmask for acknowledge() used by default
     *
     *  @var int
     */
    const ACK_DEFAULT = 0x00;

    /**
     *  The bitmask for discard() used by default
     *
     *  @var int
     */
    const DISC_DEFAULT = 0x00;

    /**
     *  A bitmask for discard() requiring the message to get re-queued
     *
     *  @var int
     */
    const DISC_REQUEUE = 0x01;

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
     *          Raised in case retrieving the next message has failed
     */
    public function fetch(
        $uri, $flags = self::FETCH_DEFAULT, &$token = null
    );

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
     *          Raised in case the acknowledgement has failed
     */
    public function acknowledge($token, $flags = self::ACK_DEFAULT);

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
     *          Raised in case discarding the message has failed
     */
    public function discard($token, $flags = self::DISC_DEFAULT);
}

