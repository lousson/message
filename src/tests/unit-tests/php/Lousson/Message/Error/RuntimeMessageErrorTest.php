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
 *  Lousson\Message\Error\RuntimeMessageErrorTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Error;

/** Dependencies: */
use Lousson\AbstractExceptionTest;
use ReflectionClass;

/**
 *  A test case for the RuntimeMessageError class
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class RuntimeMessageErrorTest extends AbstractExceptionTest
{
    /**
     *  Obtain the exception to test
     *
     *  The getException() method returns the exception instance to be
     *  tested, according to the given $args - e.g. as provided by the
     *  provideExceptionParameters() method).
     *
     *  @param  array               $args       The exception arguments
     *
     *  @return \Exception
     *          An exception instance is returned on success
     */
    public function getException(array $args)
    {
        $class = "Lousson\\Message\\Error\\RuntimeMessageError";
        $reflection = new ReflectionClass($class);
        $instance = $reflection->newInstanceArgs($args);
        return $instance;
    }

    /**
     *  Obtain a list of implemented interfaces
     *
     *  The getExpectedInterfaces() method returns a list of zero or more
     *  interface names, each referring to an interface the exception that
     *  is returned by the getException() method is expected to implement.
     *
     *  @return array
     *          A list of interface names is returned on success
     */
    public function getExpectedInterfaces()
    {
        $interfaces = parent::getExpectedInterfaces();
        $interfaces[] = "Lousson\\Message\\AnyMessageException";

        return $interfaces;
    }
}

