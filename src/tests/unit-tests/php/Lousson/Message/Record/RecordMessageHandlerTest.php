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
 *  Lousson\Message\Record\RecordMessageProducerTest class definition
 *
 *  @package    org.lousson.record
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Record;

/** Interfaces: */
use Lousson\Message\AnyMessage;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandlerTest;
use Lousson\Message\Generic\GenericMessage;

/**
 *  An abstract test case for record message handlers
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.record
 */
abstract class RecordMessageHandlerTest extends AbstractMessageHandlerTest
{
    /**
     *  Obtain a record message handler instance
     *
     *  The getRecordMessageHandler() method returns an instance of the
     *  record message handler class that is to be tested.
     *
     *  @return \Lousson\Message\Record\RecordMessageHandler
     *          A record message handler instance is returned on success
     */
    abstract public function getRecordMessageHandler();

    /**
     *  Obtain a message handler instance
     *
     *  The getMessageHandler() method returns an instance of the message
     *  handler class that is to be tested.
     *
     *  @return \Lousson\Message\Record\RecordMessageHandler
     *          A record message handler instance is returned on success
     */
    final public function getMessageHandler()
    {
        $testClass = get_class($this);
        $handler = $this->getRecordMessageHandler();

        $this->assertInstanceOf(
            "Lousson\\Message\\Record\\RecordMessageHandler", $handler,
            "The $testClass::getRecordMessageHandler() method must ".
            "return an instance of the RecordMessageHandler interface"
        );

        return $handler;
    }

    /**
     *  Provide message parameters
     *
     *  The provideMessageParameters() method returns an array of one
     *  or more items, each of whose is an array of either one or two:
     *
     *- Message data, usually a byte sequence, for getMessage()
     *- A media type string, for getMessage()
     *
     *  @return array
     *          A list of message parameters is returned on success
     */
    public function provideMessageParameters()
    {
        $type = "application/json";

        $data[] = array('{"foo":"bar","baz":[1,2,3]}', $type);
        $data[] = array('{"foo":"bar","baz":[]}', $type);

        return $data;
    }

    /**
     *
     */
    public function provideProcessRecordParameters()
    {
        $uri = "urn:lousson:test";

        $data[] = array($uri, array());
        $data[] = array($uri, array("foo" => "bar", "baz" => array()));
        $data[] = array($uri, array("foo" => array("bar", "baz")));
        $data[] = array($uri, array("foo" => null));
        $data[] = array($uri, array("foo" => array("bar" => null)));

        return $data;
    }

    /**
     *
     */
    public function provideInvalidProcessRecordParameters()
    {
        $uri = "urn:lousson:test";

        $data[] = array($uri, array(1, 2, 3));
        $data[] = array($uri, array("foo" => array(1, "bar" => "baz")));

        return $data;
    }

    /**
     *
     */
    public function provideInvalidProcessMessageParameters()
    {
        $data = $this->provideInvalidProcessRecordParameters();
        $type = "application/json";

        foreach ($data as &$item) {
            $item[1] = json_encode($item[1]);
            $item[1] = new GenericMessage($item[1], $type);
        }

        return $data;
    }

    /**
     *  @param  string              $uri        The message/event URI
     *  @param  array               $record     The message data record
     *
     *  @dataProvider               provideProcessRecordParameters
     *  @test
     */
    public function testProcessRecord($uri, array $record)
    {
        $handler = $this->getMessageHandler();
        $handlerClass = get_class($handler);
        $result = $handler->processRecord($uri, $record);

        $this->assertNull(
            $result,
            "The $handlerClass::processRecord() method must return NULL"
        );
    }

    /**
     *  @param  string              $uri        The message/event URI
     *  @param  array               $record     The message data record
     *
     *  @dataProvider               provideInvalidProcessRecordParameters
     *  @expectedException          Lousson\Message\AnyMessageException
     *  @test
     */
    public function testProcessInvalidRecord($uri, array $record)
    {
        $this->testProcessRecord($uri, $record);
    }

    /**
     *  @param  string              $uri        The message/event URI
     *  @param  AnyMessage          $message    The message itself
     *
     *  @dataProvider               provideInvalidProcessMessageParameters
     *  @expectedException          Lousson\Message\AnyMessageException
     *  @test
     */
    public function testProcessInvalidMessage($uri, AnyMessage $message)
    {
        $this->testProcessMessage($uri, $message);
    }
}

