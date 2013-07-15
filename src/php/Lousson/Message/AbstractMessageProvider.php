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
 *  Lousson\Message\AbstractMessageProvider class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Interfaces: */
use Lousson\Message\AnyMessageProvider;
use Lousson\URI\AnyURIFactory;

/** Dependencies: */
use Lousson\URI\Builtin\BuiltinURIFactory;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;

/**
 *  An abstract message provider implementation
 *
 *  The Lousson\Message\AbstractMessageProvider class implements the API
 *  specified by the AnyMessageProvider interface as far as possible -
 *  without assuming too many implementation details. This might ease the
 *  creation of new message providers.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class AbstractMessageProvider implements AnyMessageProvider
{
    /**
     *  Create a provider instance
     *
     *  The constructor allows the provisioning of a custom URI factory
     *  instance for the provider to operate on - instread of the builtin
     *  default.
     *
     *  @param  AnyURIFactory       $uriFactory     The URI factory
     */
    public function __construct(AnyURIFactory $uriFactory = null)
    {
        if (null === $uriFactory) {
            $uriFactory = new BuiltinURIFactory();
        }

        $this->uriFactory = $uriFactory;
    }

    /**
     *  Convert an URI into an URI object
     *
     *  The fetchURI() method is used internally to validate the $uri
     *  provided and to parse it into an instance of the AnyURI interface.
     *
     *  @param  string              $uri        The URI to parse
     *
     *  @return \Lousson\URI\AnyURI
     *          An URI instance is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          All exceptions raised implement this interface
     *
     *  @throws \InvalidArgumentException
     *          Raised in case the URI is considered invalid
     */
    protected function fetchURI($uri)
    {
        if (!$uri instanceof \Lousson\URI\AnyURI) try {
            $uri = $this->uriFactory->getURI($uri);
        }
        catch (\Lousson\URI\AnyURIException $error) {
            $message = $error->getMessage();
            $code = $error->getCode();
            throw new MessageArgumentError($message, $code, $error);
        }

        return $uri;
    }

    /**
     *  The provider's URI factory instance
     *
     *  @var \Lousson\URI\AnyURIFactory
     */
    private $uriFactory;
}

