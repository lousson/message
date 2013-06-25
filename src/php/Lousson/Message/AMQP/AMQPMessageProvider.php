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
 *  Lousson\Message\AMQP\AMQPMessageHandler class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Carlos Ascaso <carlos.ascaso@sedo.de>
 *  @filesource
 */
namespace Lousson\Message\AMQP;

/** Dependencies: */
use Lousson\Message\AMQP\Impl\AMQPObjectsFactory;
use Lousson\Message\AMQP\URI\AMQPURIParser;
use Lousson\URI\Generic\GenericURI;

/** Interfaces: */
use Lousson\Message\AnyMessageProvider;
use Lousson\Message\AnyMessage;
use Lousson\URI\AnyURI;

/** Exceptions: */
use Lousson\Message\AMQP\Error\AMQPRuntimeError;
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;
use Lousson\URI\AnyURIException;

/**
 *  Message handler implementation for publishing messages into an AMQP exchange
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPMessageProvider implements AnyMessageProvider 
{
    /**
     *  Create a new instance of the AMQP message handler.
     *
     *  @param  AnyURI              $uri
     *  @param  AMQPURIParser       $amqpURIParser
     *  @param  AMQPObjectsFactory  $amqpObjectsFactory
     */
    public function __construct(
        AMQPURIParser $amqpURIParser,
        AMQPObjectsFactory $amqpObjectsFactory
    )
    {
        $this->amqpURIParser = $amqpURIParser;
        $this->amqpObjectsFactory = $amqpObjectsFactory;
    }

    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  @param  string              $uri        The event URI
     *  @param  int                 $flags      The option bitmask
     *  @param  mixed               $token      The delivery token
     *
     *  @throws \Lousson\Message\Error\RuntimeMessageError
     *          Raised in case fetching the message has failed
     */
    public function fetch(
        $uri, $flags = self::FETCH_DEFAULT, &$token = null
    )
    {

        try {
            if ( false === isset($this->queue)){
                $this->buildQueue($uri);
            }

            $msg = $this->queue->get();
            if (false === $msg) {
                return null;
            }

            $token = $msg->getDeliveryTag();
            return $msg->getBody();
        }
        catch (AMQPRuntimeError $error) {
            $message = 'Impl AMQP error:' . $error->getMessage() ;
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }

    }

    /**
     *  Buildd the queue object
     *
     *  @param  string     $uri        The event URI
     *
     *  @throws \Lousson\Message\AnyURIException
     *          Raised in case processing the URI has failed
     *
     *  @throws \Lousson\Message\RuntimeMessageError
     *          Raised in case creating the queue has failed
     */
    public function buildQueue($uri)
    {
        $uriObj = $this->fetchURI($uri);

        try {
            $amqpURI = $this->amqpURIParser->parseAnyURI($uriObj);

        }
        catch (AnyURIException $error) {
            $message = "Parsing the URI $uri failed";
            $code = AnyURIException::E_INVALID;
            throw new InvalidMessageError($message, $code, $error);
        }

        $queueName = $amqpURI->getQueue();
        if (!$queueName) {
            $message = "The given URI $uri contains no queue information.";
            throw new InvalidMessageError($message);
        }

        try {
            $this->queue =
                $this->amqpObjectsFactory->createQueue($queueName);
        }
        catch (AMQPRuntimeError $error) {
            $message = 'Impl AMQP error creatinf the queue';
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }
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
     *  @throws \Lousson\Message\RuntimeMessageException
     *          Raised in case the acknowledgement has failed
     */
    public function acknowledge($token, $flags = self::ACK_DEFAULT){

        try {
            $this->queue->ack($token);
        }
        catch (AMQPChannelException $error) {
            $message = 'Impl AMQP error. channel is not open:'.
                $error->getMessage();
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }
        catch (AMQPConnectionException $error) {
            $message = 'Impl AMQP error. Connection lost: '.
                $error->getMessage();
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
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
     *          Raised in case discarding the message has failed
     */
    public function discard($token, $flags = self::DISC_DEFAULT){

        try {
            $this->queue->nack($token);
        }
        catch (AMQPChannelException $error) {
            $message = 'Impl AMQP error. channel is not open:'.
                $error->getMessage();
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }
        catch (AMQPConnectionException $error) {
            $message = 'Impl AMQP error. Connection lost: '.
                $error->getMessage();
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($message, $code, $error);
        }
    }

    /**
     * Fetch the URI
     *
     * @param  string 
     * @return AnyURI 
     */
    protected function fetchURI($uri){

        return GenericURI::create($uri);
    }

    /**
     *  The Queue must be cached. 
     *
     *  there is only a limited number of channels per connection.
     *  Without caching, a client running the fetch method in a loop without ack 
     *  will hit the maximal number of channels
     *
     *  @var  array of \AMQPQueue
     */
    private $queues = array();

    /**
     *  @var AMQPObjectsFactory
     */
    private $amqpObjectsFactory;

    /**
     *  @var AMQPURIParser
     */
    private $amqpURIParser;
}
