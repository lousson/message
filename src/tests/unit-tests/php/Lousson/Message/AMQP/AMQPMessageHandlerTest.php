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
 *  Lousson\Message\AMQP\AMQPMessageHandlerTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\AMQP;

/** Interfaces: */
use Lousson\Message\AnyMessage;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandlerTest;
use Lousson\Message\AMQP\AMQPMessageHandler;

/**
 *  A test case for the AMQP message handler
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class AMQPMessageHandlerTest extends AbstractMessageHandlerTest
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
        $exchange = $this->getMockBuilder("AMQPExchange")
            ->disableOriginalConstructor()
            ->getMock(array("publish"));

        $handler = new AMQPMessageHandler($exchange, "test-route");
        return $handler;
    }

    /**
     *  Test the processMessage() method
     *
     *  The testProcessMessageException() verifies that AMQP exceptions
     *  thrown in the provider's processMessage() method are catched and
     *  wrapped.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message itself
     *
     *  @dataProvider       provideValidProcessMessageParameters
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessMessageException($uri, AnyMessage $message)
    {
        $exchange = $this->getMockBuilder("AMQPExchange")
            ->disableOriginalConstructor()
            ->getMock(array("publish"));

        $exchange
            ->expects($this->any())
            ->method("publish")
            ->will($this->throwException(new \AMQPException));

        $handler = new AMQPMessageHandler($exchange, "test-route");
        $handler->processMessage($uri, $message);
    }
}


