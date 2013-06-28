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
 *  Lousson\Message\AMQP\Intern\AMQPFactory class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Benjamin Schneider <benjamin.schneider.de at gmail.com>
 *  @filesource
 */
namespace Lousson\Message\AMQP\Intern;

/** Interfaces: */
use Lousson\URI\AnyURIFactory;
use Lousson\URI\AnyURI;

/** Dependencies: */
use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Lousson\URI\Builtin\BuiltinURIFactory;

/** Exceptions: */
use Lousson\Message\AMQP\Intern\AMQPRuntimeError;
use Lousson\Message\Error\InvalidMessageerror;

/**
 *  A factory creating various PECL/AMQP class instances
 *
 *  The Lousson\Message\AMQP\Intern\AMQPFactory is used internally to
 *  create various PECL/AMQP class instances by aggregating the parameters
 *  from the "amqp://" URL format:
 *
 *- amqp://$user:$password@$hostname:$port/$vhost
 *
 *  In order to create AMQPExchange or AMQPQueue instances, the neccessary
 *  parameters can be provided in the query-part of the URL, e.g.:
 *
 *- amqp://localhost:5672/?exchange-name=my-exchange
 *- amqp://127.0.0.1/?queue-name=my-queue
 *
 *  Note that the AMQPFactory implementation does NOT provide defaults for
 *  any absent or invalid values!
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPFactory
{
    /**
     *  The URL query key that identifies exchange names
     *
     *  @var string
     */
    const KEY_EXCHANGE_NAME = "exchange-name";

    /**
     *  The URL query key that identifies queue names
     *
     *  @var string
     */
    const KEY_QUEUE_NAME = "queue-name";

    /**
     *  Create a factory instance
     *
     *  The constructor allows the caller to provide an URI factory for the
     *  AMQP factory to operate with - instead of the builtin default.
     *
     *  @param  AnyURIFactory       $factory        The URI factory, if any
     */
    public function __construct(AnyURIFactory $factory = null)
    {
        if (null === $factory) {
            $factory = new BuiltinURIFactory();
        }

        $this->factory = $factory;
    }

    /**
     *  Create a exchange instance
     *
     *  The createExchange() method creates a new AMQPExchange object and uses
     *  the given $uri to aggregate the connection details and the exchange's
     *  name (?exchange-name=..). The optional $channel parameter can get used
     *  to provide an existing AMQPChannel instance to rely on.
     *
     *  @param  string              $uri        The exchange URI
     *  @param  AMQPChannel         $channel    The AMQP channel, if any
     *
     *  @return \AMQPExchange
     *          An AMQP exchange instance is returned on success
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the connection could not get established
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the $uri is malformed or invalid in general
     */
    public function createExchange($uri, AMQPChannel $channel = null)
    {
        $uri = $this->fetchURI($uri);

        if (null === $channel) {
            $channel = $this->createChannel($uri);
        }
        else if (isset($channel->connection) &&
                $channel->connection instanceof AMQPConnection) {
            $this->connect($channel->connection);
        }

        try {
            $exchange = new AMQPExchange($channel);
            $exchange->channel = $channel;
            $query = (string) $uri->getPart(AnyURI::PART_QUERY);
            parse_str($query, $parts);
            $exchange->setName(@$parts["exchange-name"]);
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $notice = "Could not create AMQP exchange: Caught $class";
            $code = AMQPRuntimeError::E_UNKNOWN;
            throw new AMQPRuntimeError($notice, $code, $error);
        }

        return $exchange;
    }

    /**
     *  Create a queue instance
     *
     *  The createQueue() method creates a new AMQPQueue object and uses
     *  the given $uri to aggregate the connection details and the queue's
     *  name (?queue-name=..). The optional $channel parameter can get used
     *  to provide an existing AMQPChannel instance to rely on.
     *
     *  @param  string              $uri        The queue URI
     *  @param  AMQPChannel         $channel    The AMQP channel, if any
     *
     *  @return \AMQPQueue
     *          An AMQP queue instance is returned on success
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the connection could not get established
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the $uri is malformed or invalid in general
     */
    public function createQueue($uri, AMQPChannel $channel = null)
    {
        $uri = $this->fetchURI($uri);

        if (null === $channel) {
            $channel = $this->createChannel($uri);
        }
        else if (isset($channel->connection) &&
                $channel->connection instanceof AMQPConnection) {
            $this->connect($channel->connection);
        }

        try {
            $queue = new AMQPQueue($channel);
            $queue->channel = $channel;
            $query = (string) $uri->getPart(AnyURI::PART_QUERY);
            parse_str($query, $parts);
            $queue->setName(@$parts["queue-name"]);
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $notice = "Could not create AMQP queue: Caught $class";
            $code = AMQPRuntimeError::E_UNKNOWN;
            throw new AMQPRuntimeError($notice, $code, $error);
        }

        return $queue;
    }

    /**
     *  Create a channel instance
     *
     *  The createChannel() method creates a new AMQPChannel object and
     *  uses the given $uri to aggregate the connection details and/or the
     *  relies on the $connection instance provided.
     *
     *  @param  string              $uri        The connection URI
     *  @param  AMQPConnection      $connection The connection, if any
     *
     *  @return \AMQPConnection
     *          An AMQP connection instance is returned on success
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the connection could not get established
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the $uri is malformed or invalid in general
     */
    public function createChannel($uri, AMQPConnection $connection = null)
    {
        $uri = $this->fetchURI($uri);

        if (null === $connection) {
            $connection = $this->createConnection($uri);
        }

        $this->connect($connection);

        $channel = new AMQPChannel($connection);
        $channel->uri = $uri;
        $channel->connection = $connection;

        return $channel;
    }

    /**
     *  Create a connection instance
     *
     *  The createConnection() method creates a new AMQPConnection object
     *  and uses the given $uri to aggregrate the hostname, port, username,
     *  password and vhost (the /path) for configuring the instance.
     *
     *  @param  string              $uri        The connection URI
     *
     *  @return \AMQPConnection
     *          An AMQP connection instance is returned on success
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the $uri is malformed or invalid in general
     */
    public function createConnection($uri)
    {
        static $map = array(
            AnyURI::PART_HOST => "setHost",
            AnyURI::PART_PORT => "setPort",
            AnyURI::PART_USERNAME => "setLogin",
            AnyURI::PART_USERAUTH => "setPassword",
            AnyURI::PART_PATH => "setVhost",
        );

        $uri = $this->fetchURI($uri);
        $connection = new AMQPConnection();

        foreach ($map as $part => $method) {
            $value = $uri->getPart($part);
            isset($value) && $connection->$method($value);
            $connection->$part = $value;
        }

        return $connection;
    }

    /**
     *  Ensure a connection being established
     *
     *  The connect() method is used internally to ensure the $connection
     *  instance actually represents an established connection. If not, a
     *  (re-) connection attempt is made.
     *
     *  @param  AMQPConnection      $connection     The connection to check
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPRuntimeError
     *          Raised in case the connection could not get established
     */
    private function connect(AMQPConnection $connection)
    {
        $setup = ini_set("track_errors", true);
        $php_errormsg = "UNKNOWN ERROR";
        $error = null;
        $status = true;

        if(!$connection->isConnected()) try {
            $status = $connection->connect();
            $errormsg = $php_errormsg;
        }
        catch (\AMQPException $error) {
            $class = get_class($error);
            $errormsg = "Caught $class";
        }

        ini_set("track_errors", $setup);

        if (false === $status) {
            $notice = "Could not establish AMQP connection: $errormsg";
            $code = AMQPRuntimeError::E_UNKNOWN;
            throw new AMQPRuntimeError($errormsg, $code, $error);
        }
    }

    /**
     *  Fetch an URI
     *
     *  The fetchURI() method is used internally to validate and convert
     *  the given $uri into an instance of the AnyURI interface - unless
     *  the provided value is an URI object already.
     *
     *  @param  string              $uri        The URI to operate on
     *
     *  @return \Lousson\URI\AnyURI
     *          An URI object is returned on success
     *
     *  @throws \Lousson\Message\AMQP\Intern\AMQPArgumentError
     *          Raised in case the $uri is malformed or invalid in general
     */
    private function fetchURI($uri)
    {
        if (!$uri instanceof AnyURI) try {
            $uri = $this->factory->getURI($uri);
        }
        catch (\Lousson\URI\AnyURIException $error) {
            $message = $error->getMessage();
            $code = $error->getCode();
            throw new AMQPArgumentError($message, $code, $error);
        }

        return $uri;
    }

    /**
     *  An URI factory instance
     *
     *  @var \Lousson\URI\AnyURIFactory
     */
    private $factory;
}

