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
 *  Lousson\Message\AbstractMessageProviderTest class definition
 *
 *  @package    org.lousson.message
 *  @copyright  (c) 2013, The Lousson Project
 *  @license    http://opensource.org/licenses/bsd-license.php New BSD License
 *  @author     Mathias J. Hennig <mhennig at quirkies.org>
 *  @filesource
 */
namespace Lousson\Message;

/** Dependencies: */
use PHPUnit_Framework_TestCase;

/**
 *  An abstract test case for message providers
 *
 *  The Lousson\Message\AbstractMessageProviderTest class serves as the
 *  base for testing implementations of the AnyMessageProvider interface.
 *
 *  @since      lousson/Lousson_Message-0.1.0
 *  @package    org.lousson.message
 */
abstract class AbstractMessageProviderTest extends AbstractMessageTest
{
    /**
     *  The default event URI used within the test cases
     *
     *  @var string
     */
    const DEFAULT_MESSAGE_URI = "urn:lousson:test";

    /**
     *  An alias for the AnyMessageProvider::FETCH_DEFAULT bitmask
     *
     *  @var int
     */
    const FETCH_DEFAULT = AnyMessageProvider::FETCH_DEFAULT;

    /**
     *  An alias for the AnyMessageProvider::FETCH_CONFIRM bitmask
     *
     *  @var int
     */
    const FETCH_CONFIRM = AnyMessageProvider::FETCH_CONFIRM;

    /**
     *  An alias for the AnyMessageProvider::ACK_DEFAULT bitmask
     *
     *  @var int
     */
    const ACK_DEFAULT = AnyMessageProvider::ACK_DEFAULT;

    /**
     *  An alias for the AnyMessageProvider::DISC_DEFAULT bitmask
     *
     *  @var int
     */
    const DISC_DEFAULT = AnyMessageProvider::DISC_DEFAULT;

    /**
     *  An alias for the AnyMessageProvider::DISC_REQUEUE bitmask
     *
     *   @var int
     */
    const DISC_REQUEUE = AnyMessageProvider::DISC_REQUEUE;

    /**
     *  Obtain a message provider instance
     *
     *  The getMessageProvider() method returns the message provider
     *  instance the test case shall operate with.
     *
     *  @param  string              $uri        The test message URI
     *  @param  array               $expected   The test messages
     *
     *  @return \Lousson\Message\AnyMessageProvider
     *          A message provider instance is returned on success
     */
    abstract public function getMessageProvider($uri, array $expected);

    /**
     *  Obtain a message URI string
     *
     *  The getMessageURI() method returns a URI string that is used as
     *  the default message or event URI within data providers and tests.
     *
     *  @return string
     *          A message URI is returned on success
     */
    public function getMessageURI()
    {
        $class = get_class($this);
        $constant = "$class::DEFAULT_MESSAGE_URI";
        $uri = constant($constant);
        return $uri;
    }

    /**
     *  Aggregate data/URI tuples
     *
     *  The aggregateDataURITuples() method returns a list of multiple
     *  items, each of whose is an array of two items:
     *
     *- A list of AnyMessage instances
     *- An arbitrary URI string (invalid if $useInvalidURIs is set)
     *
     *  It is used as a data source for data providers within the message
     *  provider test cases and combines the return values of either the
     *  provideValidURIs() or the provideInvalidURIs() method with those of
     *  provideValidMessageInstances().
     *
     *  @param  bool                $useInvalidURIs Use invalid URIs if set
     *
     *  @return array
     *          A list of data/URI tuples is returned on success
     */
    final public function aggregateDataURITuples($useInvalidURIs = false)
    {
        $params = array();
        $dataParams = $this->provideValidMessageInstances();
        $data = array_map("array_shift", $dataParams);

        if ($useInvalidURIs) {
            $uriParams = $this->provideInvalidURIs();
        }
        else {
            $uriParams = $this->provideValidURIs();
        }

        foreach ($uriParams as $uriItem) {
            $params[] = array($data, $uriItem[0]);
        }

        return $params;
    }

