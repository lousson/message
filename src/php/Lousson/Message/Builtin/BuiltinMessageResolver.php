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
 *  Lousson\Message\Builtin\BuiltinMessageResolver class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Builtin;

/** Interfaces: */
use Lousson\Message\AnyMessageResolver;
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolver;
use Lousson\Message\Builtin\BuiltinMessageHandler;
use Lousson\Message\Builtin\BuiltinMessageProvider;
use Lousson\URI\Builtin\BuiltinURIUtil;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;

/**
 *  A proxy message resolver implementation
 *
 *  The Lousson\Message\Builtin\BuiltinMessageResolver class is a proxy
 *  implementation of the AnyMessageResolver interface, determining the
 *  actual resolver at runtime.
 *
 *  @since      lousson/Lousson_Message-1.1.0
 *  @package    org.lousson.message
 */
class BuiltinMessageResolver extends AbstractMessageResolver
{
    /**
     *  Assign a resolver instance
     *
     *  The setResolver() method is used to associate the message $resolver
     *  with the given URI $scheme.
     *
     *  @param  string              $scheme     The URI scheme to resolve
     *  @param  AnyMessageHandler   $resolver    The resolver to assign
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI scheme is considered invalid
     */
    public function setResolver($scheme, AnyMessageResolver $resolver)
    {
        $scheme = $this->fetchScheme($scheme);
        $this->resolvers[$scheme] = $resolver;
    }

    /**
     *  Retrieve a resolver instance
     *
     *  The getResolver() method is used to retrieve the message resolver
     *  associated with the given URI $scheme, if any.
     *
     *  @param  string              $scheme     The URI scheme to resolve
     *
     *  @return \Lousson\Message\AnyMessageResolver
     *          A message resolver instance is returned on success,
     *          NULL otherwise
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is scheme considered invalid
     */
    public function getResolver($scheme)
    {
        $scheme = $this->fetchScheme($scheme);
        $resolver = @$this->resolvers[$scheme];
        return $resolver;
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
        $resolver = $this->getResolver($scheme);
        $handler = null;

        if (isset($resolver)) {
            $handler = new BuiltinMessageHandler($resolver);
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
        $scheme = (string) $uri->getPart(AnyURI::PART_SCHEME);
        $resolver = $this->getResolver($scheme);
        $provider = null;

        if (isset($resolver)) {
            $provider = new BuiltinMessageProvider($resolver);
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
     *  A mapping of URI schemes to resolver instances
     *
     *  @var array
     */
    private $resolvers = array();
}

