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
 *  Lousson\Message\Record\RecordMessageProducer class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message\Record;

/** Interfaces: */
use Lousson\Record\AnyRecordException;
use Lousson\Message\Record\RecordMessageHandler;

/** Dependencies: */
use Lousson\Record\Builtin\BuiltinRecordFactory;
use Lousson\Record\Builtin\BuiltinRecordUtil;
use Lousson\Message\Generic\GenericMessageHandler;

/** Exceptions: */
use Lousson\Message\Error\InvalidMessageError;
use Lousson\Message\Error\RuntimeMessageError;
use Exception;

/**
 *  An abstract record message producer
 *
 *  The RecordMessageProducer class is an abstract implementation of the
 *  AnyMessageHandler and RecordMessageHandler interfaces. It allows the
 *  authors of derived classes to create record-publishing producers by
 *  implementing just one method: processMessage(), as declared in the
 *  AnyMessageHandler interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class RecordMessageProducer
    extends GenericMessageHandler
    implements RecordMessageHandler
{
    /**
     *  The default record media type
     *
     *  @var string
     */
    const DEFAULT_RECORD_TYPE = "application/json";

    /**
     *  Process message records
     *
     *  The processRecord() method is used to invoke the logic to process
     *  the data $record provided, according to the given event $uri.
     *
     *  Note that the default implementation in the RecordMessageProducer
     *  class does not implement any processing logic beside some basic
     *  validation of the parameters provided. Thus; authors of derived
     *  classes may override this method without an actual invocation of
     *  parent::processRecord().
     *
     *  @param  string              $uri        The message URI
     *  @param  array               $record     The message record
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case processing the message has failed
     */
    public function processRecord($uri, array $record)
    {
        $uri = $this->fetchURI($uri);
        $record = $this->fetchRecord($record);

        try {
            $factory = $this->getRecordFactory($uri);
            $type = $this->getRecordType($uri);
            $builder = $factory->getRecordBuilder($type);
            $data = $builder->buildRecord($record, $type);
        }
        catch (Exception $error) {
            $class = get_class($error);
            $notice = "Failed to process record: Caught $class";
            $code = RuntimeMessageError::E_INTERNAL_ERROR;
            throw new RuntimeMessageError($notice, $code, $error);
        }

        $this->process($uri, $data, $type);
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
     *  Obtain a record media type
     *
     *  The getRecordType() method returns the internet media type that
     *  is associated with messages for the given $uri. By default, this
     *  is "application/json". Authors of derived classes, however, may
     *  choose to reimplement this method - in order to apply some custom
     *  logic.
     *
     *  @param  string              $uri        The message URI
     *
     *  @return string
     *          An internet media type is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the given $uri is malformed
     */
    protected function getRecordType($uri)
    {
        $uri = $this->fetchURI($uri);
        return self::DEFAULT_RECORD_TYPE;
    }

    /**
     *  Validate and normalize a data record
     *
     *  @param  array               $data       The message data record
     *
     *  @return array
     *          A data record array is returned on success
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the data record is malformed
     */
    final protected function fetchRecord(array $data)
    {
        try {
            $record = BuiltinRecordUtil::normalizeData($data);
        }
        catch (AnyRecordException $error) {
            $notice = "Failed to process record: ". $error->getMessage();
            $code = $error->getCode();
            throw new InvalidMessageError($notice, $code, $error);
        }

        return $record;
    }
}