    /**
     *  Aggregrate fetch() test parameters
     *
     *  The aggregateFetchParameters() method returns an array of multiple
     *  items, each of whose is an array of either two or three items:
     *
     *- A list of AnyMessage instances
     *- An arbitrary URI string (invalid if $useInvalidURIs is set)
     *- A flags bitmask for the fetch() method (or absent)
     *
     *  It is used as a data source for data providers within the message
     *  provider test cases and combines aggregated data/URI tuples with
     *  all possible flag bitmasks for the fetch() method.
     *
     *  @param  bool                $useInvalidURIs Use invalid URIs if set
     *
     *  @return array
     *          A list of fetch() test paramters is returned on success
     */
    final public function aggregateFetchParameters($useInvalidURIs = false)
    {
        $combis = $this->aggregateDataURITuples($useInvalidURIs);
        $params = $combis;

        foreach ($combis as $item) {
            $item[] = self::FETCH_DEFAULT;
            $params[] = $item;
        }

        foreach ($combis as $item) {
            $item[] = self::FETCH_CONFIRM;
            $params[] = $item;
        }

        return $params;
    }

    /**
     *  Provide valid data/URI tuples
     *
     *  The provideValidDataValidURITuples() method is a data provider
     *  alias based on the aggregateDataURITuples() method. It will return
     *  valid data / valid URI tuples only.
     *
     *  @return array
     *          A list of data/URI tuples is returned on success
     */
    final public function provideValidDataValidURITuples()
    {
        $params = $this->aggregateDataURITuples(false);
        return $params;
    }

    /**
     *  Provide invalid data/URI tuples
     *
     *  The provideValidDataInvalidURITuples() method is a data provider
     *  alias based on the aggregateDataURITuples() method. It will return
     *  valid data / invalid URI tuples only.
     *
     *  @return array
     *          A list of data/URI tuples is returned on success
     */
    final public function provideValidDataInvalidURITuples()
    {
        $params = $this->aggregateDataURITuples(true);
        return $params;
    }

    /**
     *  Provide valid fetch() test parameteters
     *
     *  The provideValidFetchParameters() method is a data provider alias
     *  that invokes aggregateFetchParameters() without any arguments.
     *
     *  @return array
     *          A list of fetch() test parameters is returned on success
     */
    final public function provideValidFetchParameters()
    {
        $params = $this->aggregateFetchParameters(false);
        return $params;
    }

    /**
     *  Provide fetch() test parameters with invalid URIs
     *
     *  The provideFetchParametersWithInvalidURIs() method is an alias
     *  for aggregateFetchParameters() requesting the use of invalid URIs.
     *
     *  @return array
     *          A list of fetch() test parameters is returned on success
     */
    final public function provideFetchParametersWithInvalidURIs()
    {
        $params = $this->aggregateFetchParameters(true);
        return $params;
    }

    /**
     *  Provide acknowledge() test parameters
     *
     *  The provideAcknowledgeTestParameters() method returns an array of
     *  multiple items, each of whose is an array consisting of either two
     *  or three items:
     *
     *- An array of AnyMessage instances
     *- An URI the provider is expected to consider as valid
     *- A bitmask of acknowledge() flags (might be absent)
     *
     *  @return array
     *          A list of acknowledge() test parameters is returned on
     *          success
     */
    final public function provideAcknowledgeTestParameters()
    {
        $combinations = $this->provideValidDataValidURITuples();
        $parameters = $combinations;

        foreach ($combinations as $item) {
            $item[] = self::ACK_DEFAULT;
            $parameters[] = $item;
        }

        return $parameters;
    }

    /**
     *  Provide discard() test parameters
     *
     *  the provideDiscardTestParameters() method returns an array of
     *  multiple items, each of whose is an array consisting of either two
     *  or three items:
     *
     *- An array of AnyMessage instances
     *- An URI the provider is expected to consider as valid
     *- A bitmask of discard() flags (might be absent)
     *
     *  @return array
     *          A list of discard() test parameters is returned on
     *          success
     */
    final public function provideDiscardTestParameters()
    {
        $combinations = $this->provideValidDataValidURITuples();
        $parameters = $combinations;

        foreach ($combinations as $item) {
            $item[] = self::DISC_DEFAULT;
            $parameters[] = $item;
        }

        foreach ($combinations as $item) {
            $item[] = self::DISC_REQUEUE;
            $parameters[] = $item;
        }

        return $parameters;
    }

