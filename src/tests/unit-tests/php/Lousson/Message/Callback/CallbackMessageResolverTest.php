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
 *  Lousson\Message\Callback\CallbackMessageResolverTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Callback;

/** Interfaces: */
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolverTest;
use Lousson\Message\Callback\CallbackMessageResolver;
use Lousson\Message\Generic\GenericMessageResolver;
use Lousson\URI\Builtin\BuiltinURIResolver;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;
use DomainException;

/**
 *  A test case for the generic message resolver
 *
 *  The Lousson\Message\Callback\CallbackMessageResolverTest class is a test
 *  case derived from the AbstractMessageResolverTest. It extends the list
 *  of tests by checks specific for the implementation of the generic
 *  message resolver.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class CallbackMessageResolverTest
    extends AbstractMessageResolverTest
{
    /**
     *  Obtain a message resolver instance
     *
     *  The getMessageResolver() method is used to obtain the resolver
     *  instance to use in the tests.
     *
     *  @return \Lousson\Message\Callback\CallbackMessageResolver
     *          A message resolver instance is returned on success
     */
    public function getMessageResolver()
    {
        $resolver = new BuiltinURIResolver();
        $resolver = new GenericMessageResolver($resolver);

        $callback = function(AnyURI $uri) use ($resolver) {
            return $resolver;
        };

        $resolver = new CallbackMessageResolver($callback);
        return $resolver;
    }

    /**
     *  Test the lookupResolver() method
     *
     *  The testLookupErrorHandling() method is a test case to check
     *  the handling of message exceptions raised by the callback.
     *
     *  @expectedException  Lousson\Message\Error\InvalidMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\InvalidMessageError
     *          Raised if the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testLookupErrorHandling()
    {
        $callback = function($uri) { throw new InvalidMessageError; };
        $resolver = new CallbackMessageResolver($callback);
        $uri = "http://example.com/";
        $resolver->resolveHandler($uri);
    }

    /**
     *  Test the lookupResolver() method
     *
     *  The testLookupExceptionHandling() method is a test case to check
     *  the handling of non-domain exceptions raised by the callback.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised if the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testLookupExceptionHandling()
    {
        $callback = function($uri) { throw new DomainException; };
        $resolver = new CallbackMessageResolver($callback);
        $uri = "http://lousson.org/";
        $resolver->resolveHandler($uri);
    }

    /**
     *  Test the lookupResolver() method
     *
     *  The testLookupResultHandling() method is a test case to check
     *  the handling of invalid values returned by the callback.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised if the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testLookupResultHandling()
    {
        $callback = function(AnyURI $uri) { return $this; };
        $resolver = new CallbackMessageResolver($callback);
        $uri = "urn:foo:bar";
        $resolver->resolveHandler($uri);
    }
}

