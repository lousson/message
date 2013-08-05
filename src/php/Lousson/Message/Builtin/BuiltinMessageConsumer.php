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
 *  Lousson\Message\Builtin\BuiltinMessageConsumer class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Builtin;

/** Interfaces: */
use Lousson\Message\AnyMessage;
use Lousson\Message\AnyMessageHandler;
use Psr\Log\LoggerInterface;

/** Dependencies: */
use Lousson\LoggerAware;
use Lousson\URI\Builtin\BuiltinURIUtil;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;

/**
 *  A logging message consumer
 *
 *  The Lousson\Message\Builtin\BuiltinMessageConsumer is an implementation
 *  of the AnyMessageHandler interface. It uses the Psr\Log\LoggerInterface
 *  to protocol any message consumed.
 *
 *  @since      lousson/Lousson_Message-2.0.0
 *  @package    org.lousson.message
 */
class BuiltinMessageConsumer
    extends LoggerAware
    implements AnyMessageHandler
{
    /**
     *  Create a consumer instance
     *
     *  The caller may provide a $logger at construction time, avoiding the
     *  need for a subsequent call to the setLogger() method.
     *
     *  @param  LoggerInterface     $logger         The logger instance
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if (isset($logger)) {
            $this->setLogger($logger);
        }
    }

    /**
     *  Process message data
     *
     *  The process() method is used to invoke the logic that processes
     *  the message $data, a byte sequence of the given mime- or media-
     *  $type, according to the event $uri provided. If the $type is not
     *  provided, implementations should assume "application/octet-stream"
     *  or may attempt to detect it.
     *
     *  @param  string              $uri        The event URI
     *  @param  string              $data       The message data
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
    public function process($uri, $data, $type = null)
    {
        try {
            BuiltinURIUtil::parseURI($uri);
        }
        catch (\Exception $error) {
            $class = get_class($error);
            $message = "Could not process message: Caught $class";
            $code = $error->getCode();
            throw new MessageArgumentError($message, $code, $error);
        }

        if (null === $type) {
            $type = "application/octet-stream";
        }

        $message = sprintf(
            "[message] handler=%s uri=%s type=%s data=%s",
            get_class($this), $uri, $type, base64_encode($data)
        );

        $logger = $this->getLogger();
        $logger->info($message);
    }

    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  @param  string              $uri        The event URI
     *  @param  AnyMessage          $message    The message instance
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
    public function processMessage($uri, AnyMessage $message)
    {
        $data = $message->getContent();
        $type = $message->getType();

        $this->process($uri, $data, $type);
    }
}

