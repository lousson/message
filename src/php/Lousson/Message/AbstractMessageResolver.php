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
 *  Lousson\Message\AbstractMessageResolver class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Interfaces: */
use Lousson\Message\AnyMessageResolver;
use Lousson\URI\AnyURIFactory;
use Lousson\URI\AnyURIResolver;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\URI\Builtin\BuiltinURIFactory;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;

/**
 *  An abstract message resolver implementation
 *
 *  The Lousson\Message\AbstractMessageResolver class is an abstract
 *  implementation of the AnyMessageResolver interface that operates on
 *  top of an URI resolver, in order to find various aliases for the URIs
 *  to resolve into message provider and handler instances.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class AbstractMessageResolver implements AnyMessageResolver
{
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
        AnyURIResolver $resolver = null,
        AnyURIFactory $factory = null
    ) {
        if (null === $factory) {
            $factory = new BuiltinURIFactory();
        }

        $this->resolver = $resolver;
        $this->factory = $factory;
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
    abstract public function lookupHandler(AnyURI $uri);

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
    abstract public function lookupProvider(AnyURI $uri);

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
    public function resolveHandler(&$uri)
    {
        $handler = $this->resolveEntity($uri, "lookupHandler");
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
    public function resolveProvider(&$uri)
    {
        $provider = $this->resolveEntity($uri, "lookupProvider");
        return $provider;
    }

    /**
     *  Resolve message entities
     *
     *  The resolveEntity() method is used internally to implement both,
     *  the resolveHandler() and resolveProvider() methods.
     *
     *  @param  string              $uri        The URI to resolve
     *  @param  string              $method     The lookup method to use
     *
     *  @return object
     *          A handler or provider instance is returned on success,
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
    private function resolveEntity(&$uri, $method)
    {
        $resolvedURIs = $this->resolveURI($uri);
        $entity = null;

        foreach ($resolvedURIs as $resolved) {
            if ($entity = $this->$method($resolved)) {
                $uri = $resolved;
                break;
            }
        }

        return $entity;
    }

    /**
     *  Resolve URIs
     *
     *  The resolveURI() method is used internally to access and invoke
     *  the URI resolver given to the constructor, if any, and convert the
     *  given $uri to a list of one or more URIs to look up handlers and
     *  providers for.
     *
     *  @param  string              $uri        The URI to resolve
     *
     *  @return array
     *          A list of URI instances is returned on success
     *
     *  @throws \Lousson\Message\Error\InvalidMessageError
     *          Raised in case the given $uri is malformed
     */
    private function resolveURI($uri)
    {
        try {

            if (!$uri instanceof AnyURI) {
                $uri = $this->factory->getURI($uri);
            }

            if (null !== $this->resolver) {
                $resolved = $this->resolver->resolve($uri);
            }

            if (empty($resolved)) {
                $resolved = array($uri);
            }

            return $resolved;
        }
        catch (\Lousson\URI\AnyURIException $error) {
            $message = $error->getMessage();
            $message = "Could not resolve entity URI; $message";
            $code = $error->getCode();
            throw new InvalidMessageError($message, $code, $error);
        }
    }

    /**
     *  The URI resolver associated, if any
     *
     *  @var \Lousson\URI\AnyURIResolver
     */
    private $resolver;
}

