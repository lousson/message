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
 *  Lousson\Message\Callback\CallbackMessageResolver class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Callback;

/** Interfaces: */
use Lousson\URI\AnyURIFactory;
use Lousson\URI\AnyURIResolver;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolver;
use Closure;

/** Exceptions: */
use Lousson\Message\Error\RuntimeMessageError;

/**
 *  A callback message resolver implementation
 *
 *  The Lousson\Message\Callback\CallbackMessageResolver class is a proxy
 *  implementation of the AnyMessageResolver interface, using a callback to
 *  determine the actual resolver instance to query for message handlers
 *  and resolvers.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class CallbackMessageResolver extends AbstractMessageResolver
{
    /**
     *  Create a resolver instance
     *
     *  The constructor requires the caller to provide a Closure $callback,
     *  used to determine the actual resolver instance to query for message
     *  handlers and providers.
     *  The constructor also allows the provisioning of an URI resolver for
     *  the new instance. Note that this parameter is optional: If it is
     *  absent, each URI will resolve to itself. Furthermore, one can pass
     *  a custom URI $factory instance to be used instead of the builtin
     *  one.
     *
     *  @param  Closure             $callback       The resolver callback
     *  @param  AnyURIResolver      $resolver       The URI resolver
     *  @param  AnyURIFactory       $factory        The URI factory
     */
    public function __construct(
        Closure $callback,
        AnyURIResolver $resolver = null,
        AnyURIFactory $factory = null
    ) {
        parent::__construct($resolver, $factory);
        $this->callback = $callback;
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
        $resolver = $this->lookupResolver($uri);
        $handler = null;

        if (null !== $resolver) {
            $handler = $resolver->resolveHandler($uri);
        }

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
        $resolver = $this->lookupResolver($uri);
        $provider = null;

        if (null !== $resolver) {
            $provider = $resolver->resolveProvider($uri);
        }

        return $provider;
    }

    /**
     *  Invoking the resolver callback
     *
     *  The lookupResolver() method is used internally to invoke the
     *  callback provided at construction time, in order to determine
     *  the resolver instance to query for message handler or provider
     *  instances.
     *
     *  @param  AnyURI              $uri        The URI to resolve
     *
     *  @return \Lousson\Message\AnyMessageResolver
     *          A message resolver instance is returned on success,
     *          NULL otherwise.
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the callback throws an exception
     */
    private function lookupResolver(AnyURI $uri)
    {
        $callback = $this->callback;
        $resolver = null;

        try {
            $resolver = $callback($uri);
        }
        catch (\Lousson\Message\AnyMessageException $error) {
            /* Allowed by the AnyMessageResolver interface */
            throw $error;
        }
        catch (\Exception $error) {
            $class = get_class($error);
            $message = "Could not resolve $uri - Caught $class";
            $code = $error->getCode();
            throw new RuntimeMessageError($message, $code, $error);
        }

        if (!$resolver instanceof \Lousson\Message\AnyMessageResolver) {
            $type = @get_class($result)?: gettype($result);
            $message = "Could not resolve $uri - Callback returned $type";
            $code = RuntimeMessageError::E_INVALID;
            throw new RuntimeMessageError($message, $code);
        }

        return $resolver;
    }

    /**
     *  The message resolver callback
     *
     *  @var \Closure
     */
    private $callback;
}

