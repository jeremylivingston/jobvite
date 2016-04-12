<?php

namespace Livingstn\Jobvite;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Jeremy Livingston
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $response;

    public function setUp()
    {
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->getMock();
    }

    public function tearDown()
    {
        $this->response = null;
        $this->client = null;
    }

    /**
     * @expectedException \Livingstn\Jobvite\Exception\JobviteException
     */
    public function testInvalidJsonThrowsException()
    {
        $this->response->method('getBody')
            ->will($this->returnValue('this is not json'));

        $this->client->method('request')
            ->will($this->returnValue($this->response));

        $jobviteClient = new Client('companyId', 'apiKey', 'secretKey');
        $jobviteClient->setClient($this->client);

        $jobviteClient->getJobFeed();
    }

    public function testValidJsonReturnsStdClass()
    {
        $this->response->method('getBody')
            ->will($this->returnValue('{ "this": "is totally", "json": true }'));

        $this->client->method('request')
            ->will($this->returnValue($this->response));

        $jobviteClient = new Client('companyId', 'apiKey', 'secretKey');
        $jobviteClient->setClient($this->client);

        $return = $jobviteClient->getJobFeed();

        $this->assertInstanceOf('stdClass', $return);
    }

    /**
     * @dataProvider queryParameterProvider
     */
    public function testAuthenticationQueryParameterIsSet($companyId, $apiKey, $secret, $expectedKey, $expectedValue)
    {
        $this->response->method('getBody')
            ->will($this->returnValue('{}'));

        $this->client->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function($options) use ($expectedKey, $expectedValue) {
                    // Test that the expected parameter exists in the "query" array
                    return isset($options['query'][$expectedKey]) && $options['query'][$expectedKey] === $expectedValue;
                })
            )
            ->will($this->returnValue($this->response));

        $jobviteClient = new Client($companyId, $apiKey, $secret);
        $jobviteClient->setClient($this->client);

        $jobviteClient->getJobFeed();
    }

    public function testDefaultUrlIsProduction()
    {
        $this->response->method('getBody')
            ->will($this->returnValue('{}'));

        $this->client->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function($options) {
                    return $options['base_uri'] === Client::URL_PRODUCTION;
                })
            )
            ->will($this->returnValue($this->response));

        $jobviteClient = new Client('companyId', 'apiKey', 'secretKey');
        $jobviteClient->setClient($this->client);

        $jobviteClient->getJobFeed();
    }

    public function queryParameterProvider()
    {
        return array(
            ['CID', 'AKEY', 'SKEY', 'companyId', 'CID'],
            ['CID', 'AKEY', 'SKEY', 'api', 'AKEY'],
            ['CID', 'AKEY', 'SKEY', 'sc', 'SKEY'],
        );
    }

    public function testValidFilterIsAddedToQueryString()
    {
        $this->response->method('getBody')
            ->will($this->returnValue('{}'));

        $this->client->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function($options) {
                    return isset($options['query']['type']) && $options['query']['type'] === 'my_type';
                })
            )
            ->will($this->returnValue($this->response));

        $jobviteClient = new Client('companyId', 'apiKey', 'secret');
        $jobviteClient->setClient($this->client);

        $jobviteClient->getJobFeed(['type' => 'my_type']);
    }

    public function testInvalidFilterIsNotAddedToQueryString()
    {
        $this->response->method('getBody')
            ->will($this->returnValue('{}'));

        $this->client->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function($options) {
                    return !isset($options['query']['made_up_type']);
                })
            )
            ->will($this->returnValue($this->response));

        $jobviteClient = new Client('companyId', 'apiKey', 'secret');
        $jobviteClient->setClient($this->client);

        $jobviteClient->getJobFeed(['made_up_type' => 'my_type']);
    }
}
