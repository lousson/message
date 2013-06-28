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
 *  Lousson\Message\AMQP\AMQPMessageProviderTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\AMQP;

/** Dependencies: */
use Lousson\Message\AbstractMessageProviderTest;
use Lousson\Message\AMQP\AMQPMessageResolver;
use Lousson\Message\AMQP\Intern\AMQPFactory;
use Lousson\URI\Generic\GenericURI;

/**
 *  A test case for the AMQP message provider
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class AMQPMessageProviderTest extends AbstractMessageProviderTest
{
    /**
     *  An alias for the AMQPMessageResolver::ROUTE_REGEX constant
     *
     *  @var string
     */
    const ROUTE_REGEX = AMQPMessageResolver::ROUTE_REGEX;

    /**
     *  An alias for the AMQPMessageResolver::ROUTE_INDEX constant
     *
     *  @var int
     */
    const ROUTE_INDEX = AMQPMessageResolver::ROUTE_INDEX;

    /**
     *  Obtain a message provider instance
     *
     *  The getMessageProvider() method returns the message provider
     *  instance the test case shall operate with.
     *
     *  @param  string              $uri        The test message URI
     *  @param  array               $expected   The test messages
     *
     *  @return \Lousson\Message\AMQP\AMQPMessageProvider
     *          A message provider instance is returned on success
     */
    public function getMessageProvider($uri, array $expected)
    {
        $factory = new AMQPFactory();
        $routingKey = null;
        $queue = $factory->createQueue($uri);
        $queue->purge();
        $exchange = $factory->createExchange($uri);

        if (preg_match(self::ROUTE_REGEX, (string) $uri, $matches)) {
            $routingKey = $matches[self::ROUTE_INDEX];
        }

        foreach ($expected as $message) {
            $content = $message->getContent();
            $type = $message->getType();
            $headers["content_type"] = $message->getType();
            $exchange->publish(
                $content, $routingKey, AMQP_NOPARAM, $headers
            );
        }

        $resolver = new AMQPMessageResolver();
        $resolverClass = get_class($resolver);
        $provider = $resolver->resolveProvider($uri);

        return $provider;
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
     *  Test the fetch() method
     *
     *  The testFetchException() method verifies that the message provider
     *  handles exceptions raised by the underlying AMQPQueue correctly.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case the test is successful
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testFetchException()
    {
        $queue = $this->getQueueMock();
        $queue
            ->expects($this->once())
            ->method("get")
            ->will($this->throwException(new \AMQPException));

        $provider = new AMQPMessageProvider($queue);
        $provider->fetch(self::DEFAULT_MESSAGE_URI);
    }

    /**
     *  Test the fetch() method
     *
     *  The testFetchWarning() method verifies that the message provider
     *  handles non-TRUE return values from the underlying AMQPQueue.
     *
     *  @expectedException  \PHPUnit_Framework_Error_Warning
     *  @test
     *
     *  @throws \PHPUnit_Framework_Error_Warning
     *          Raised in case the test is successful
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testFetchWarning()
    {
        $queue = $this->getQueueMock();
        $queue
            ->expects($this->once())
            ->method("ack")
            ->will($this->throwException(new \AMQPException));

        $provider = new AMQPMessageProvider($queue);
        $provider->fetch(self::DEFAULT_MESSAGE_URI);
    }

    /**
     *  Test the acknowledge() method
     *
     *  The testAcknowledgeException() method verifies that the message
     *  provider handles exceptions raised by the underlying AMQPQueue
     *  correctly.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case the test is successful
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeException()
    {
        $queue = $this->getQueueMock();
        $queue
            ->expects($this->once())
            ->method("ack")
            ->will($this->throwException(new \AMQPException));

        $provider = new AMQPMessageProvider($queue);
        $this->performFetch(
            $provider,
            self::DEFAULT_MESSAGE_URI,
            self::FETCH_CONFIRM,
            $token
        );

        $provider->acknowledge($token);
    }

    /**
     *  Test the acknowledge() method
     *
     *  The testAcknowledgeFailure() method verifies that the message
     *  provider handles non-TRUE return values from the underlying
     *  AMQPQueue.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case the test is successful
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeFailure()
    {
        $queue = $this->getQueueMock();
        $provider = new AMQPMessageProvider($queue);

        $this->performFetch(
            $provider,
            self::DEFAULT_MESSAGE_URI,
            self::FETCH_CONFIRM,
            $token
        );

        $provider->acknowledge($token);
    }

    /**
     *  Test the discard() method
     *
     *  The testDiscardException() method verifies that the message
     *  provider handles exceptions raised by the underlying AMQPQueue
     *  correctly.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case the test is successful
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardException()
    {
        $queue = $this->getQueueMock();
        $queue
            ->expects($this->once())
            ->method("nack")
            ->will($this->throwException(new \AMQPException));

        $provider = new AMQPMessageProvider($queue);
        $this->performFetch(
            $provider,
            self::DEFAULT_MESSAGE_URI,
            self::FETCH_CONFIRM,
            $token
        );

        $provider->discard($token);
    }

    /**
     *  Test the discard() method
     *
     *  The testDiscardFailure() method verifies that the message
     *  provider handles non-TRUE return values from the underlying
     *  AMQPQueue.
     *
     *  @expectedException  Lousson\Message\Error\RuntimeMessageError
     *  @test
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case the test is successful
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardFailure()
    {
        $queue = $this->getQueueMock();
        $provider = new AMQPMessageProvider($queue);

        $this->performFetch(
            $provider,
            self::DEFAULT_MESSAGE_URI,
            self::FETCH_CONFIRM,
            $token
        );

        $provider->discard($token);
    }

    /**
     *  Obtain an AMQPQueue mock
     *
     *  The getQueueMock() method is used internally to create a mock
     *  object for the AMQPQueue class returning and envelope with the
     *  the given $content and $type in the get() method.
     *
     *  @param  mixed               $content    The mock envelope body
     *  @param  mixed               $type       The mock envelope type
     *
     *  @return \AMQPQueue
     *          An AMQP queue mock is returned on success
     */
    private function getQueueMock($content = null, $type = null)
    {
        $envelope = $this->getEnvelopeMock($content, $type);

        $queue = $this->getMockBuilder("AMQPQueue")
            ->disableOriginalConstructor()
            ->getMock(array("get", "ack", "nack"));

        $queue
            ->expects($this->any())
            ->method("get")
            ->will($this->returnValue($envelope));

        return $queue;
    }

    /**
     *  Obtain an AMQPEnvelope mock
     *
     *  The getEnvelopeMock() method is used internally to create a mock
     *  object for the AMQPEnvelope class representing the given $content
     *  and $type.
     *
     *  @param  mixed               $content    The mock envelope body
     *  @param  mixed               $type       The mock envelope type
     *
     *  @return \AMQPEnvelope
     *          An AMQP envelope mock is returned on success
     */
    private function getEnvelopeMock($content, $type)
    {
        $methods = array("getBody", "getContentType", "getDeliveryTag");
        $envelope = $this->getMockBuilder("AMQPEnvelope")
            ->disableOriginalConstructor()
            ->getMock($methods);

        $envelope
            ->expects($this->any())
            ->method("getBody")
            ->will($this->returnValue($content));

        $envelope
            ->expects($this->any())
            ->method("getContentType")
            ->will($this->returnValue($type));

        return $envelope;
    }
}

