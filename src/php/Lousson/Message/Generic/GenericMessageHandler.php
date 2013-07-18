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
 *  Lousson\Message\Generic\GenericMessageHandler class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Interfaces: */
use Lousson\Message\AnyMessageFactory;
use Lousson\Message\AnyMessageResolver;
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURIFactory;

/** Dependencies: */
use Lousson\Message\AbstractMessageHandler;

/** Exceptions: */
use Lousson\Message\Error\MessageRuntimeError;

/**
 *  A generic message handler implementation
 *
 *  The Lousson\Message\Generic\GenericMessageHandler is an implementation
 *  of the AnyMessageHandler interface that acts as a proxy:
 *  A message resolver, provided at construction time, is used to determine
 *  the actual handler to process messages - at runtime.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class GenericMessageHandler extends AbstractMessageHandler
{
    /**
     *  Create a handler instance
     *
     *  The constructor requires the caller to provide a message $resolver
     *  instance, which will get used to determine the actual handler to be
     *  used when the process() or processMessage() method is invoked.
     *
     *  One can also provide custom message and URI factories, to be used
     *  internally - instead of the builtin ones.
     *
     *  @param  AnyMessageResolver  $resolver       The message resolver
     *  @param  AnyMessageFactory   $messageFactory The message factory
     *  @param  AnyURIFactory       $uriFactory     The URI factory
     */
    public function __construct(
        AnyMessageResolver $resolver,
        AnyMessageFactory $messageFactory = null,
        AnyURIFactory $uriFactory = null
    ) {
        parent::__construct($messageFactory, $uriFactory);
        $this->resolver = $resolver;
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
        if ($handler = $this->resolver->resolveHandler($uri)) try {
            $handler->processMessage($uri, $message);
        }
        catch (\Lousson\Message\AnyMessageException $error) {
            /* Allowed by the AnyMessageHandler interface */
            throw $error;
        }
        catch (\Exception $error) {
            $class = get_class($error);
            $description = "Could not process message: Caught $class";
            $code = MessageRuntimeError::E_UNKNOWN;
            throw new MessageRuntimeError($description, $code, $error);
        }
        else {
            $description = "Could not process message: No handler found";
            $code = MessageRuntimeError::E_INVALID;
            throw new MessageRuntimeError($description, $code);
        }
    }

    /**
     *  The message resolver instance
     *
     *  @var \Lousson\Message\AnyMessageResolver
     */
    private $resolver;
}

