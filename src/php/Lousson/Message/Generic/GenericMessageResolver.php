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
 *  Lousson\Message\Generic\GenericMessageResolver class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Interfaces: */
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessageProvider;
use Lousson\Message\AnyMessageResolver;
use Lousson\URI\AnyURIFactory;
use Lousson\URI\AnyURIResolver;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolver;
use Lousson\URI\Builtin\BuiltinURIUtil;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;

/**
 *  A generic message resolver implementation
 *
 *  The Lousson\Message\Generic\GenericMessageResolver class is a generic
 *  implementation of the AnyMessageResolver interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class GenericMessageResolver extends AbstractMessageResolver
{
    /**
     *  Create a resolver instance
     *
     *  The constructor allows the caller to provide an URI resolver for
     *  the new instance. Note that this parameter is optional: If it is
     *  absent, each URI will resolve to itself.
     *  The also optional $fallback parameter can be used to provide a
     *  fallback resolver invoked when no entity is found, whilst the URI
     *  $factory parameter is used to force the use of an alternative URI
     *  factory.
     *
     *  @param  AnyURIResolver      $resolver       The URI resolver
     *  @param  AnyURIFactory       $factory        The URI factory
     *  @param  AnyMessageResolver  $fallback       The fallback resolver
     */
    public function __construct(
        AnyURIResolver $resolver = null,
        AnyURIFactory $factory = null,
        AnyMessageResolver $fallback = null
    ) {
        parent::__construct($resolver, $factory);

        $this->fallback = $fallback;
        assert($fallback !== $this);
    }

    /**
     *  Assign a handler instance
     *
     *  The setHandler() method is used to associate the message $handler
     *  with the given URI $scheme.
     *
     *  @param  string              $scheme     The URI scheme to resolve
     *  @param  AnyMessageHandler   $handler    The handler to assign
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI scheme is considered invalid
     */
    public function setHandler($scheme, AnyMessageHandler $handler)
    {
        $scheme = $this->fetchScheme($scheme);
        $this->handlers[$scheme] = $handler;
    }

    /**
     *  Retrieve a handler instance
     *
     *  The getHandler() method is used to retrieve the message handler
     *  associated with the given URI $scheme, if any.
     *
     *  @param  string              $scheme     The URI scheme to resolve
     *
     *  @return \Lousson\Message\AnyMessageHandler
     *          A message handler instance is returned on success,
     *          NULL otherwise
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is scheme considered invalid
     */
    public function getHandler($scheme)
    {
        $scheme = $this->fetchScheme($scheme);
        $handler = @$this->handlers[$scheme];
        return $handler;
    }

    /**
     *  Assign a provider instance
     *
     *  The setProvider() method is used to associate the message $provider
     *  with the given URI $scheme.
     *
     *  @param  string              $scheme     The URI scheme to resolve
     *  @param  AnyMessageProvider  $provider   The provider to assign
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is scheme considered invalid
     */
    public function setProvider($scheme, AnyMessageProvider $provider)
    {
        $scheme = $this->fetchScheme($scheme);
        $this->providers[$scheme] = $provider;
    }

    /**
     *  Retrieve a provider instance
     *
     *  The setProvider() method is used to retrieve the message provider
     *  associated with the given URI $scheme, if any.
     *
     *  @param  string              $scheme     The URI scheme to resolve
     *
     *  @return \Lousson\Message\AnyMessageProvider
     *          A message provider instance is returned on success,
     *          NULL otherwise
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is scheme considered invalid
     */
    public function getProvider($scheme)
    {
        $scheme = $this->fetchScheme($scheme);
        $provider = @$this->providers[$scheme];
        return $provider;
    }

    /**
     *  A hook for resolving handlers
     *
     *  The lookupHandler() method is used internally to resolve the
     *  handler associated with the given $uri's prefix.
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
    final public function lookupHandler(AnyURI $uri)
    {
        $scheme = (string) $uri->getPart(AnyURI::PART_SCHEME);
        $handler = $this->getHandler($scheme);
        return $handler;
    }

    /**
     *  A hook for resolving providers
     *
     *  The lookupHandler() method is used internally to resolve the
     *  provider associated with the given $uri's prefix.
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
    final public function lookupProvider(AnyURI $uri)
    {
        $scheme = (string) $uri->getPart(AnyURI::PART_SCHEME);
        $provider = $this->getProvider($scheme);
        return $provider;
    }

    /**
     *  Resolve message handlers
     *
     *  The resolveHandler() method is used to resolve the given $uri and
     *  obtain a message handler instance to process messages associated.
     *  Note that the $uri parameter is passed by-reference: The resolver
     *  may re-write its value. The subsequent invocation of the handler's
     *  process() method, if any, should be made with the re-written URI.
     *
     *  @param  string              $uri        The URI to resolve
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
    final public function resolveHandler(&$uri)
    {
        $handler = parent::resolveHandler($uri);

        if (!isset($handler) && isset($this->fallback)) {
            $handler = $this->fallback->resolveHandler($uri);
        }

        return $handler;
    }

    /**
     *  Resolve message providers
     *
     *  The resolveProvider() method is used to resolve the given $uri and
     *  obtain a message provider instance to process messages associated.
     *  Note that the $uri parameter is passed by-reference: The resolver
     *  may re-write its value. The subsequent invocation of the provider's
     *  fetch() method, if any, should be made with the re-written URI.
     *
     *  @param  string              $uri        The URI to resolve
     *
     *  @return \Lousson\Message\AnyMessageProvider
     *          A message provider instance is returned on success,
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
    final public function resolveProvider(&$uri)
    {
        $provider = parent::resolveProvider($uri);

        if (!isset($provider) && isset($this->fallback)) {
            $provider = $this->fallback->resolveProvider($uri);
        }

        return $provider;
    }

    /**
     *  Fetch URI scheme names
     *
     *  The fetchScheme() method is used internally to parse, validate and
     *  normalize the name of the URI $scheme provided.
     *
     *  @param  string              $scheme     The URI scheme to parse
     *
     *  @return string
     *          The normalized URI scheme name is returned on success
     *
     *  @throws \Lousson\Message\Error\MessageArgumentError
     *          Raised in case the URI scheme is considered invalid
     */
    private function fetchScheme($scheme)
    {
        try {
            $scheme = BuiltinURIUtil::parseURIScheme($scheme);
            return $scheme;
        }
        catch (\Lousson\URI\AnyURIException $error) {
            $message = $error->getMessage();
            $code = $error->getCode();
            throw new MessageArgumentError($message, $code, $error);
        }
    }

    /**
     *  A mapping of URI schemes to handler instances
     *
     *  @var array
     */
    private $handlers = array();

    /**
     *  A mapping of URI schemes to provider instances
     *
     *  @var array
     */
    private $providers = array();

    /**
     *  The fallback message resolver, if any
     *
     *  @var \Lousson\Message\AnyMessageResolver
     */
    private $fallback;
}

