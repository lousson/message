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
 *  Lousson\Message\AnyMessageResolver interface definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Dependencies: */
use Lousson\Message\AnyMessageException;
use Lousson\Message\AnyMessageHandler;
use Lousson\Message\AnyMessageProvider;

/**
 *  An interface for message resolvers
 *
 *  The Lousson\Message\AnyMessageResolver interface declares the API any
 *  message resolver implementation must provide.
 *
 *  Message resolvers are used within dynamic message routes, in order to
 *  determine which handler/provider is addressed in the next hop. They're
 *  also capable of re-writing the routing URIs and thus loosen coupling
 *  between modules.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
interface AnyMessageResolver
{
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
    public function resolveHandler(&$uri);

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
    public function resolveProvider(&$uri);
}

