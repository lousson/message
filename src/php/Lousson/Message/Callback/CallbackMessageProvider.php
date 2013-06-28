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
 *  Lousson\Message\Callback\CallbackMessageProvider class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Callback;

/** Interfaces: */
use Lousson\Message\AnyMessage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/** Dependencies: */
use Lousson\Message\AbstractMessageProvider;
use Lousson\Message\Builtin\BuiltinMessageStash;
use Closure;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;

/**
 *  A callback message provider implementation
 *
 *  The Lousson\Message\Callback\CallbackMessageProvider implements the
 *  AnyMessageProvider interface based on a Closure callback provided at
 *  construction time.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class CallbackMessageProvider
    extends AbstractMessageProvider
    implements LoggerAwareInterface
{
    /**
     *  Create a provider instance
     *
     *  The constructor requires to be provided with a Closure to be used
     *  as the actual implementation of the fetch() method. This $callback
     *  must accept one parameter, an instance of the AnyURI interface,
     *  and return an instance of the AnyMessage interface or NULL.
     *
     *  Optionally, one can also specify the URI $factory the message
     *  provider shall operate with - instead of the builtin default.
     *
     *  @param  Closure             $callback   The fetch() callback
     *  @param  AnyURIFactory       $factory    The URI factory to use
     */
    public function __construct(
        Closure $callback,
        AnyURIFactory $factory = null
    ) {
        parent::__construct($factory);

        $provider = $this;
        $notifier = function(array $stash) use ($provider) {
            if ($logger = $provider->getLogger()) {
                foreach ($stash as $key => $tuple) {
                    $warning = "Discarded message {key} from \"{uri}\"";
                    $context = array("key" => $key, "uri" => $tuple[0]);
                    $logger->warning($warning, $context);
                }
            }
        };

        $this->stash = new BuiltinMessageStash($notifier);
        $this->callback = $callback;
    }

    /**
     *  Assign a logger instance
     *
     *  The setLogger() method is used to assign an instance of the PSR-3
     *  LoggerInterface to the message provider. This logger is used e.g.
     *  to protocol discarded messages
     *
     *  @param  LoggerInterface     $logger     The logger instance
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *  Obtain a logger instance
     *
     *  The getLogger() method is used to obtain the logger instance that
     *  has been assigned via setLogger(), if any.
     *
     *  @return \Psr\Log\LoggerInterface
     *          A logger instance is returned on success, NULL otherwise
     */
    public function getLogger()
    {
        return $this->logger;
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
    final public function fetch(
        $uri,
        $flags = self::FETCH_DEFAULT,
        &$token = null
    ) {
        $uri = $this->fetchURI($uri);
        $key = $uri->getLexical();

        if (!empty($this->requeued[$key])) {
            $message = array_shift($this->requeued[$key]);
        }
        else {
            $callback = $this->callback;
            $message = $callback($uri);
        }

        if (isset($message) && self::FETCH_CONFIRM & (int) $flags) {
            $token = $this->stash->store(array($uri, $message));
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
    final public function acknowledge($token, $flags = self::ACK_DEFAULT)
    {
        $this->stash->restore($token, __FUNCTION__);
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
    final public function discard($token, $flags = self::DISC_DEFAULT)
    {
        list($uri, $message) = $this->stash->restore($token, __FUNCTION__);

        if (self::DISC_REQUEUE & (int) $flags) {
            $this->requeued[(string) $uri][] = $message;
        }
        else if ($logger = $this->getLogger()) {
            $notice = "Discarded message {token} from \"{uri}\"";
            $context = array("token" => $token, "uri" => $uri);
            $logger->warning($notice, $context);
        }
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
        foreach ($this->requeued as $uri => &$list) {
            while (!empty($list)) {
                $this->fetch($uri, self::FETCH_CONFIRM, $token);
            }
        }

        /* Force stash cleanup here and now */
        unset($this->stash);
    }

    /**
     *  A stash for managing pending message tokens
     *
     *  @var \Lousson\Message\Builtin\BuiltinMessageStash
     */
    private $stash;

    /**
     *  A map of requeued messages per-URI
     *
     *  @var array
     */
    private $requeued = array();

    /**
     *  The callback invoked by fetch()
     *
     *  @var \Closure
     */
    private $callback;

    /**
     *  The provider's logger instance, if any
     *
     *  @var \Psr\Log\LoggerInterface
     */
    private $logger;
}

