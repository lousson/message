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
 *  Lousson\Message\Generic\GenericMessageTest class definition
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
use Lousson\Message\Generic\GenericMessage;

/**
 *  A test case for the generic message class
 *
 *  The Lousson\Message\Generic\GenericMessageTest is a test case for the
 *  generic implementation of the AnyMessage interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class GenericMessageTest extends AbstractMessageTest
{
    /**
     *  Provide message data parameters
     *
     *  The provideGetContentParameters() method returns an array of one
     *  or more items, each of whose is an array of one, two or three:
     *
     *- Message data, usually a byte sequence, for getMessage()
     *- A media type string, for getMessage()
     *- The expected return value of getContent()
     *
     *  @return array
     *          A list of message data parameters is returned on success
     */
    public function provideGetContentParameters()
    {
        $data = $this->provideValidMessageData();

        $data[] = array(null, null, null);
        $data[] = array("f\0\0bar", null, "f\0\0bar");

        return $data;
    }

    /**
     *  Provide message type parameters
     *
     *  The provideGetTypeParameters() method returns an array of one
     *  or more items, each of whose is an array of one, two or three:
     *
     *- Message data, usually a byte sequence, for getMessage()
     *- A media type string, for getMessage()
     *- The expected return value of getType()
     *
     *  @return array
     *          A list of message type parameters is returned on success
     */
    public function provideGetTypeParameters()
    {
        $data = $this->provideValidMessageData();
        $type = "application/octet-stream";

        $data[] = array(null, null, $type);
        $data[] = array("f\0\0bar", $type, $type);

        return $data;
    }

    /**
     *  Test the getContent() method
     *
     *  The testGetContent() method is a smoke-test for the message class'
     *  getContent() method. It operates on a set of (valid) message $data
     *  and, optionally, predefined media $type strings.
     *
     *  The $expected parameter can be used to specify what value shall
     *  be returned by getContent(). If provided, the test will include
     *  an assertion for equality by default.
     *
     *  @param  mixed               $data       The message data
     *  @param  string              $type       The message data type
     *  @param  mixed               $expected   The type expected
     *
     *  @dataProvider               provideGetContentParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testGetContent($data, $type = null, $expected = null)
    {
        $message = new GenericMessage($data, $type);
        $content = $message->getContent();

        $isString = $this->isType("string");
        $isStringOrNull = $this->logicalOr($isString, $this->isNull());
        $constraint = "::getContent() method must return a string value";
        $this->assertThat($content, $isStringOrNull, $constraint);

        if (3 <= func_num_args()) {
            $constraint = "::getContent() must return the expected value";
            $this->assertEquals($expected, $content, $constraint);
        }
    }

    /**
     *  Test the getType() method
     *
     *  The testGetType() method is a smoke-test for the message class'
     *  getType() method. It operates on a set of (valid) message $data
     *  and, optionally, predefined media $type strings.
     *
     *  The $expected parameter can be used to specify what value shall
     *  be returned by getType(). If provided, the test will include
     *  an assertion for equality by default.
     *
     *  @param  mixed               $data       The message data
     *  @param  string              $type       The message data type
     *  @param  string              $expected   The message type expected
     *
     *  @dataProvider               provideGetTypeParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testGetType($data, $type = null, $expected = null)
    {
        $message = new GenericMessage($data, $type);
        $type = $message->getType();
        $constraint = "::getType() must return a string value";
        $this->assertInternalType("string", $type, $constraint);

        if (3 <= func_num_args()) {
            $constraint = "::getType() must return the expected value";
            $this->assertEquals($expected, $type, $constraint);
        }
    }
}

