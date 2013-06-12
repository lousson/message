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
 *  Lousson\Message\Generic\GenericMessageHandler interface definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Interfaces: */
use Lousson\Message\AnyMessageException;
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURIException;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\Builtin\BuiltinMessageFactory;
use Lousson\Record\Builtin\BuiltinRecordFactory;
use Lousson\URI\Builtin\BuiltinURIFactory;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;
use Exception;

/**
 *  An abstract message handler implementation
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class GenericMessageHandler implements AnyMessageHandler
{
    /**
     *  Process message data
     *
     *  The process() method is used to invoke the logic that processes
     *  the message $data, a byte sequence of the given mime- or media-
     *  $type, according to the event $uri provided. If the $type is not
     *  provided, the implementation will either attempt to detect it or
     *  assume "application/octet-stream".
     *
     *  Note that the default implementation in the GenericMessageHandler
     *  class will forward the call to the processMessage() method - after
     *  creating a new message instance from the given $data and $type.
     *
     *  @param  string              $uri        The event URI
     *  @param  mixed               $data       The message data
     *  @param  string              $type       The media type
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case processing the message has failed
     */
    final public function process($uri, $data, $type = null)
    {
        $uri = $this->fetchURI($uri);
        $message = $this->fetchMessage($data, $type);

        try {
            $this->processMessage($uri, $message);
        }
        catch (AnyMessageException $error) {
            /** Allowed by the AnyMessageHandler interface */
            throw $error;
        }
        catch (Exception $error) {
            $class = get_class($error);
            $notice = "Failed to process data: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($notice, $code, $error);
        }
    }

    /**
     *  Obtain an URI factory instance
     *
     *  The getURIFactory() method returns an URI factory object. It is
     *  used within e.g. fetchURI() to parse URIs, validate them and to
     *  create instances of the AnyURI interface.
     *
     *  @return \Lousson\Message\AnyURIFactory
     *          An URI factory instance is returned on success
     */
    protected function getURIFactory()
    {
        $factory = new BuiltinURIFactory();
        return $factory;
    }

    /**
     *  Obtain a message factory instance
     *
     *  The getMessageFactory() method returns a message factory object.
     *  It is used within e.g. fetchMessage() to create instances of the
     *  AnyMessage interface.
     *
     *  @return \Lousson\Message\AnyMessageFactory
     *          A message factory instance is returned on success
     */
    protected function getMessageFactory()
    {
        $factory = new BuiltinMessageFactory();
        return $factory;
    }

    /**
     *  Convert an URI into an URI object
     *
     *  The fetchURI() method is used internally to validate the $uri
     *  provided and parse it into an instance of the AnyURI interface.
     *
     *  @param  string              $uri        The URI to parse
     *
     *  @return \Lousson\URI\AnyURI
     *          An URI instance is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the URI could not get parsed
     */
    final protected function fetchURI($uri)
    {
        if (!$uri instanceof AnyURI) try {
            $factory = $this->getURIFactory();
            $uri = $factory->getURI($uri);
        }
        catch (AnyURIException $error) {
            $message = "Failed to parse URI: ". $error->getMessage();
            $code = $error->getCode();
            throw new InvalidMessageError($message, $code, $error);
        }
        catch (Exception $error) {
            $class = get_class($error);
            $message = "Failed to parse URI: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }

        return $uri;
    }

    /**
     *  Create a message object from arbitrary data
     *
     *  The fetchMessage() method is used internally to create a message
     *  object, an instance of the AnyMessage interface, from the $data and
     *  $type provided.
     *
     *  @param  string              $data       The message data
     *  @param  string              $type       The message media type
     *
     *  @return \Lousson\Message\AnyMessage
     *          A message instance is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the message could not get created
     */
    final protected function fetchMessage($data, $type = null)
    {
        try {
            $factory = $this->getMessageFactory();
            $message = $factory->getMessage($data, $type);
        }
        catch (AnyMessageException $error) {
            /** Allowed by the AnyMessageHandler interface */
            throw $error;
        }
        catch (Exception $error) {
            $class = get_class($error);
            $notice = "Failed to prepare message: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($notice, $code, $error);
        }

        return $message;
    }
}

