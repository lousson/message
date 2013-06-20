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
 *  Lousson\Message\AMQP\URI\AMQPURIParser class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Benjamin Schneider <benjamin.schneider.de at gmail.com>
 *  @filesource
 */
namespace Lousson\Message\AMQP\URI;

use Lousson\URI\AnyURI;
use Lousson\URI\Error\InvalidURIError;

/**
 *  Parse AnyURIs into AMQP domain specific URIs.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class AMQPURIParser
{
    /**
     *  Parse the given URI into an AMQP domain specific URI.
     *
     *  @param  AnyURI  $uri
     *
     *  @return AMQPURI
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the URI could not be parsed into an AMQP URI.
     */
    public function parseAnyURI(AnyURI $uri)
    {
        $this->validateScheme($uri);

        $amqpURI = new AMQPURI();

        $this->parseHostAndPort($uri, $amqpURI);
        $this->parseCredentials($uri, $amqpURI);
        $this->parsePath($uri, $amqpURI);
        $this->parseQuery($uri, $amqpURI);

        return $amqpURI;
    }

    /**
     *  Parse the host and port out of the given URI into the AMQP URI.
     *
     *  @param  AnyURI   $uri
     *  @param  AMQPURI  $amqpURI
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the host could not be parsed.
     */
    private function parseHostAndPort(AnyURI $uri, AMQPURI &$amqpURI)
    {
        $host = rtrim($uri->getPart(AnyURI::PART_HOST), '/');
        if (!is_string($host) || !strlen($host)) {
            $message = "The given URI $uri contains no host information.";
            throw new InvalidURIError($message);
        }

        $amqpURI->setHost($host);

        $port = $uri->getPart(AnyURI::PART_PORT);
        if ($port) {
            $amqpURI->setPort($port);
        }
    }

    /**
     *  Parse the login and password out of the given URI into the AMQP URI.
     *
     *  @param  AnyURI   $uri
     *  @param  AMQPURI  $amqpURI
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the credentials could not be parsed.
     */
    private function parseCredentials(AnyURI $uri, AMQPURI &$amqpURI)
    {
        $login = $uri->getPart(AnyURI::PART_USERNAME);
        if ($login) {
            $amqpURI->setLogin($login);
        }

        $password = $uri->getPart(AnyURI::PART_USERAUTH);
        if ($password) {
            $amqpURI->setPassword($password);
        }
    }

    /**
     *  Parse the vhost out of the given URI into the AMQP URI.
     *
     *  @param  AnyURI   $uri
     *  @param  AMQPURI  $amqpURI
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the path could not be parsed.
     */
    private function parsePath(AnyURI $uri, AMQPURI &$amqpURI)
    {
        $path = $uri->getPart(AnyURI::PART_PATH);
        if (is_string($path) && strlen($path)) {
            $amqpURI->setVhost($path);
        }
    }

    /**
     *  Parse the exchange, queue and routing key out of the given URI.
     *
     *  @param  AnyURI   $uri
     *  @param  AMQPURI  $amqpURI
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the query could not be parsed.
     */
    private function parseQuery(AnyURI $uri, AMQPURI &$amqpURI)
    {
        $query = $uri->getPart(AnyURI::PART_QUERY);
        if (!is_string($query) || !strlen($query)) {
            return;
        }

        parse_str($query, $parts);

        if (!is_array($parts)) {
            return;
        }

        if (array_key_exists('exchange', $parts)) {
            $amqpURI->setExchange($parts['exchange']);
        }

        if (array_key_exists('queue', $parts)) {
            $amqpURI->setQueue($parts['queue']);
        }

        if (array_key_exists('routing_key', $parts)) {
            $amqpURI->setRoutingKey($parts['routing_key']);
        }
    }

    /**
     *  Validate that the given scheme equals to 'amqp'
     *
     *  @param  AnyURI  $uri
     *
     *  @throws \Lousson\URI\AnyURIException
     *          Raised in case the scheme is not valid.
     */
    private function validateScheme(AnyURI $uri)
    {
        $scheme = $uri->getPart(AnyURI::PART_SCHEME);
        if ($scheme !== 'amqp') {
            $message = "Cannot handle scheme $scheme";
            throw new InvalidURIError($message);
        }
    }
}