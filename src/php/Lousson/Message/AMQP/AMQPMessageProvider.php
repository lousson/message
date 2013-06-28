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
 *  Lousson\Message\AMQP\AMQPMessageProvider class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\AMQP;

/** Interfaces: */
use Lousson\URI\AnyURIFactory;

/** Dependencies: */
use AMQPEnvelope;
use AMQPQueue;
use Lousson\Message\AbstractMessageProvider;
use Lousson\Message\Builtin\BuiltinMessageStash;
use Lousson\Message\Generic\GenericMessage;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;

/**
 *  An AMQP message provider implementation
 *
 *  The Lousson\Message\AMQP\AMQPMessageProvider is a message provider
 *  implementation based on AMQP queues.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPMessageProvider
    extends AbstractMessageProvider
{
    /**
     *  Create a provider instance
     *
     *  The constructor requires the caller to provide the AMQPQueue the
     *  provider shall operate on. It also allows the provisioning of a
     *  custom URI factory, to be used instead of the builtin one.
     *
     *  @param  AMQPQueue           $queue      The AMQP queue instance
     *  @param  AnyURIFactory       $factory    The optional URI factory
     */
    public function __construct(
        AMQPQueue $queue,
        AnyURIFactory $factory = null
    ) {
        parent::__construct($factory);

        $this->stash = new BuiltinMessageStash();
        $this->queue = $queue;
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
        $message = null;

        try {
            $envelope = $this->queue->get(AMQP_NOPARAM);
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $notice = "Could not fetch message: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($notice, $code, $error);
        }

        if ($envelope) {

            $deliveryTag = $envelope->getDeliveryTag();
            $message = $this->fetchMessage($envelope);

            if (self::FETCH_CONFIRM & (int) $flags) {
                $token = $this->stash->store($deliveryTag);
            }
            else try {
                $status = $this->queue->ack($deliveryTag);
                assert(false !== $status);
            }
            catch (\AMQPException $error) {
                $notice = "Failed to ack/nack AMQP message: Caught $error";
                trigger_error($notice, E_USER_WARNING);
            }
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
        $deliveryTag = $this->stash->restore($token, __FUNCTION__);

        try {
            $setup = ini_set("track_errors", true);
            $php_errormsg = "UNKNOWN ERROR";
            $status = @$this->queue->ack($deliveryTag);
            $error = $php_errormsg;
            ini_set("track_errors", $setup);
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $message = "Could not acknowledge message: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }

        if (!$status) {
            $message = "Error while acknowledgeing token $token: $error";
            $code = RuntimeMessageError::E_UNKNOWN;
            throw new RuntimeMessageError($message, $code);
        }
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
        static $map = array(
            self::DISC_DEFAULT => AMQP_NOPARAM,
            self::DISC_REQUEUE => AMQP_REQUEUE,
        );

        $deliveryTag = $this->stash->restore($token, __FUNCTION__);

        try {
            $setup = ini_set("track_errors", true);
            $php_errormsg = "UNKNOWN ERROR";
            $status = @$this->queue->nack(
                $deliveryTag,
                $map[self::DISC_REQUEUE & $flags]
            );
            $error = $php_errormsg;
            ini_set("track_errors", $setup);
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $message = "Could not discard message: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }

        if (!$status) {
            $message = "Error while discarding token $token: $error";
            $code = RuntimeMessageError::E_UNKNOWN;
            throw new RuntimeMessageError($message, $code);
        }
    }

    /**
     *  Fetch a message instance
     *
     *  The fetchMessage() method is used internally to aggregate a message
     *  instance from the data provided by an AMQP $envelope.
     *
     *  @param  AMQPException       $envelope       The AMQP envelope
     *
     *  @return \Lousson\Message\Generic\GenericMessage
     *          A message instance is returned on success
     */
    private function fetchMessage(AMQPEnvelope $envelope)
    {
        $content = $envelope->getBody();
        $type = $envelope->getContentType();
        $message = new GenericMessage($content, $type);
        return $message;
    }

    /**
     *  The AMQPQueue instance to fetch messages from
     *
     *  @var \AMQPQueue
     */
    private $queue;

    /**
     *  The message stash managing delivery tags
     *
     *  @var \Lousson\Message\Builtin\BuiltinMessageStash
     */
    private $stash;
}

