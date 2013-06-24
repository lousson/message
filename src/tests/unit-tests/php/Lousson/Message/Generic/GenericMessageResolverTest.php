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
 *  @package    org.lousson.message
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
use Lousson\URI\Builtin\BuiltinURIResolver;

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
        $fallback = $this->getMock("Lousson\\Message\\AnyMessageResolver");
        $resolver = new BuiltinURIResolver();
        $resolver = new GenericMessageResolver($resolver, null, $fallback);
        return $resolver;
    }

    /**
     *  Test the setHandler() method
     *
     *  The testSetHandler() method is a test case for the setHandler()
     *  method. It verifies that the setter/getter association is working
     *  and finally tests the parameter validation.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\Error\InvalidMessageError
     *          Raised in case the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testSetHandler()
    {
        $resolver = $this->getMessageResolver();
        $resolverClass = get_class($resolver);

        $handler = $this->getMessageHandler();
        $resolver->setHandler("foo", $handler);
        $uri = "foo:bar";

        $this->assertSame(
            $handler, $resolver->resolveHandler($uri), sprintf(
            "The %s::resolveHandler() method must return the handler ".
            "formerly assigned via setHandler()",
            $resolverClass
        ));

        $this->setExpectedException(self::I_EXCEPTION);
        $resolver->setHandler(":äöü", $handler);
    }

    /**
     *  Test the setProvider() method
     *
     *  The testSetProvider() method is a test case for the setProvider()
     *  method. It verifies that the setter/getter association is working
     *  and finally tests the parameter validation.
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\Error\InvalidMessageError
     *          Raised in case the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testSetProvider()
    {
        $resolver = $this->getMessageResolver();
        $resolverClass = get_class($resolver);

        $provider = $this->getMessageProvider();
        $resolver->setProvider("foo", $provider);
        $uri = "foo:bar";

        $this->assertSame(
            $provider, $resolver->resolveProvider($uri), sprintf(
            "The %s::resolveProvider() method must return the provider ".
            "formerly assigned via setProvider()",
            $resolverClass
        ));

        $this->setExpectedException(self::I_EXCEPTION);
        $resolver->setProvider(":äöü", $provider);
    }
}

