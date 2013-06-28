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
 *  Lousson\Message\AMQP\AMQPMessageResolver class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\AMQP;

/** Interfaces: */
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolver;
use Lousson\Message\AMQP\Intern\AMQPFactory;
use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;

/**
 *  An AMQP message resolver implementation
 *
 *  The Lousson\Message\AMQP\AMQPMessageResolver class is an implementation
 *  of the AnyMessageResolver interface that specializes in AMQP resources.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPMessageResolver extends AbstractMessageResolver
{
    /**
     *  The regular expression to extract the routing key from AMQP URLs
     *
     *  @var string
     */
    const ROUTE_REGEX = "/^[^?]+\\?([^#]*&)?routing-key=([^&#]*)/";

    /**
     *  The index of the routing key in the resultset of the ROUTE_REGEX
     *
     *  @var int
     */
    const ROUTE_INDEX = 2;

    /**
     *  Create a resolver instance
     *
     *  The constructor allows the caller to provide an URI resolver for
     *  the new instance. Note that this parameter is optional: If it is
     *  absent, each URI will resolve to itself.
     *  The also optional $factory parameter can be used to provide an
     *  alternative URI factory beside the builtin one.
     *
     *  @param  AnyURIResolver      $resolver       The URI resolver
     *  @param  AnyURIFactory       $factory        The URI factory
     */
    public function __construct(
        AnyURIResolver $uriResolver = null,
        AnyURIFactory $uriFactory = null,
        AMQPFactory $amqpFactory = null
    ) {
        parent::__construct($uriResolver, $uriFactory);

        if (null === $amqpFactory) {
            $amqpFactory = new AMQPFactory();
        }

        $this->amqp = $amqpFactory;
    }

    /**
     *  Lookup message handlers
     *
     *  The lookupHandler() method is used to resolve the message
     *  handler associated with the given $uri's prefix. Classes that
     *  extend the GenericMessageResolver may override this method, in
     *  order to apply some custom logic.
     *
     *  @param  AnyURI              $uri        The URI to resolve
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler instance is returned on success,
     *          NULL otherwise
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    public function lookupHandler(AnyURI $uri)
    {
        $lexical = (string) $uri->getLexical();
        $routingKey = null;

        if (preg_match(self::ROUTE_REGEX, $lexical, $matches)) {
            $routingKey = $matches[self::ROUTE_INDEX];
        }

        if (!isset($this->handlers[$lexical][$routingKey])) {

            $scheme = (string) $uri->getPart(AnyURI::PART_SCHEME);

            if (0 === strcasecmp("amqp", $scheme)) {
                $exchange = $this->amqp->createExchange($uri);
                $instance = new AMQPMessageHandler($exchange, $routingKey);
                $this->handlers[$lexical][$routingKey] = $instance;
            }
        }

        $handler = $this->handlers[$lexical][$routingKey];
        return $handler;
    }

    /**
     *  Lookup message providers
     *
     *  The lookupHandler() method is used to resolve the message
     *  provider associated with the given $uri's prefix. Classes that
     *  extend the GenericMessageResolver may override this method, in
     *  order to apply some custom logic.
     *
     *  @param  AnyURI              $uri        The URI to resolve
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler instance is returned on success,
     *          NULL otherwise
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is considered invalid
     *
     *  @throws \RuntimeException
     *          Raised in case an internal error occurred
     */
    public function lookupProvider(AnyURI $uri)
    {
        $lexical = (string) $uri->getLexical();

        if (!isset($this->providers[$lexical])) {

            $scheme = (string) $uri->getPart(AnyURI::PART_SCHEME);

            if (0 === strcasecmp("amqp", $scheme)) {
                $queue = $this->amqp->createQueue($uri);
                $instance = new AMQPMessageProvider($queue);
                $this->providers[$lexical] = $instance;
            }
        }

        $provider = $this->providers[$lexical];
        return $provider;
    }

    /**
     *  A register for AMQPMessageHandler instances
     *
     *  @var array
     */
    private $handlers = array();

    /**
     *  A register for AMQPMessageProvider instances
     *
     *  @var array
     */
    private $providers = array();

    /**
     *  An AMQP factory instance
     *
     *  @var \Lousson\Message\AMQP\Intern\AMQPFactory
     */
    private $amqp;
}

