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
 *  Lousson\Message\Callback\CallbackMessageProviderTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Callback;

/** Interfaces: */
use Lousson\Message\AnyMessageProvider;

/** Dependencies: */
use Lousson\Message\AbstractMessageProviderTest;
use Lousson\Message\Callback\CallbackMessageProvider;
use Psr\Log\NullLogger;

/**
 *  A test case for the callback message provider
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class CallbackMessageProviderTest extends AbstractMessageProviderTest
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
     *  @return \Lousson\Message\Callback\CallbackMessageProvider
     *          A message provider instance is returned on success
     */
    public function getMessageProvider($uri, array $expected)
    {
        $callback = function($uri) use(&$expected) {
            $message = array_shift($expected);
            return $message;
        };

        $provider = new CallbackMessageProvider($callback);
        $provider->setLogger(new NullLogger());

        return $provider;
    }

    /**
     *  Test the destructor
     *
     *  The testDestructor() method verifies that the cleanup in the
     *  CallbackMessageProvider's destructor works as expected.
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

