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
 *  Lousson\Message\AbstractMessageHandler class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Interfaces: */
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessageFactory;
use Lousson\URI\AnyURIFactory;

/** Dependencies: */
use Lousson\Message\Builtin\BuiltinMessageFactory;
use Lousson\URI\Builtin\BuiltinURIFactory;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;

/**
 *  An abstract message handler implementation
 *
 *  The Lousson\Message\AbstractMessageHandler class implements the API
 *  specified by the AnyMessageHandler interface as far as possible -
 *  without assuming too many implementation details. This might ease the
 *  creation of new message handlers.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class AbstractMessageHandler implements AnyMessageHandler
{
    /**
     *  Create a handler instance
     *
     *  The constructor allows the provisioning of custom message and URI
     *  factory instances for the provider to operate on - instead of the
     *  builtin default.
     *
     *  @param  AnyMessageFactory   $messageFactory The message factory
     *  @param  AnyURIFactory       $uriFactory     The URI factory
     */
    public function __construct(
        AnyMessageFactory $messageFactory = null,
        AnyURIFactory $uriFactory = null
    ) {
        if (null === $messageFactory) {
            $messageFactory = new BuiltinMessageFactory();
        }

        if (null === $uriFactory) {
            $uriFactory = new BuiltinURIFactory();
        }

        $this->messageFactory = $messageFactory;
        $this->uriFactory = $uriFactory;
    }

    /**
     *  Process message data
     *
     *  The process() method is used to invoke the logic that processes
     *  the message $data, a byte sequence of the given mime- or media-
     *  $type, according to the event $uri provided. If the $type is not
     *  provided, the implementation will either attempt to detect it or
     *  assume "application/octet-stream".
     *
     *  Note that the default implementation in the AbstractMessageHandler
     *  class will forward the call to the processMessage() method - after
     *  creating a new message instance from the given $data and $type.
     *
     *  @param  string              $uri        The event URI
     *  @param  mixed               $data       The message data
     *  @param  string              $type       The media type
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case an argument is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    final public function process($uri, $data, $type = null)
    {
        $uri = $this->fetchURI($uri);
        $message = $this->fetchMessage($data, $type);
        $this->processMessage($uri, $message);
    }

    /**
     *  Convert an URI into an URI object
     *
     *  The fetchURI() method is used internally to validate the $uri
     *  provided and to parse it into an instance of the AnyURI interface.
     *
     *  @param  string              $uri        The URI to parse
     *
     *  @return \Lousson\URI\AnyURI
     *          An URI instance is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is considered invalid
     */
    final protected function fetchURI($uri)
    {
        if (!$uri instanceof \Lousson\URI\AnyURI) try {
            $uri = $this->uriFactory->getURI($uri);
        }
        catch (\Lousson\URI\AnyURIException $error) {
            $message = $error->getMessage();
            $code = $error->getCode();
            throw new MessageArgumentError($message, $code, $error);
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
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case an argument is considered invalid
     */
    final protected function fetchMessage($data, $type = null)
    {
        $factory = $this->messageFactory;
        $message = $factory->getMessage($data, $type);
        return $message;
    }

    /**
     *  The provider's message factory instance
     *
     *  @var \Lousson\Message\AnyMessageFactory
     */
    private $messageFactory;

    /**
     *  The provider's URI factory instance
     *
     *  @var \Lousson\URI\AnyURIFactory
     */
    private $uriFactory;
}

