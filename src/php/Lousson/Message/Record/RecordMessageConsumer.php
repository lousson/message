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
 *  Lousson\Message\Record\RecordMessageConsumer class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Record;

/** Interfaces: */
use Lousson\Message\AnyMessageException;
use Lousson\Message\AnyMessage;
use Lousson\Message\Record\RecordMessageHandler;
use Lousson\Record\AnyRecordException;

/** Dependencies: */
use Lousson\Message\Generic\GenericMessageHandler;
use Lousson\Record\Builtin\BuiltinRecordFactory;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;
use Exception;

/**
 *  An abstract record message consumer
 *
 *  The RecordMessageConsumer class is an abstract implementation of the
 *  AnyMessageHandler and RecordMessageHandler interfaces. It allows the
 *  authors of derived classes to create record-processing consumers by
 *  by implementing just one method: processRecord(), as declared in the
 *  RecordMessageHandler interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class RecordMessageConsumer
    extends GenericMessageHandler
    implements RecordMessageHandler
{
    /**
     *  Process message instances
     *
     *  The processMessage() method is used to invoke the logic that
     *  processes the given $message according to the event $uri.
     *
     *  Note that the default implementation in the RecordMessageConsumer
     *  class will forward the call to the processRecord() method - after
     *  parsing the record from the message's content, according to it's
     *  media type.
     *
     *  @param  string              $uri        The event URI
     *  @param  AnyMessage          $message    The message instance
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case processing the message has failed
     */
    final public function processMessage($uri, AnyMessage $message)
    {
        $uri = $this->fetchURI($uri);
        $record = $this->fetchRecord($message);

        try {
            $this->processRecord($uri, $record);
        }
        catch (AnyMessageException $error) {
            /** Allowed by the AnyMessageHandler interface */
            throw $error;
        }
        catch (Exception $error) {
            $class = get_class($error);
            $notice = "Failed to process record: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($notice, $code, $error);
        }
    }

    /**
     *  Obtain a record factory instance
     *
     *  The getRecordFactory() method returns a record factory object. It
     *  is used within e.g. fetchRecord() to parse message content into a
     *  data record array.
     *
     *  @return \Lousson\Record\AnyRecordFactory
     *          A record factory instance is returned on success
     */
    protected function getRecordFactory()
    {
        $factory = new BuiltinRecordFactory();
        return $factory;
    }

    /**
     *  Extract a data record from a message
     *
     *  The fetchRecord() method is used internally to parse the content
     *  of the given $message into a record data array, provided that the
     *  factory returned by getRecordFactory() can provide a parser for
     *  the message's type.
     *
     *  @param  AnyMessage          $message    The message instance
     *
     *  @return array
     *          A data record array is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the message content cannot get parsed
     */
    final protected function fetchRecord(AnyMessage $message)
    {
        try {
            $factory = $this->getRecordFactory();
            $type = $message->getType();
            $parser = $factory->getRecordParser($type);
            $content = $message->getContent();
            $record = $parser->parseRecord($content);
        }
        catch (AnyRecordException $error) {
            $notice = "Failed to parse message: ". $error->getMessage();
            $code = $error->getCode();
            throw new InvalidMessageError($notice, $code, $error);
        }
        catch (Exception $error) {
            $class = get_class($error);
            $notice = "Failed to parse message: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($notice, $code, $error);
        }

        return $record;
    }
}

