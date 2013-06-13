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
 *  Lousson\Message\AbstractMessageTest class definition
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
 *  An abstract test case for message classes
 *
 *  The AbstractMessageTest class is as a base for test implementations
 *  for the interfaces in the Lousson\Message namespace. It provides a set
 *  of utilities that should ease the tasks of test authors.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.record
 *  @link       http://www.phpunit.de/manual/current/en/
 */
abstract class AbstractMessageTest extends PHPUnit_Framework_TestCase
{
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
        $data[][] = "foobar";
        $data[][] = "f\0\0bar";
        $data[][] = null;

        $data[] = array("foobar", "text/plain");
        $data[] = array("f\0\0bar", "application/octet-stream");
        $data[] = array(null, "application/xml+xhtml");
        $data[] = array(null, null);

        return $data;
    }

    /**
     *  Create an AnyMessage mock
     *
     *  The getMessageMock() method returns a mock of the AnyMessage
     *  interface, one whose getContent() and getType() methods return
     *  the $content and $type provided.
     *
     *  @param  string              $content    The message content
     *  @param  string              $type       The message type
     *
     *  @return \Lousson\Message\AnyMessage
     *          A message mock instance is returned on success
     */
    protected function getMessageMock($content = null, $type = null)
    {
        $message = $this->getMock(
            "Lousson\\Message\\AnyMessage",
            array("getContent", "getType")
        );

        $message
            ->expects($this->any())
            ->method("getContent")
            ->will($this->returnValue($content));

        $message
            ->expects($this->any())
            ->method("getType")
            ->will($this->returnValue($type));

        return $message;
    }
}

