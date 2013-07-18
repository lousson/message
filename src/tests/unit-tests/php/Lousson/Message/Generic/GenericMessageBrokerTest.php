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
 *  Lousson\Message\Generic\GenericMessageBrokerTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Dependencies: */
use Lousson\Message\AbstractMessageTest;
use Lousson\Message\Generic\GenericMessageBroker;
use Lousson\Message\Generic\GenericMessageHandler;
use Lousson\Message\Generic\GenericMessageProvider;

/**
 *  A test case for the generic message broker
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class GenericMessageBrokerTest extends AbstractMessageTest
{
    /**
     *  Test the process() method
     *
     *  The testProcess() method is a test case for the generic message
     *  broker's process() method.
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcess()
    {
        $data[] = "urn:foo:bar";
        $data[] = "foo? bar. baz!";
        $data[] = "text/plain";

        $provider = $this->getProviderMock();
        $handler = $this->getHandlerMock();
        $callback = $this->getCallback($data);

        $handler
            ->expects($this->once())
            ->method("process")
            ->will($this->returnCallback($callback));

        $broker = new GenericMessageBroker($provider, $handler);
        $broker->process($data[0], $data[1], $data[2]);
    }

    /**
     *  Test the processMessage() method
     *
     *  The testProcessMessage() method is a test case for the generic
     *  message broker's processMessage() method.
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessMessage()
    {
        $data[] = "urn:foo:bar";
        $data[] = $this->getMessageMock();

        $provider = $this->getProviderMock();
        $handler = $this->getHandlerMock();
        $callback = $this->getCallback($data);

        $handler
            ->expects($this->once())
            ->method("processMessage")
            ->will($this->returnCallback($callback));

        $broker = new GenericMessageBroker($provider, $handler);
        $broker->processMessage($data[0], $data[1]);
    }

    /**
     *  Test the fetch() method
     *
     *  The testFetch() method is a test case for the generic message
     *  broker's fetch() method.
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testFetch()
    {
        $data[] = "urn:foo:bar";
        $data[] = GenericMessageBroker::FETCH_CONFIRM;
        $data[] = null;

        $provider = $this->getProviderMock();
        $handler = $this->getHandlerMock();
        $callback = $this->getCallback($data);

        $provider
            ->expects($this->once())
            ->method("fetch")
            ->will($this->returnCallback($callback));

        $broker = new GenericMessageBroker($provider, $handler);
        $broker->fetch($data[0], $data[1], $data[2]);
    }

    /**
     *  Test the acknowledge() method
     *
     *  The testAcknowledge() method is a test case for the generic message
     *  broker's acknowledge() method.
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledge()
    {
        $data[] = "1234567890";
        $data[] = 0;

        $provider = $this->getProviderMock();
        $handler = $this->getHandlerMock();
        $callback = $this->getCallback($data);

        $provider
            ->expects($this->once())
            ->method("acknowledge")
            ->will($this->returnCallback($callback));

        $broker = new GenericMessageBroker($provider, $handler);
        $broker->acknowledge($data[0], $data[1]);
    }

    /**
     *  Test the discard() method
     *
     *  The testDiscard() method is a test case for the generic message
     *  broker's discard() method.
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscard()
    {
        $data[] = "1234567890";
        $data[] = GenericMessageBroker::DISC_REQUEUE;

        $provider = $this->getProviderMock();
        $handler = $this->getHandlerMock();
        $callback = $this->getCallback($data);

        $provider
            ->expects($this->once())
            ->method("discard")
            ->will($this->returnCallback($callback));

        $broker = new GenericMessageBroker($provider, $handler);
        $broker->discard($data[0], $data[1]);
    }

    /**
     *  Obtain a test callback
     *
     *  The getCallback() method is used internally to obtain a
     *  closure instance that requires to get invoked with the exact
     *  same parameters as formerly provided in the $data array,
     *  otherwise it raises a PHPUnit_Framework_AssertionFailedError.
     *
     *  @param  array               $data           The parameter data
     *
     *  @return \Closure
     */
    private function getCallback(array $data)
    {
        $test = $this;
        $callback = function() use ($data, $test) {
            $test->assertEquals($data, func_get_args());
        };

        return $callback;
    }

    /**
     *  Obtain a handler instance
     *
     *  The getHandlerMock() method is used internally to obtain a
     *  mock of the AnyMessageHandler interface.
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler mock is returned on success
     */
    private function getHandlerMock()
    {
        $methods = array("process", "processMessage");
        $handler = $this->getMock(self::I_HANDLER, $methods);
        return $handler;
    }

    /**
     *  Obtain a provider instance
     *
     *  The getProviderMock() method is used internally to obtain a
     *  mock of the AnyMessageProvider interface.
     *
     *  @return \Lousson\Message\AnyMessageProvider
     *          A message provider mock is returned on success
     */
    private function getProviderMock()
    {
        $methods = array("fetch", "acknowledge", "discard");
        $provider = $this->getMock(self::I_PROVIDER, $methods);
        return $provider;
    }
}

