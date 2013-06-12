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
use Lousson\Message\Record\RecordMessageHandlerTest;
use Lousson\Message\Record\RecordMessageProducer;
use Lousson\Record\Builtin\BuiltinRecordUtil;
use Lousson\URI\Generic\GenericURI;

/** Exceptions: */
use Lousson\Message\Error\RuntimeMessageError;
use Exception;
use DomainException;

/**
 *  A test case for the generic message handler class
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.record
 */
final class RecordMessageProducerTest extends RecordMessageHandlerTest
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
    public function getRecordMessageHandler()
    {
        $handler = $this->getMock(
            "Lousson\\Message\\Record\\RecordMessageProducer",
            array("processMessage")
        );

        $callback = function($uri, AnyMessage $message) {
            try {
                GenericURI::create($uri);
                $content = $message->getContent();
                $data = json_decode($content, true);
                BuiltinRecordUtil::normalizeData($data);
            }
            catch (Exception $error) {
                $message = $error->getMessage();
                $code = $error->getCode();
                throw new RuntimeMessageError($message, $code, $error);
            }
        };

        $handler
            ->expects($this->any())
            ->method("processMessage")
            ->will($this->returnCallback($callback));

        return $handler;
    }

    /**
     *  @dataProvider           provideProcessRecordParameters
     *  @expectedException      Lousson\Message\Error\RuntimeMessageError
     *  @test
     */
    public function testProcessValidError($uri, array $record)
    {
        $consumer = $this->getHandlerMock("processMessage");
        $consumer
            ->expects($this->once())
            ->method("processMessage")
            ->will($this->throwException(new RuntimeMessageError));

        $consumer->processRecord($uri, $record);
    }

    /**
     *  @dataProvider           provideProcessRecordParameters
     *  @expectedException      Lousson\Message\AnyMessageException
     *  @test
     */
    public function testProcessInvalidError($uri, array $record)
    {
        $consumer = $this->getHandlerMock("processMessage");
        $consumer
            ->expects($this->once())
            ->method("processMessage")
            ->will($this->throwException(new DomainException));

        $consumer->processRecord($uri, $record);
    }

    /**
     *  @dataProvider           provideProcessRecordParameters
     *  @expectedException      Lousson\Message\AnyMessageException
     *  @test
     */
    public function testFetchInvalidError($uri, array $record)
    {
        $consumer = $this->getHandlerMock("getRecordFactory");
        $consumer
            ->expects($this->once())
            ->method("getRecordFactory")
            ->will($this->throwException(new DomainException));

        $consumer->processRecord($uri, $record);
    }
}

