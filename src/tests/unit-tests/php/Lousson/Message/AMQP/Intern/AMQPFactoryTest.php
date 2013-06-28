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
 *  Lousson\Message\AMQP\Intern\AMQPFactoryTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\AMQP\Intern;

/** Interfaces: */
use Lousson\URI\AnyURI;

/** Dependencies: */
use AMQPChannel;
use Lousson\Message\AbstractMessageTest;
use Lousson\Message\AMQP\Intern\AMQPFactory;
use Lousson\URI\Generic\GenericURI;

/**
 *  A test case for the AMQPFactory class
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class AMQPFactoryTest extends AbstractMessageTest
{
    /**
     *  Create an AMQP factory insance
     *
     *  The getAMQPFactory() method is used internally to obtain the
     *  AMQP factory instance to be used in the tests.
     *
     *  @return \Lousson\Message\AMQP\Intern\AMQPFactory
     *          An AMQPFactory instance is returned on success
     *
     *  @throws \PHPUnit_Framework_SkippedTest
     *          Raised in case the AMQP extension is not available
     */
    public function getAMQPFactory()
    {
        if (!class_exists("AMQPConnection")) {
            $this->markTestSkipped("The AMQP extension is not available");
        }

        $factory = new AMQPFactory();
        return $factory;
    }

    /**
     *  Provide valid URIs
     *
     *  The provideValidURIs() method returns an array of one or more
     *  items, each of whose is an array with one item: Either a valid
     *  URI string or an instance of the AnyURI interface.
     *
     *  Authors of derived classes should reimplement this method if
     *  their test subject operates with a particular implementation
     *  that supports e.g. specific schemes only.
     *
     *  @return array
     *          A list of URI parameters is returned on success
     */
    public function provideValidURIs()
    {
        $uri = "amqp://guest:guest@localhost:5672/?routing-key=test-route";
        $uri .= "&exchange-name=test-exchange&queue-name=test-queue";

        $uris[][] = $uri;
        return $uris;
    }

    /**
     *  Test the createExchange() method
     *
     *  The testCreateExchange() method is a smoke test for the factory's
     *  createExchange() method operating with a valid exchange $uri.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider   provideValidURIs
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateExchange($uri)
    {
        $factory = $this->getAMQPFactory();
        $exchange = $this->invokeCreateExchange($factory, $uri);

        $this->assertAttributeNotEmpty("channel", $exchange);
        $this->assertInstanceOf("AMQPChannel", $exchange->channel);

        $channel = $exchange->channel;
        unset($exchange);

        $this->invokeCreateExchange($factory, $uri, $channel);
    }

    /**
     *  Test the createExchange() method
     *
     *  The testCreateExchangeReconnectError() method tests the error
     *  handling in the factory's createExchange() method's reconnec
     *  mechanism.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider       provideValidURIs
     *  @expectedException  Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *  @test
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *          Raised in case the test is successful
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateExchangeReconnectError($uri)
    {
        $callback = function() { return false; };
        $factory = $this->getAMQPFactory($uri);
        $connection = $this->getConnectionMock($callback);

        $channel = $this->getMockBuilder("AMQPChannel")
            ->disableOriginalConstructor()
            ->getMock(array());

        $channel->connection = $connection;
        $factory->createExchange($uri, $channel);
    }

    /**
     *  Test the createExchange() method
     *
     *  The testCreateExchangeRuntimeError() method tests the error
     *  handling in the factory's createExchange() method.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider       provideValidURIs
     *  @expectedException  Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *  @test
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *          Raised in case the test is successful
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateExchangeRuntimeError($uri)
    {
        $factory = $this->getAMQPFactory();
        $uri = $this->getURIMock($uri);
        $factory->createExchange($uri);
    }

    /**
     *  Test the createExchange() method
     *
     *  The testCreateExchangeArgumentError() method tests the error
     *  handling in the factory's createExchange() method.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider       provideInvalidURIs
     *  @expectedException  Lousson\Message\AMQP\Intern\AMQPArgumentError
     *  @test
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateExchangeArgumentError($uri)
    {
        $factory = $this->getAMQPFactory();
        $factory->createExchange($uri);
    }

    /**
     *  Test the createQueue() method
     *
     *  The testCreateQueue() method is a smoke test for the factory's
     *  createQueue() method, operating with a valid queue $uri.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider   provideValidURIs
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateQueue($uri)
    {
        $factory = $this->getAMQPFactory();
        $queue = $this->invokeCreateQueue($factory, $uri);

        $this->assertAttributeNotEmpty("channel", $queue);
        $this->assertInstanceOf("AMQPChannel", $queue->channel);

        $channel = $queue->channel;
        unset($queue);

        $this->invokeCreateQueue($factory, $uri, $channel);
    }

    /**
     *  Test the createQueue() method
     *
     *  The testCreateQueueReconnectError() method tests the error
     *  handling in the factory's createQueue() method's reconnect
     *  mechanism.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider       provideValidURIs
     *  @expectedException  Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *  @test
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *          Raised in case the test is successful
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateQueueReconnectError($uri)
    {
        $callback = function() { throw new \AMQPException; };
        $factory = $this->getAMQPFactory($uri);
        $connection = $this->getConnectionMock($callback);

        $channel = $this->getMockBuilder("AMQPChannel")
            ->disableOriginalConstructor()
            ->getMock(array());

        $channel->connection = $connection;
        $factory->createQueue($uri, $channel);
    }

    /**
     *  Test the createQueue() method
     *
     *  The testCreateQueueRuntimeError() method tests the error handling
     *  in the factory's createQueue() method.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider       provideValidURIs
     *  @expectedException  Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *  @test
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *          Raised in case the test is successful
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateQueueRuntimeError($uri)
    {
        $factory = $this->getAMQPFactory();
        $uri = $this->getURIMock($uri);
        $factory->createQueue($uri);
    }

    /**
     *  Test the createQueue() method
     *
     *  The testCreateQueueArgumentError() method tests the error handling
     *  in the factory's createQueue() method.
     *
     *  @param  string              $uri        The queue URI to use
     *
     *  @dataProvider       provideInvalidURIs
     *  @expectedException  Lousson\Message\AMQP\Intern\AMQPArgumentError
     *  @test
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testCreateQueueArgumentError($uri)
    {
        $factory = $this->getAMQPFactory();
        $factory->createQueue($uri);
    }

    /**
     *  Invoke the createExchange() method
     *
     *  The invokeCreateExchange() method is used internally to invoke the
     *  given $factory's createExchange() method with the $uri and $channel
     *  parameters provided. It performs assertions on the returned value
     *  before passing it back to the caller.
     *
     *  @param  AMQPFactory         $factory    The factory to use
     *  @param  string              $uri        The URI to pass on
     *  @param  AMQPChannel         $channel    The channel to pass on
     *
     *  @return AMQPExchange
     *          An AMQP exchange instance is returned on success
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \AMQPException
     *          Raised in case the $uri or the $channel is invalid
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function invokeCreateExchange(
        AMQPFactory $factory,
        $uri,
        AMQPChannel $channel = null
    ) {
        $factoryClass = get_class($factory);
        $exchange = $factory->createExchange($uri, $channel);

        $this->assertInstanceOf(
            "AMQPExchange", $exchange, sprintf(
            "The %s::createExchange() method must return an instance of ".
            "the AMQPExchange class (uri used: %s)", $factoryClass, $uri
        ));

        return $exchange;
    }

    /**
     *  Invoke the createQueue() method
     *
     *  The invokeCreateQueue() method is used internally to invoke the
     *  given $factory's createQueue() method with the $uri and $channel
     *  parameters provided. It performs assertions on the returned value
     *  before passing it back to the caller.
     *
     *  @param  AMQPFactory         $factory    The factory to use
     *  @param  string              $uri        The URI to pass on
     *  @param  AMQPChannel         $channel    The channel to pass on
     *
     *  @return AMQPQueue
     *          An AMQP queue instance is returned on success
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \AMQPException
     *          Raised in case the $uri or the $channel is invalid
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function invokeCreateQueue(
        AMQPFactory $factory,
        $uri,
        AMQPChannel $channel = null
    ) {
        $queue = $factory->createQueue($uri, $channel);
        $factoryClass = get_class($factory);

        $this->assertInstanceOf(
            "AMQPQueue", $queue, sprintf(
            "The %s::createQueue() method must return an instance of ".
            "the AMQPQueue class (uri used: %s)", $factoryClass, $uri
        ));

        return $queue;
    }

    /**
     *  Create an URI mock object
     *
     *  The getURIMock() method is used internally to create a mocked
     *  instance of the AnyURI interface, used to provoke errors within
     *  the factory methods - in order to test the error handling.
     *
     *  @param  string              $uri        The URI prototype
     *
     *  @return \Lousson\URI\AnyURI
     *          An URI instance is returned on success
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the given $uri is malformed
     */
    private function getURIMock($uri)
    {
        $uri = GenericURI::create($uri);

        $callback = function($part = null) use ($uri) {
            if (AnyURI::PART_QUERY === $part) {
                throw new \AMQPException;
            }
            return $uri->getPart($part);
        };

        $uri = $this->getMock(
            "Lousson\\URI\\AnyURI",
            array("getPart", "getLexical", "__toString")
        );

        $uri->expects($this->any())
            ->method("getPart")
            ->will($this->returnCallback($callback));

        $uri->expects($this->any())
            ->method("getLexical")
            ->will($this->returnCallback($callback));

        $uri->expects($this->any())
            ->method("__toString")
            ->will($this->returnCallback($callback));

        return $uri;
    }

    /**
     *
     */
    private function getConnectionMock($callback)
    {
        $connection = $this->getMock("AMQPConnection", array("connect"));
        $connection
            ->expects($this->once())
            ->method("connect")
            ->will($this->returnCallback($callback));

        return $connection;
    }
}

