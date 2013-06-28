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
 *  Lousson\Message\AbstractMessageFactoryTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Dependencies: */
use Lousson\Message\AbstractMessageTest;

/**
 *  An abstract test case for message factories
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class AbstractMessageFactoryTest extends AbstractMessageTest
{
    /**
     *  Obtain a message factory instance
     *
     *  The getMessageFactory() method returns an instance of the message
     *  factory class that is to be tested.
     *
     *  @return \Lousson\Message\AnyMessageFactory
     *          A message factory instance is returned on success
     */
    abstract public function getMessageFactory();

    /**
     *  Test the getMessage() method
     *
     *  The testGetMessage() method is a test case for getMessage() that
     *  verifies the retuned object being an instance of the AnyMessage
     *  interface.
     *
     *  @param  string              $data       The message data
     *  @param  string              $type       The message media type
     *
     *  @dataProvider               provideValidMessageData
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testGetMessage($data, $type = null)
    {
        $factory = $this->getMessageFactory();
        $this->assertInstanceOf(
            "Lousson\\Message\\AnyMessageFactory", $factory,
            "The test's ::getMessageFactory() method must return a ".
            "message factory instance"
        );

        if (2 >= func_num_args()) {
            $message = $factory->getMessage($data, $type);
        }
        else {
            $message = $factory->getMessage($data);
        }

        $factoryClass = get_class($factory);
        $this->assertInstanceOf(
            "Lousson\\Message\\AnyMessage", $message,
            "The $factoryClass::getMessage() method must return an ".
            "instance of the AnyMessage interface"
        );
    }
}

