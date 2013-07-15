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
 *  Lousson\Message\Builtin\BuiltinMessageStash class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Builtin;

/** Dependencies: */
use Closure;

/** Exceptions: */
use Lousson\Message\Error\MessageArgumentError;

/**
 *  A stash for message data
 *
 *  The Lousson\Message\Builtin\BuiltinMessageStash class is a utility for
 *  anonymous storage of message data. Its primary purpose is to provide an
 *  easy mechanism for the the implementation of the fetch(), acknowledge()
 *  and discard() logic in AnyMessageProvider implementations.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
final class BuiltinMessageStash
{
    /**
     *  Create a stash instance
     *
     *  The constructor allows the caller to provide a $callback to be
     *  invoked by the destructor in case there are messages left in the
     *  stash. It must acceppt a single parameter, an array where the
     *  keys are tokens created by the stash and the items are formerly
     *  stored using the store() method.
     *
     *  @param  Closure             $callback   The destructor callback
     */
    public function __construct(Closure $callback = null)
    {
        $this->callback = $callback;
    }

    /**
     *  Store data in the stash
     *
     *  The store() method swaps the given $data and returns a stash token
     *  for later retrieval using restore().
     *
     *  @param  mixed               $data       The data to store
     *
     *  @return string
     *          A stash token is returned on success
     */
    public function store($data)
    {
        $key = uniqid();
        $this->stash[$key] = $data;
        return $key;
    }

    /**
     *  Restore data from the stash
     *
     *  The restore() method returns the data associated with the given
     *  $token. In case the token is invalid, it will raise an exception
     *  to signal that the $use of an invalid token is impossible.
     *
     *  @param  string              $token      The stash token
     *  @param  string              $use        An english verb
     *
     *  @return mixed
     *          The data associated with the token is returned on success
     *
     *  @throws \Lousson\Message\Error\MessageArgumentError
     *          Raised in case the token is not associated with any data
     */
    public function restore($token, $use = "use")
    {
        if (isset($this->stash[$token]) ||
                array_key_exists($token, $this->stash)) {
            $data = $this->stash[$token];
            unset($this->stash[$token]);
            return $data;
        }

        $notice = "Could not $use invalid token: $token";
        throw new MessageArgumentError($notice);
    }

    /**
     *  Cleanup
     *
     *  The destructor has been implemented explicitely in order to invoke
     *  the cleanup callback provided at construction time, if any.
     */
    public function __destruct()
    {
        if (isset($this->callback) && count($this->stash)) try {
            $callback = $this->callback;
            $callback($this->stash);
        }
        catch (\Exception $error) {
            $warning = "While cleaning up message stash: Caught $error";
            trigger_error($warning, E_USER_WARNING);
        }
    }

    /**
     *  The stash array itself
     *
     *  @var array
     */
    private $stash = array();
}

