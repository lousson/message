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
 *  Lousson\Message\AMQP\URI\AMQPURI class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Benjamin Schneider <benjamin.schneider.de at gmail.com>
 *  @filesource
 */
namespace Lousson\Message\AMQP\URI;

/**
 *  Represents an AMQP URI and all it's contents.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPURI
{
    /**
     *  Compare this URI's connection part to the one of another URI.
     *
     *  Check whether username, password, host, port and vhost of this and
     *  the given URI are identical and returns true if so, or false otherwise.
     *
     *  @param  AMQPURI  $other
     *
     *  @return bool
     */
    public function equalsConnection(AMQPURI $other)
    {
        $attributes = array('host', 'login', 'password', 'port', 'vhost');

        foreach ($attributes as $attribute) {
            if ($this->{$attribute} !== $other->{$attribute}) {
                return false;
            }
        }

        return true;
    }

    /**
     *  Set the exchange's name.
     *  @param string $exchange
     */
    public function setExchange($exchange)
    {
        $this->exchange = strval($exchange);
    }

    /**
     *  Get the exchange's name.
     *  @return null|string
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     *  Set the host name.
     *  @param string $host
     */
    public function setHost($host)
    {
        $this->host = strval($host);
    }

    /**
     *  Get the host name.
     *  @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     *  Set the login name.
     *  @param string $login
     */
    public function setLogin($login)
    {
        $this->login = strval($login);
    }

    /**
     *  Get the login name.
     *  @return null|string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     *  Set the password.
     *  @param string $password
     */
    public function setPassword($password)
    {
        $this->password = strval($password);
    }

    /**
     *  Get the password.
     *  @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *  Set the port number.
     *  @param int $port
     */
    public function setPort($port)
    {
        $this->port = intval($port);
    }

    /**
     *  Get the port number.
     *  @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     *  Set the queue name.
     *  @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = strval($queue);
    }

    /**
     *  Get the queue name.
     *  @return null|string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     *  Set the routing key.
     *  @param string $routingKey
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = strval($routingKey);
    }

    /**
     *  Get the routing key.
     *  @return null|string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     *  Set the vhost name.
     *  @param string $vhost
     */
    public function setVhost($vhost)
    {
        $this->vhost = strval($vhost);
    }

    /**
     *  Get the vhost name.
     *  @return null|string
     */
    public function getVhost()
    {
        return $this->vhost;
    }

    /**
     *  The exchange's name
     *  @var string|null
     */
    private $exchange;

    /**
     *  The host name
     *  @var string
     */
    private $host;

    /**
     *  The login name
     *  @var string|null
     */
    private $login;

    /**
     *  The password
     *  @var string|null
     */
    private $password;

    /**
     *  The port number
     *  @var int|null
     */
    private $port;

    /**
     *  The queue name
     *  @var string|null
     */
    private $queue;

    /**
     *  The routing key
     *  @var string|null
     */
    private $routingKey;

    /**
     *  The vhost name
     *  @var string|null
     */
    private $vhost;
}