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
 *  Lousson\Message\Generic\GenericMessageProvider class definition
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
use Lousson\Message\AbstractMessageProvider;
use Lousson\Message\Builtin\BuiltinMessageStash;

/** Exceptions: */
use Lousson\Message\Error\MessageRuntimeError;

/**
 *  A generic message provider implementation
 *
 *  The Lousson\Message\Generic\GenericMessageProvider is an implementation
 *  of the AnyMessageProvider interface that acts as a proxy:
 *  A message resolver, provided at construction time, is used to determine
 *  the actual provider to process messages - at runtime.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class GenericMessageProvider extends AbstractMessageProvider
{
    /**
     *  Create a provider instance
     *
     *  The constructor requires the caller to provide a message $resolver
     *  instance, which will get used to determine the actual provider to
     *  be used when the fetch() method is invoked.
     *
     *  One can also provide a custom URI factory, to be used internally -
     *  instead of the builtin ones.
     *
     *  @param  AnyMessageResolver  $resolver       The message resolver
     *  @param  AnyURIFactory       $uriFactory     The URI factory
     */
    public function __construct(
        AnyMessageResolver $resolver,
        AnyURIFactory $uriFactory = null
    ) {
        parent::__construct($uriFactory);

        $callback = function(array $stash) {
            foreach ($stash as $key => $tuple) {
                $tuple[0]->discard($tuple[1]);
            }
        };

        $this->resolver = $resolver;
        $this->stash = new BuiltinMessageStash($callback);
    }

    /**
     *  Retrieve message instances
     *
     *  The fetch() method is used to obtain the next message that is
     *  associated with the given event $uri. The $flags parameter can
     *  be used to request special behavior:
     *
     *- AnyMessageProvider::FETCH_CONFIRM
     *  Populate the $token reference for acknowledge() or discard()
     *
     *  @param  string              $uri        The event URI
     *  @param  int                 $flags      The option bitmask
     *  @param  mixed               $token      The delivery token
     *
     *  @return \Lousson\Message\AnyMessage
     *          A message instance is returned on success, or NULL in
     *          case no more messages are available for the given $uri
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
    public function fetch(
        $uri, $flags = self::FETCH_DEFAULT, &$token = null
    ) {
        if ($provider = $this->resolver->resolveProvider($uri)) try {
            $message = $provider->fetch($uri, $flags, $newToken);
        }
        catch (\Lousson\Message\AnyMessageException $error) {
            /* Allowed by the AnyMessageHandler interface */
            throw $error;
        }
        catch (\Exception $error) {
            $class = get_class($error);
            $description = "Could not fetch message: Caught $class";
            $code = MessageRuntimeError::E_UNKNOWN;
            throw new MessageRuntimeError($description, $code, $error);
        }
        else {
            $description = "Could not fetch message: No provider found";
            $code = MessageRuntimeError::E_INVALID;
            throw new MessageRuntimeError($description, $code);
        }

        if (self::FETCH_CONFIRM & (int) $flags) {
            if (null !== $newToken)
            $token = $this->stash->store(array($provider, $newToken));
        }

        return $message;
    }

    /**
     *  Acknowledge a message
     *
     *  The acknowledge() method is used to tag a message (formerly
     *  received via fetchToken() on the same instance) as acknowledged.
     *  This is usually done after the processing of the message, when one
     *  knows that no further errors can occur.
     *
     *  The optional $flags parameter can be used to request special
     *  behavior (none defined yet).
     *
     *  @param  mixed               $token      The delivery token
     *  @param  int                 $flags      The option bitmask
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
    public function acknowledge($token, $flags = self::ACK_DEFAULT)
    {
        $data = $this->stash->restore($token, __FUNCTION__);
        $data[0]->acknowledge($data[1], $flags);
    }

    /**
     *  Discard a message
     *
     *  The discard() method is used to tag a message (formerly received
     *  via fetch() or fetchToken() on the same instance) as discarded.
     *  This is usually done if either the message was invalid or some
     *  internal error occured (in combination with DISC_REQUEUE).
     *  behavior:
     *
     *- AnyMessageProvider::DISC_REQUEUE
     *  Re-queue the message for the next call to fetch()
     *
     *  @param  mixed               $token      The delivery token
     *  @param  int                 $flags      The option bitmask
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
    public function discard($token, $flags = self::DISC_DEFAULT)
    {
        $data = $this->stash->restore($token, __FUNCTION__);
        $data[0]->discard($data[1], $flags);
    }

    /**
     *  Cleanup
     *
     *  The constructor has been implemented explicitely in order to take
     *  care of any messages not yet confirmed/discarded - or re-queued in
     *  a former operation but never fetched again.
     */
    public function __destruct()
    {
        /* Force stash cleanup here and now */
        unset($this->stash);
    }

    /**
     *  The message resolver instance
     *
     *  @var \Lousson\Message\AnyMessageResolver
     */
    private $resolver;

    /**
     *  A message stash for tracking unconfirmed messages
     *
     *  @var \Lousson\Message\Builtin\BuiltinMessageStash
     */
    private $stash;
}

