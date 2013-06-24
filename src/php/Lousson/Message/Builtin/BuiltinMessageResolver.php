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
use Lousson\URI\AnyURI;

/** Dependencies: */
use Lousson\Message\AbstractMessageResolver;

/**
 *  The default message resolver implementation
 *
 *  The BuiltinMessageResolver class is the default implementation of the
 *  AnyMessageResolver interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class BuiltinMessageResolver extends AbstractMessageResolver
{
    /**
     *  A hook for resolving handlers
     *
     *  The lookupHandler() method is used internally to resolve the
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
        return null;
    }

    /**
     *  A hook for resolving providers
     *
     *  The lookupHandler() method is used internally to resolve the
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
        return null;
    }
}

