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
 *  Lousson\Message\Callback\CallbackMessageHandlerTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Callback;

/** Interfaces: */
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandlerTest;
use Lousson\Message\Callback\CallbackMessageHandler;
use Psr\Log\NullLogger;

/**
 *  A test case for the callback message handler
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class CallbackMessageHandlerTest extends AbstractMessageHandlerTest
{
    /**
     *  Obtain a message handler instance
     *
     *  The getMessageHandler() method returns an instance of the message
     *  handler class that is to be tested.
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler instance is returned on success
     */
    public function getMessageHandler()
    {
        $callback = function(AnyURI $uri, AnyMessage $message) {
            CallbackMessageHandlerTest::$uri = $uri;
            CallbackMessageHandlerTest::$message = $message;
        };

        $handler = new CallbackMessageHandler($callback);
        return $handler;
    }

    /**
     *  The last URI processed
     *
     *  @var \Lousson\URI\AnyURI
     */
    private static $uri;

    /**
     *  The last message processed
     *
     *  @var \Lousson\Message\AnyMessage
     */
    private static $message;
}


