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
 *  Lousson\Message\Builtin\BuiltinMessageProviderTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Builtin;

/** Interfaces: */
use Lousson\Message\AnyMessageProvider;

/** Dependencies: */
use Lousson\Message\AbstractMessageProviderTest;
use Lousson\Message\Callback\CallbackMessageProvider;
use Lousson\Message\Builtin\BuiltinMessageProvider;
use Psr\Log\NullLogger;

/**
 *  A test case for the builtin message provider
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class BuiltinMessageProviderTest extends AbstractMessageProviderTest
{
    /**
     *  Obtain a message provider instance
     *
     *  The getMessageProvider() method returns the message provider
     *  instance the test case shall operate with.
     *
     *  @param  string              $uri        The test message URI
     *  @param  array               $expected   The test messages
     *
     *  @return \Lousson\Message\Builtin\BuiltinMessageProvider
     *          A message provider instance is returned on success
     */
    public function getMessageProvider($uri, array $expected)
    {
        $builtin = function($uri) use(&$expected) {
            $message = array_shift($expected);
            return $message;
        };

        $inner = new CallbackMessageProvider($builtin);
        $inner->setLogger(new NullLogger());

        $resolverMethods = array("resolveProvider", "resolveHandler");
        $test = $this;
        $resolverCallback = function(&$uri) use ($test, $inner) {
            $isURI = $test->isInstanceOf("Lousson\\URI\\AnyURI");
            $isString = $test->isType("string");
            $test->assertThat($uri, $test->logicalOr($isString, $isURI));
            return $inner;
        };

        $resolver = $this->getMock(self::I_RESOLVER, $resolverMethods);
        $resolver
            ->expects($this->any())
            ->method("resolveProvider")
            ->will($this->returnCallback($resolverCallback));

        $outer = new BuiltinMessageProvider($resolver);
        return $outer;
    }

    /**
     *  Test the fetch() method
     *
     *  The testFetchException() method is a test case to verify that the
     *  implementation does not violate the message provider interface when
     *  the resolved provider raises an invalid exception.
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
    public function testFetchException()
    {
        $provider = $this->getMock(self::I_PROVIDER);
        $provider
            ->expects($this->once())
            ->method("fetch")
            ->will($this->throwException(new \DomainException));

        $resolver = $this->getMock(self::I_RESOLVER);
        $resolver
            ->expects($this->once())
            ->method("resolveProvider")
            ->will($this->returnValue($provider));

        $provider = new BuiltinMessageProvider($resolver);
        $provider->fetch("urn:foo:bar");
    }

    /**
     *  Test the fetch() method
     *
     *  The testFetchException() method is a test case to verify that the
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
    public function testFetchMissing()
    {
        $resolver = $this->getMock(self::I_RESOLVER);
        $provider = new BuiltinMessageProvider($resolver);
        $provider->fetch("urn:foo:bar");
    }

    /**
     *  Test the destructor
     *
     *  The testDestructor() method verifies that the cleanup in the
     *  BuiltinMessageProvider's destructor works as expected.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The fetch() flags
     *
     *  @dataProvider               provideValidFetchParameters
     *  @test
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDestructor(
        array $data,
        $uri,
        $flags = self::FETCH_DEFAULT
    ) {
        $flags |= self::FETCH_CONFIRM;
        $provider = $this->getMessageProvider($uri, $data);
        $index = 0;

        while($provider->fetch($uri, $flags, $token));
        $provider->discard($token, self::DISC_REQUEUE);
        $provider->__destruct();
        unset($provider);
    }
}

