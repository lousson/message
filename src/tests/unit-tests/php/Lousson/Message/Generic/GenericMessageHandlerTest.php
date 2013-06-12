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
 *  Lousson\Message\Generic\GenericMessageHandlerTest class definition
 *
 *  @package    org.lousson.record
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandlerTest;
use Lousson\Message\Generic\GenericMessageHandler;

/** Exceptions: */
use Lousson\Message\Error\RuntimeMessageError;
use DomainException;

/**
 *  A test case for the generic message handler class
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.record
 */
final class GenericMessageHandlerTest extends AbstractMessageHandlerTest
{
    /**
     *  Obtain a message handler instance
     *
     *  The getMessageHandler() method returns an instance of the message
     *  handler class that is to be tested.
     *
     *  @return \Lousson\Message\Generic\GenericMessageHandler
     *          A message handler instance is returned on success
     */
    public function getMessageHandler()
    {
        $handler = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageHandler",
            array("processMessage")
        );

        return $handler;
    }

    /**
     *  Test behavior in case of an URI factory error
     *
     *  The testGetURIFactoryError() method is a test case for scenarios
     *  where the getURIFactory() method has been overridden and now either
     *  raises a non-message exception itself or returns a factory that
     *  does so.
     *
     *  @expectedException          Lousson\Message\AnyMessageException
     *  @test
     */
    public function testGetURIFactoryError()
    {
        $handler = $this->getHandlerMock("getURIFactory");
        $handler
            ->expects($this->once())
            ->method("getURIFactory")
            ->will($this->throwException(new DomainException));

        $uri = $this->getMessageURI();
        $handler->process($uri, null, null);
    }

    /**
     *  Test behavior in case of a valid message factory error
     *
     *  The testGetMessageFactoryValidError() method is a test case for
     *  the scenario of an overridden getMessageFactory() method that
     *  either raises a message exception itself or when the factory does
     *  so.
     *
     *  @expectedException          Lousson\Message\AnyMessageException
     *  @test
     */
    public function testGetMessageFactoryValidError()
    {
        $handler = $this->getHandlerMock("getMessageFactory");
        $handler
            ->expects($this->once())
            ->method("getMessageFactory")
            ->will($this->throwException(new RuntimeMessageError));

        $uri = $this->getMessageURI();
        $handler->process($uri, null, null);
    }

    /**
     *  Test behavior in case of an invalid message factory error
     *
     *  The testGetMessageFactoryInvalidError() method is a test case
     *  for the scenario of an overridden getMessageFactory() method that
     *  either raises a non-message exception itself or returns a factory
     *  that does so.
     *
     *  @expectedException          Lousson\Message\AnyMessageException
     *  @test
     */
    public function testGetMessageFactoryInvalidError()
    {
        $handler = $this->getHandlerMock("getMessageFactory");
        $handler
            ->expects($this->once())
            ->method("getMessageFactory")
            ->will($this->throwException(new DomainException));

        $uri = $this->getMessageURI();
        $handler->process($uri, null, null);
    }
}