    /**
     *  Test the fetch() method
     *
     *  The testFetchWithValidParameters() method is a test case for the
     *  message provider's fetch() method that operates with valid $data,
     *  a valid $uri and valid $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The fetch() flags
     *
     *  @dataProvider   provideValidFetchParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testFetchWithValidParameters(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $providerClass = get_class($provider);

        foreach ($data as $index => $message) {

            $content = $message->getContent();
            $type = $message->getType();

            if (2 === func_num_args()) {
                $message = $this->performFetch($provider, $uri);
            }
            else {
                $message = $this->performFetch($provider, $uri, $flags);
            }

            $this->assertNotNull(
                $message, sprintf(
                "The %s::fetch() method is not expected to return NULL ".
                "in iteration %s",
                $providerClass, $index
            ));

            $expected = array($content, $type);
            $actual = array($message->getContent(), $message->getType());

            $this->assertEquals(
                $expected, $actual, sprintf(
                "The message returned by %s::fetch() is expected to ".
                "have particular content and type values in iteration %s",
                $providerClass, $index
            ));
        }

        if (2 === func_num_args()) {
            $message = $this->performFetch($provider, $uri);
        }
        else {
            $message = $this->performFetch($provider, $uri, $flags);
        }

        $constraint = sprintf(
            "The %s::fetch() method is expected to return NULL after ".
            "the last iteration",
            $providerClass
        );

        $this->assertNull($message, $constraint);
    }

    /**
     *  Test the fetch() method
     *
     *  The testFetchWithInvalidParameters() method is a test case for the
     *  message provider's fetch() method that operates with valid $data,
     *  an invalid $uri and valid $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The fetch() flags
     *
     *  @dataProvider   provideFetchParametersWithInvalidURIs
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testFetchWithInvalidParameters(
        array $data,
        $uri,
        $flags = null
    ) {
        $validURIParamter = $this->provideValidURIs();
        $validURI = $validURIParamter[0][0];
        $provider = $this->getMessageProvider($validURI, $data);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->fetch($uri, $flags);
        }
        else {
            $provider->fetch($uri);
        }
    }

    /**
     *  Test the fetch() and acknowledge() methods
     *
     *  The testAcknowledgeValidToken() method is a test case for the
     *  message provider's fetch() and acknowledge() methods that operates
     *  with valid $data, a valid $uri and valid acknowledge() $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The acknowledge() flags
     *
     *  @dataProvider   provideAcknowledgeTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeValidToken(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $providerClass = get_class($provider);
        $testClass = get_class($this);
        $message = $this->performFetch(
            $provider, $uri, self::FETCH_CONFIRM, $token
        );

        $this->assertInstanceOf(
            self::I_MESSAGE, $message, sprintf(
            "The %s::fetch() method is not expected to return NULL ".
            "during the %s::%s() test",
            $providerClass, $testClass, __FUNCTION__
        ));

        if (2 !== func_num_args()) {
            $this->performAcknowledge($provider, $token, $flags);
        }
        else {
            $this->performAcknowledge($provider, $token);
        }
    }

    /**
     *  Test the fetch() and acknowledge() methods
     *
     *  The testAcknowledgeInvalidTokenBeforeFetch() method is a test case
     *  for the message provider's fetch() and acknowledge() methods thatcw
     *  operates with valid $data, a valid $uri and valid acknowledge()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The acknowledge() flags
     *
     *  @dataProvider   provideAcknowledgeTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeInvalidTokenBeforeFetch(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $token = md5($uri . microtime(true));
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->acknowledge($token, $flags);
        }
        else {
            $provider->acknowledge($token);
        }
    }

    /**
     *  Test the fetch() and acknowledge() methods
     *
     *  The testAcknowledgeInvalidTokenAfterFetch() method is a test case
     *  for the message provider's fetch() and acknowledge() methods that
     *  operates with valid $data, a valid $uri and valid acknowledge()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The acknowledge() flags
     *
     *  @dataProvider   provideAcknowledgeTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeInvalidTokenAfterFetch(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $token = md5($uri . microtime(true));

        $this->performFetch($provider, $uri, self::FETCH_CONFIRM);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->acknowledge($token, $flags);
        }
        else {
            $provider->acknowledge($token);
        }
    }

    /**
     *  Test the fetch() and acknowledge() methods
     *
     *  The testAcknowledgeTokenAfterAcknowledge() method is a test case
     *  for the message provider's fetch() and acknowledge() methods that
     *  operates with valid $data, a valid $uri and valid acknowledge()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The acknowledge() flags
     *
     *  @dataProvider   provideAcknowledgeTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeTokenAfterAcknowledge(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);

        $this->performFetch($provider, $uri, self::FETCH_CONFIRM, $token);
        $this->performAcknowledge($provider, $token);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->acknowledge($token, $flags);
        }
        else {
            $provider->acknowledge($token);
        }
    }

    /**
     *  Test the fetch() and acknowledge() methods
     *
     *  The testAcknowledgeTokenAfterDiscard() method is a test case
     *  for the message provider's fetch() and acknowledge() methods that
     *  operates with valid $data, a valid $uri and valid acknowledge()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The acknowledge() flags
     *
     *  @dataProvider   provideAcknowledgeTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testAcknowledgeTokenAfterDiscard(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);

        $this->performFetch($provider, $uri, self::FETCH_CONFIRM, $token);
        $this->performDiscard($provider, $token);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->acknowledge($token, $flags);
        }
        else {
            $provider->acknowledge($token);
        }
    }

    /**
     *  Test the fetch() and discard() methods
     *
     *  The testDiscardValidToken() method is a test case for the
     *  message provider's fetch() and discard() methods that operates
     *  with valid $data, a valid $uri and valid discard() $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The discard() flags
     *
     *  @dataProvider   provideDiscardTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardValidToken(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $providerClass = get_class($provider);
        $testClass = get_class($this);
        $message = $this->performFetch(
            $provider, $uri, self::FETCH_CONFIRM, $token
        );

        $this->assertInstanceOf(
            self::I_MESSAGE, $message, sprintf(
            "The %s::fetch() method is not expected to return NULL ".
            "during the %s::%s() test",
            $providerClass, $testClass, __FUNCTION__
        ));

        if (2 !== func_num_args()) {
            $this->performDiscard($provider, $token, $flags);
        }
        else {
            $this->performDiscard($provider, $token);
        }
    }

    /**
     *  Test the fetch() and discard() methods
     *
     *  The testDiscardInvalidTokenBeforeFetch() method is a test case
     *  for the message provider's fetch() and discard() methods thatcw
     *  operates with valid $data, a valid $uri and valid discard()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The discard() flags
     *
     *  @dataProvider   provideDiscardTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardInvalidTokenBeforeFetch(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $token = md5($uri . microtime(true));
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->discard($token, $flags);
        }
        else {
            $provider->discard($token);
        }
    }

    /**
     *  Test the fetch() and discard() methods
     *
     *  The testDiscardInvalidTokenAfterFetch() method is a test case
     *  for the message provider's fetch() and discard() methods that
     *  operates with valid $data, a valid $uri and valid discard()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The discard() flags
     *
     *  @dataProvider   provideDiscardTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardInvalidTokenAfterFetch(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);
        $token = md5($uri . microtime(true));

        $this->performFetch($provider, $uri, self::FETCH_CONFIRM);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->discard($token, $flags);
        }
        else {
            $provider->discard($token);
        }
    }

    /**
     *  Test the fetch() and discard() methods
     *
     *  The testDiscardTokenAfterDiscard() method is a test case
     *  for the message provider's fetch() and discard() methods that
     *  operates with valid $data, a valid $uri and valid discard()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The discard() flags
     *
     *  @dataProvider   provideDiscardTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardTokenAfterDiscard(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);

        $this->performFetch($provider, $uri, self::FETCH_CONFIRM, $token);
        $this->performDiscard($provider, $token);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->discard($token, $flags);
        }
        else {
            $provider->discard($token);
        }
    }

    /**
     *  Test the fetch() and discard() methods
     *
     *  The testDiscardTokenAfterAcknowledge() method is a test case
     *  for the message provider's fetch() and discard() methods that
     *  operates with valid $data, a valid $uri and valid discard()
     *  $flags.
     *
     *  @param  array               $data       The test messages
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The discard() flags
     *
     *  @dataProvider   provideDiscardTestParameters
     *  @test
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case of success (see setExpectedException())
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    public function testDiscardTokenAfterAcknowledge(
        array $data,
        $uri,
        $flags = null
    ) {
        $provider = $this->getMessageProvider($uri, $data);

        $this->performFetch($provider, $uri, self::FETCH_CONFIRM, $token);
        $this->performAcknowledge($provider, $token);
        $this->setExpectedException(self::I_EXCEPTION);

        if (2 !== func_num_args()) {
            $provider->discard($token, $flags);
        }
        else {
            $provider->discard($token);
        }
    }

    /**
     *  Invoke the fetch() method
     *
     *  The performFetch() method is used internally to invoke the
     *  fetch() method of the given $provider with the given $uri,
     *  $flags and $token reference.
     *
     *  @param  AnyMessageProvider  $provider   The message provider
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The fetch() flags
     *  @param  string              $token      The token reference
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the URI is considered invalid
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    final protected function performFetch(
        AnyMessageProvider $provider,
        $uri,
        $flags = self::FETCH_DEFAULT,
        &$token = null
    ) {
        $argumentCount = func_num_args() - 1;
        $providerClass = get_class($provider);
        $newToken = null;

        switch ($argumentCount) {
            case 2:
                $message = $provider->fetch($uri, $flags);
                break;
            case 1:
                $message = $provider->fetch($uri);
                break;
            default:
                $message = $provider->fetch($uri, $flags, $newToken);
                break;
        }

        $constraint = sprintf(
            "The %s::fetch() method must return either an instance of ".
            "the AnyMessage interface or NULL when called with %d (uri: ".
            "%s, flags: 0x%X) arguments",
            $providerClass, $argumentCount, $uri, $flags
        );

        $isMessage = $this->isInstanceOf(self::I_MESSAGE);
        $isMessageOrNull = $this->logicalOr($isMessage, $this->isNull());
        $this->assertThat($message, $isMessageOrNull, $constraint);

        $constraint = sprintf(
            "The %s::fetch() method must set the token reference if the ".
            "flags bitmask (0x%X) has the FETCH_CONFIRM bit set (0x%X). ".
            "Otherwise, the reference must be left untouched",
            $providerClass, $flags, self::FETCH_CONFIRM
        );

        if (3 <= $argumentCount && isset($message)
                && self::FETCH_CONFIRM & (int) $flags) {
            $this->assertNotNull($newToken, $constraint);
            $token = $newToken;
        }
        else {
            $this->assertNull($newToken, $constraint);
        }

        return $message;
    }

    /**
     *  Invoke the acknowledge() method
     *
     *  The performAcknowledge() method is used internally to invoke the
     *  acknowledge() method of the given $provider with the given $uri,
     *  $flags and $token reference.
     *
     *  @param  AnyMessageProvider  $provider   The message provider
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The acknowledge() flags
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the token is considered invalid
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    final protected function performAcknowledge(
        AnyMessageProvider $provider,
        $token,
        $flags = self::ACK_DEFAULT
    ) {
        $argumentCount = func_num_args() - 1;
        $providerClass = get_class($provider);

        switch ($argumentCount) {
            case 1:
                $value = $provider->acknowledge($token);
                break;
            default:
                $value = $provider->acknowledge($token, $flags);
                break;
        }

        $constraint = sprintf(
            "The %s::acknowledge() method must NOT return a value ".
            "(called with %d arguments, interpreted as \"%s\", 0x%X)",
            $providerClass, $argumentCount, $token, $flags
        );

        $this->assertNull($value, $constraint);
    }

    /**
     *  Invoke the discard() method
     *
     *  The performDiscard() method is used internally to invoke the
     *  discard() method of the given $provider with the given $uri,
     *  $flags and $token reference.
     *
     *  @param  AnyMessageProvider  $provider   The message provider
     *  @param  string              $uri        The message/event URI
     *  @param  int                 $flags      The discard() flags
     *
     *  @throws \PHPUnit_Framework_AssertionFailedError
     *          Raised in case an assertion has failed
     *
     *  @throws \Lousson\Message\AnyMessageException
     *          Raised in case the token is considered invalid
     *
     *  @throws \Exception
     *          Raised in case of an implementation error
     */
    final protected function performDiscard(
        AnyMessageProvider $provider,
        $token,
        $flags = self::DISC_DEFAULT
    ) {
        $argumentCount = func_num_args() - 1;
        $providerClass = get_class($provider);

        switch ($argumentCount) {
            case 1:
                $value = $provider->discard($token);
                break;
            default:
                $value = $provider->discard($token, $flags);
                break;
        }

        $constraint = sprintf(
            "The %s::discard() method must NOT return a value ".
            "(called with %d arguments, interpreted as \"%s\", 0x%X)",
            $providerClass, $argumentCount, $token, $flags
        );

        $this->assertNull($value, $constraint);
    }
}

