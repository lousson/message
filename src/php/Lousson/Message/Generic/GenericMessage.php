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
 *  Lousson\Message\Generic\GenericMessage class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Generic;

/** Dependencies: */
use Lousson\Message\AnyMessage;

/**
 *  A generic message implementation
 *
 *  The Lousson\Message\GenericMessage class is a generic implementation
 *  of the AnyMessage interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
class GenericMessage implements AnyMessage
{
    /**
     *  Create a message instance
     *
     *  The constructor requires the message content and, optional, the
     *  message's media type to be provided.
     *
     *  @param  mixed               $content    The message data
     *  @param  string              $type       The message media type
     */
    public function __construct($content, $type = self::DEFAULT_TYPE)
    {
        if (isset($content)) {
            $this->content = (string) $content;
        }

        if (isset($type)) {
            $this->type = (string) $type;
        }
        else {
            $this->type = self::DEFAULT_TYPE;
        }
    }

    /**
     *  Obtain the message's content
     *
     *  The getContent() method is used to obtain the encoded form of the
     *  message's content; a sequence of bytes represented by a string. In
     *  case the message does not have any content at all, getContent()
     *  returns NULL.
     *
     *  @return string
     *          The encoded message content is returned on success
     */
    final public function getContent()
    {
        return $this->content;
    }

    /**
     *  Obtain the message's content type
     *
     *  The getRecord() method is used to obtain the mime- or (internet-)
     *  media-type of the message's content.
     *
     *  @return string
     *          The type of the message content is returned on success
     */
    final public function getType()
    {
        return $this->type;
    }

    /**
     *  The message's content, if any
     *
     *  @var string
     */
    private $content;

    /**
     *  The message's media type
     *
     *  @var string
     */
    private $type;
}

