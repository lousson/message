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
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Interfaces: */
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandlerTest;
use Lousson\Message\Generic\GenericMessageHandler;
use Lousson\URI\Builtin\BuiltinURIFactory;
use Psr\Log\NullLogger;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;
use Lousson\Message\Error\MessageRuntimeError;

/**
 *  A test case for the generic message handler
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
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
        $innerMethods = array("processMessage", "process");
        $test = $this;
        $innerCallback = function($uri, $msg) use ($test) {
            $isURI = $test->isInstanceOf("Lousson\\URI\\AnyURI");
            $isString = $test->isType("string");
            $test->assertThat($uri, $test->logicalOr($isString, $isURI));
            $test->assertInstanceOf("Lousson\\Message\\AnyMessage", $msg);
        };

        $inner = $this->getMock(self::I_HANDLER, $innerMethods);
        $inner
            ->expects($this->any())
            ->method("processMessage")
            ->will($this->returnCallback($innerCallback));

        $resolverMethods = array("resolveHandler", "resolveProvider");
        $factory = new BuiltinURIFactory();
        $resolverCallback = function(&$uri) use ($factory, $inner) {
            try {
                $factory->getURI($uri);
                return $inner;
            }
            catch (\Lousson\URI\AnyURIException $error) {
                throw new MessageRuntimeError($error->getMessage());
            }
        };

        $resolver = $this->getMock(self::I_RESOLVER, $resolverMethods);
        $resolver
            ->expects($this->any())
            ->method("resolveHandler")
            ->will($this->returnCallback($resolverCallback));

        $outer = new GenericMessageHandler($resolver);
        return $outer;
    }

    /**
     *  Test the process() method
     *
     *  The testProcessError() method is a test case to verify that the
     *  implementation handles message exceptions raised by the handler
     *  the resolver has returned.
     *
     *  @expectedException  Lousson\Message\Error\MessageArgumentError
     *  @test
     *
     *  @throws \Lousson\Message\Error\MessageArgumentError
     *          Raised if the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessError()
    {
        $handler = $this->getMock(self::I_HANDLER);
        $handler
            ->expects($this->once())
            ->method("processMessage")
            ->will($this->throwException(new MessageArgumentError));

        $resolver = $this->getMock(self::I_RESOLVER);
        $resolver
            ->expects($this->once())
            ->method("resolveHandler")
            ->will($this->returnValue($handler));

        $handler = new GenericMessageHandler($resolver);
        $handler->process("urn:foo:bar", "foo? bar! baz.");
    }

    /**
     *  Test the process() method
     *
     *  The testProcessException() method is a test case to verify that the
     *  implementation does not violate the message handler interface when
     *  the resolved handler raises an invalid exception.
     *
     *  @expectedException  Lousson\Message\Error\MessageRuntimeError
     *  @test
     *
     *  @throws \Lousson\Message\Error\MessageRuntimeError
     *          Raised if the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessException()
    {
        $handler = $this->getMock(self::I_HANDLER);
        $handler
            ->expects($this->once())
            ->method("processMessage")
            ->will($this->throwException(new \DomainException));

        $resolver = $this->getMock(self::I_RESOLVER);
        $resolver
            ->expects($this->once())
            ->method("resolveHandler")
            ->will($this->returnValue($handler));

        $handler = new GenericMessageHandler($resolver);
        $handler->process("urn:foo:bar", "foo? bar! baz.");
    }

    /**
     *  Test the process() method
     *
     *  The testProcessException() method is a test case to verify that the
     *  implementation handles NULL return values from the message resolver
     *  in the proper way.
     *
     *  @expectedException  Lousson\Message\Error\MessageRuntimeError
     *  @test
     *
     *  @throws \Lousson\Message\Error\MessageRuntimeError
     *          Raised if the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessMissing()
    {
        $resolver = $this->getMock(self::I_RESOLVER);
        $handler = new GenericMessageHandler($resolver);
        $handler->process("urn:foo:bar", "foo? bar! baz.");
    }
}

