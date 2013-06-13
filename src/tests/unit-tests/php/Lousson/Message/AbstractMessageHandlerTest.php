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
 *  @package    org.lousson.record
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
 *  The AbstractMessageHandlerTest class serves as the base for testing
 *  implementations of the AnyMessageHandler interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.record
 */
abstract class AbstractMessageHandlerTest extends AbstractMessageTest
{
    /**
     *  The default event URI used within the test cases
     *
     *  @var string
     */
    const DEFAULT_MESSAGE_URI = "urn:lousson:test";

    /**
     *  Obtain a message factory instance
     *
     *  The getMessageHandler() method returns an instance of the message
     *  factory class that is to be tested.
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message factory instance is returned on success
     */
    abstract public function getMessageHandler();

    /**
     *  Obtain a message URI string
     *
     *  The getMessageURI() method returns a URI string that is used as
     *  the default message or event URI within data providers and tests.
     *
     *  @return string
     *          A message URI is returned on success
     */
    public function getMessageURI()
    {
        $class = get_class($this);
        $constant = "$class::DEFAULT_MESSAGE_URI";
        $uri = constant($constant);
        return $uri;
    }

    /**
     *  Provide process() parameters
     *
     *  The provideProcessParameters() method is a data provider for tests
     *  of e.g. the AnyMessageHandler::process() method.
     *  It returns an array of multiple items, each of whose is a set of
     *  valid parameters for this method, consisting of two or three items:
     *
     *- A URI string or AnyURI instance
     *- A byte sequence or string as the message content
     *- An optional internet media type string
     *
     *  @return array
     *          An array of parameter sets is returned on success
     */
    public function provideProcessParameters()
    {
        $uri = $this->getMessageURI();
        $parms = $this->provideMessageParameters();

        foreach ($parms as &$item) {
            array_unshift($item, $uri);
        }

        return $parms;
    }

    /**
     *  Provide invalid process() parameters
     *
     *  The provideInvalidProcessParameters() method is a data provider
     *  for tests of e.g. the AnyMessageHandler::process() method.
     *  It returns an array of multiple items, each of whose is a set of
     *  invalid parameters for this method, consisting of either two or
     *  three items:
     *
     *- An AnyURI instance or possibly malformed URI string
     *- A byte sequence or string as the message content
     *- An optional and possibly malformed media type string
     *
     *  @return array
     *          An array of invalid parameter sets is returned on successg
     */
    public function provideInvalidProcessParameters()
    {
        $parms[] = array(":foo", ":bar");
        $parms[] = array(":foo", ":bar", "text/plain");
        $parms[] = array("f\0\0", "b@rb@z");
        $parms[] = array("f\0\0", "b@rb@z", "application/octet-stream");

        return $parms;
    }

    /**
     *  Provide processMessage() parameters
     *
     *  The provideProcessMessageParameters() method is a data provider
     *  for tests of e.g. the AnyMessageHandler::processMessage() method.
     *  It returns an array of multiple items, each of whose is a set of
     *  valid parameters for this method, consisting of two items:
     *
     *- An URI string or AnyURI instance
     *- An instance of the AnyMessage interface
     *
     *  @return array
     *          An array of parameter sets is returned on success
     */
    public function provideProcessMessageParameters()
    {
        $data = $this->provideProcessParameters();
        $parms = array();

        foreach ($data as $item) {
            $message = $this->getMessageMock($item[1], @$item[2]);
            $parms[] = array($item[0], $message);
        }

        return $parms;
    }

    /**
     *  Test the process() method
     *
     *  The testProcess() method is a test case for the process() method
     *  of the AnyMessageHandler interface.
     *
     *  The AbstractMessageHandlerTest's default implementation, however,
     *  does not test the effect of the invocation, but only verifies that
     *  it is successful when provided with valid parameters and does not
     *  return any value.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  mixed               $data       The message data
     *  @param  string              $type       The message data type
     *
     *  @dataProvider               provideProcessParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case one of the test's assertions has failed
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testProcess($uri, $data, $type = null)
    {
        $handler = $this->getMessageHandler();
        $handlerClass = get_class($handler);

        if (3 >= func_num_args()) {
            $result = $handler->process($uri, $data, $type);
        }
        else {
            $result = $handler->process($uri, $data);
        }

        $this->assertNull(
            $result,
            "The $handlerClass::process() method must return NULL"
        );
    }

    /**
     *  Test the process() method
     *
     *  The testProcessInvalid() is a test case for the process() method
     *  of the AnyMessageHandler interface that uses invalid input data to
     *  verify the implementation's parameter handling.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  mixed               $data       The message data
     *  @param  string              $type       The message data type
     *
     *  @dataProvider               provideInvalidProcessParameters
     *  @expectedException          Lousson\Message\AnyMessageException
     *  @test
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the test is successful
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testProcessInvalid($uri, $data, $type = null)
    {
        $handler = $this->getMessageHandler();
        $handlerClass = get_class($handler);

        if (3 >= func_num_args()) {
            $result = $handler->process($uri, $data, $type);
        }
        else {
            $result = $handler->process($uri, $data);
        }
    }

    /**
     *  Test the processMessage() method
     *
     *  The testProcessMessage() method is a test case for a handler's
     *  processMessage() method, as declared by the AnyMessageHandler
     *  interface.
     *
     *  The AbstractMessageHandlerTest's default implementation, however,
     *  does not test the effect of the invocation, but only verifies that
     *  it is successful when provided with valid parameters and does not
     *  return any value.
     *
     *  @param  string              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message instance
     *
     *  @dataProvider               provideProcessMessageParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case one of the test's assertions has failed
     *
     *  @throws \Exception
     *          Raised in case of an internal error
     */
    public function testProcessMessage($uri, AnyMessage $message)
    {
        $handler = $this->getMessageHandler();
        $handlerClass = get_class($handler);
        $result = $handler->processMessage($uri, $message);

        $this->assertNull(
            $result,
            "The $handlerClass::processMessage() method must return NULL"
        );
    }

    /**
     *  Create a mock object for the tested class
     *
     *  The getHandlerMock() method is a utility that returns a mock of
     *  the class of the handler returned by getMessageHandler(). Note
     *  that one can pass multiple $method parameters, each of whose is
     *  then configurable by the standard PHPUnit facilities.
     *
     *  @param  string              $method     The method(s) to mock
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler mock is returned on success
     */
    protected function getHandlerMock($method)
    {
        $handler = $this->getMessageHandler();
        $handlerClass = get_class($handler);
        $methods = func_get_args();
        $mock = $this->getMock($handlerClass, $methods);
        return $mock;
    }
}

