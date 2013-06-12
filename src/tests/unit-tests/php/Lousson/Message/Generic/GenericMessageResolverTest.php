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
 *  Lousson\Message\Generic\GenericMessageResolverTest class definition
 *
 *  @package    org.lousson.record
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolverTest;
use Lousson\Message\Generic\GenericMessageResolver;
use Lousson\URI\Generic\GenericURI;

/** Exceptions: */
use Lousson\Message\Error\RuntimeMessageError;
use DomainException;

/**
 *  A test case for the generic message resolver
 *
 *  The GenericMessageResolverTest class is a test case derived from
 *  the AbstractMessageResolverTest. It extends the list of tests by checks
 *  specific for the implementation of the GenericMessageResolver class.
 *
 *  Note that the GenericMessageResolverTest has been declared as final.
 *  Authors of classes extending the GenericMessageResolver should extend
 *  the AbstractMessageResolverTest to implement their test cases.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class GenericMessageResolverTest
    extends AbstractMessageResolverTest
{
    /**
     *  Obtain a message resolver instance
     *
     *  The getMessageResolver() method is used to obtain the resolver
     *  instance to use in the tests.
     *
     *  @return \Lousson\Message\Generic\GenericMessageResolver
     *          A message resolver instance is returned on success
     */
    public function getMessageResolver()
    {
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        return $resolver;
    }

    /**
     *  Test the resolveHandler() method
     *
     *  The testResolveHandlerWithResolver() method is a test case that
     *  verifies whether an URI resolver passed to the message resolver's
     *  constructor is actually used when resolveHandler() is invoked.
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testResolveHandlerWithResolver()
    {
        $uriString = "urn:lousson:test";
        $uriObject = GenericURI::create($uriString);

        $mock = $this->getMock(
            "Lousson\\URI\\AnyURIResolver",
            array("resolve", "resolveURI")
        );

        $mock
            ->expects($this->once())
            ->method("resolve")
            ->with($this->equalTo($uriString))
            ->will($this->returnValue(array($uriObject)));

        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider"), array($mock)
        );

        $resolver->resolveHandler($uriString);
    }

    /**
     *  Test the resolveProvider() method
     *
     *  The testResolveProviderWithResolver() method is a test case that
     *  verifies whether an URI resolver passed to the message resolver's
     *  constructor is actually used when resolveProvider() is invoked.
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testResolveProviderWithResolver()
    {
        $uriString = "urn:lousson:test";
        $uriObject = GenericURI::create($uriString);

        $mock = $this->getMock(
            "Lousson\\URI\\AnyURIResolver",
            array("resolve", "resolveURI")
        );

        $mock
            ->expects($this->once())
            ->method("resolve")
            ->with($this->equalTo($uriString))
            ->will($this->returnValue(array($uriObject)));

        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider"), array($mock)
        );

        $resolver->resolveProvider($uriString);
    }

    /**
     *  Test the lookupHandler() method
     *
     *  The testLookupHandler() method is a test case that verifies the
     *  resolver returning the handler returned by lookupHandler() when
     *  the resolveHandler() method is invoked.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case the return values are not the same
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testLookupHandler()
    {
        $handler = $this->getMock("Lousson\\Message\\AnyMessageHandler");
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        $resolver
            ->expects($this->any())
            ->method("lookupHandler")
            ->will($this->returnValue($handler));

        $uri = "urn:lousson:test";
        $result = $resolver->resolveHandler($uri);
        $this->assertSame($handler, $result);
    }

    /**
     *  Test the lookupProvider() method
     *
     *  The testLookupProvider() method is a test case that verifies the
     *  resolver returning the provider returned by lookupProvider() when
     *  the resolveProvider() method is invoked.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case the return values are not the same
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testLookupProvider()
    {
        $provider = $this->getMock("Lousson\\Message\\AnyMessageProvider");
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        $resolver
            ->expects($this->any())
            ->method("lookupProvider")
            ->will($this->returnValue($provider));

        $uri = "urn:lousson:test";
        $result = $resolver->resolveProvider($uri);
        $this->assertSame($provider, $result);
    }

    /**
     *  Test the resolveHandler() method
     *
     *  The testResolveHandlerWithValidError() method is a test case for
     *  the resolveHandler() method. It verifies whether a message error
     *  raised by the lookupHandler() method is passed through.
     *
     *  @expectedException          \Lousson\Message\AnyMessageException
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testResolveHandlerWithValidError()
    {
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        $resolver
            ->expects($this->any())
            ->method("lookupHandler")
            ->will($this->throwException(new RuntimeMessageError()));

        $uri = "urn:lousson:test";
        $resolver->resolveHandler($uri);
    }

    /**
     *  Test the resolveProvider() method
     *
     *  The testResolveProviderWithValidError() method is a test case for
     *  the resolveProvider() method. It verifies whether a message error
     *  raised by the lookupProvider() method is passed through.
     *
     *  @expectedException          \Lousson\Message\AnyMessageException
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testResolveProviderWithValidError()
    {
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        $resolver
            ->expects($this->any())
            ->method("lookupProvider")
            ->will($this->throwException(new RuntimeMessageError()));

        $uri = "urn:lousson:test";
        $resolver->resolveProvider($uri);
    }

    /**
     *  Test the resolveHandler() method
     *
     *  The testResolveHandlerWithValidError() method is a test case for
     *  the resolveHandler() method. It verifies whether an some arbitrary
     *  error raised by the lookupHandler() method is wrapped to become an
     *  AnyMessageException instance before it is re-raised.
     *
     *  @expectedException          \Lousson\Message\AnyMessageException
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testResolveHandlerWithInvalidError()
    {
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        $resolver
            ->expects($this->any())
            ->method("lookupHandler")
            ->will($this->throwException(new DomainException()));

        $uri = "urn:lousson:test";
        $resolver->resolveHandler($uri);
    }

    /**
     *  Test the resolveProvider() method
     *
     *  The testResolveProviderWithValidError() method is a test case for
     *  the resolveProvider() method. It verifies whether an some arbitrary
     *  error raised by the lookupProvider() method is wrapped to become an
     *  AnyMessageException instance before it is re-raised.
     *
     *  @expectedException          \Lousson\Message\AnyMessageException
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testResolveProviderWithInvalidError()
    {
        $resolver = $this->getMock(
            "Lousson\\Message\\Generic\\GenericMessageResolver",
            array("lookupHandler", "lookupProvider")
        );

        $resolver
            ->expects($this->any())
            ->method("lookupProvider")
            ->will($this->throwException(new DomainException()));

        $uri = "urn:lousson:test";
        $resolver->resolveProvider($uri);
    }
}

