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
 *  Lousson\Message\AbstractMessageHandlerTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Dependencies: */
use PHPUnit_Framework_TestCase;

/**
 *  An abstract test case for message handlers
 *
 *  The Lousson\Message\AbstractMessageHandlerTest class serves as the
 *  base for testing implementations of the AnyMessageHandler interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class AbstractMessageHandlerTest extends AbstractMessageTest
{
    /**
     *  Obtain a message handler instance
     *
     *  The getMessageHandler() method returns an instance of the message
     *  handler class that is to be tested.
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler instance is returned on success
     */
    abstract public function getMessageHandler();

    /**
     *  Aggregate process() parameters
     *
     *  The aggregateProcessParameters() method returns a list of multiple
     *  items, each of whose is an array of either two or three items:
     *
     *- An arbitrary URI string (invalid if $useInvalidURIs is set)
     *- Arbitrary message content, usually a byte sequence
     *- A media type string, NULL (or absent)
     *
     *  @param  bool                $useInvalidURIs Use invalid URIs if set
     *
     *  @return array
     *          A list of process() parameters is returned on success
     */
    public function aggregateProcessParameters(
        $useInvalidURIs = false
    ) {
        $uriList = $useInvalidURIs
            ? $this->provideInvalidURIs()
            : $this->provideValidURIs();

        $uriList = array_map("array_pop", $uriList);
        $messageDataTuples = $this->provideValidMessageData();
        $parameters = array();

        foreach ($uriList as $uri) {
            foreach ($messageDataTuples as $tuple) {
                array_unshift($tuple, $uri);
                $parameters[] = $tuple;
            }
        }

        return $parameters;
    }

    /**
     *  Aggregate processMessage() parameters
     *
     *  The aggregateProcessMessageParameters() method returns a list of
     *  multiple items, each of whose is an array of two items:
     *
     *- An arbitrary URI string (invalid if $useInvalidURIs is set)
     *- An instance of the AnyMessage interface
     *
     *  @param  bool                $useInvalidURIs Use invalid URIs if set
     *
     *  @return array
     *          A list of processMessage() parameters is returned on
     *          success
     */
    public function aggregateProcessMessageParameters(
        $useInvalidURIs = false
    ) {
        $uriList = $useInvalidURIs
            ? $this->provideInvalidURIs()
            : $this->provideValidURIs();

        $uriList = array_map("array_pop", $uriList);
        $messageList = $this->provideValidMessageInstances();
        $messageList = array_map("array_pop", $messageList);
        $parameters = array();

        foreach ($uriList as $uri) {
            foreach ($messageList as $message) {
                $parameters[] = array($uri, $message);
            }
        }

        return $parameters;
    }

    /**
     *  Provide valid process() parameters
     *
     *  The provideValidProcessParameters() method is a data provider
     *  alias for aggregateProcessParameters() requesting valid URIs.
     *
     *  @return array
     *          A list of process() parameters is returned on success
     */
    public function provideValidProcessParameters()
    {
        $parameters = $this->aggregateProcessParameters(false);
        return $parameters;
    }

    /**
     *  Provide invalid process() parameters
     *
     *  The provideInvalidProcessParameters() method is an alias for
     *  aggregateProcessParameters() requesting invalid URIs.
     *
     *  @return array
     *          A list of process() parameters is returned on success
     */
    public function provideInvalidProcessParameters()
    {
        $parameters = $this->aggregateProcessParameters(true);
        return $parameters;
    }

    /**
     *  Provide valid processMessage() parameters
     *
     *  The provideValidProcessMessageParameters() method is an alias
     *  for aggregateProcessMessageParameters() requesting valid URIs.
     *
     *  @return array
     *          A list of processMessage() parameters is returned on
     *          success
     */
    public function provideValidProcessMessageParameters()
    {
        $parameters = $this->aggregateProcessMessageParameters(false);
        return $parameters;
    }

    /**
     *  Provide invalid processMessage() parameters
     *
     *  The provideInvalidProcessMessageParameters() method is an alias
     *  for aggregateProcessMessageParameters() requesting invalid URIs.
     *
     *  @return array
     *          A list of processMessage() parameters is returned on
     *          success
     */
    public function provideInvalidProcessMessageParameters()
    {
        $parameters = $this->aggregateProcessMessageParameters(true);
        return $parameters;
    }

    /**
     *  Test the process() method
     *
     *  The testProcessMessageWithValidParameters() method is a test case
     *  for the message handler's process() method that operates with valid
     *  $uri, $content and $type parameters.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  string              $content    The message content
     *  @param  string              $type       The message type, if any
     *
     *  @dataProvider   provideValidProcessParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessWithValidParameters(
        $uri,
        $content,
        $type = null
    ) {
        $handler = $this->getMessageHandler();

        if (3 <= func_num_args()) {
            $this->performProcess($handler, $uri, $content, $type);
        }
        else {
            $this->performProcess($handler, $uri, $content);
        }
    }

    /**
     *  Test the process() method
     *
     *  The testProcessWithInvalidParameters() method is a test case for
     *  the message handler's process() method that operates with invalid
     *  $uri, $content and/or $type parameters.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  string              $content    The message content
     *  @param  string              $type       The message type, if any
     *
     *  @dataProvider   provideInvalidProcessParameters
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessWithInvalidParameters(
        $uri,
        $content,
        $type = null
    ) {
        $handler = $this->getMessageHandler();
        $this->setExpectedException("Lousson\\Message\\AnyMessageException");

        if (3 <= func_num_args()) {
            $handler->process($uri, $content, $type);
        }
        else {
            $handler->process($uri, $content);
        }
    }

    /**
     *  Test the processMessage() method
     *
     *  The testProcessMessageWithValidParameters() method is a test case
     *  for the message handler's processMessage() method that operates
     *  with a valid $uri and $message instance.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message to process
     *
     *  @dataProvider   provideValidProcessMessageParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessMessageWithValidParameters(
        $uri,
        AnyMessage $message
    ) {
        $handler = $this->getMessageHandler();
        $this->performProcessMessage($handler, $uri, $message);
    }

    /**
     *  Test the processMessage() method
     *
     *  The testProcessWithInvalidParameters() method is a test case for
     *  the message handler's processMessage() method that operates with
     *  invalid $uri and/or $message parameters.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message to process
     *
     *  @dataProvider   provideInvalidProcessMessageParameters
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testProcessMessageWithInvalidParameters(
        $uri,
        AnyMessage $message
    ) {
        $handler = $this->getMessageHandler();
        $this->setExpectedException("Lousson\\Message\\AnyMessageException");
        $handler->processMessage($uri, $message);
    }

    /**
     *  Invoke the process() method
     *
     *  The performProcess() method is used internally to invoke the
     *  process() method of the given $handler with the given $uri,
     *  $content and $type.
     *
     *  @param  AnyMessageHandler   $handler    The message handler
     *  @param  string              $uri        The message/event URI
     *  @param  string              $content    The message content
     *  @param  string              $type       The message type, if any
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case processing the message has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    protected function performProcess(
        AnyMessageHandler $handler,
        $uri,
        $content,
        $type = null
    ) {
        $argumentCount = func_num_args() - 1;
        $handlerClass = get_class($handler);

        if (3 <= $argumentCount) {
            $value = $handler->process($uri, $content, $type);
        }
        else {
            $value = $handler->process($uri, $content);
        }

        $this->assertNull(
            $value, sprintf(
            "The %s::process() method must not return a value ".
            "(invoked with %d arguments)",
            $handlerClass, $argumentCount
        ));
    }

    /**
     *  Invoke the processMessage() method
     *
     *  The performProcessMessage() method is used internally to invoke
     *  the processMessage() method of the given $handler with the given
     *  $uri and $message.
     *
     *  @param  AnyMessageHandler   $handler    The message handler
     *  @param  string              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message to process
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case processing the message has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    protected function performProcessMessage(
        AnyMessageHandler $handler,
        $uri,
        AnyMessage $message
    ) {
        $handlerClass = get_class($handler);
        $value = $handler->processMessage($uri, $message);

        $this->assertNull(
            $value, sprintf(
            "The %s::processMessage() method must not return a value",
            $handlerClass
        ));
    }
}

