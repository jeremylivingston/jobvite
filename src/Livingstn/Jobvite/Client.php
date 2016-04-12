<?php

namespace Livingstn\Jobvite;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Jobvite client that can be used to retrieve a job feed for a given company
 *
 * All properties in the API response are decoded and returned as an stdClass. For a
 * full list of request and response parameters, see the Jobvite documentation at:
 * http://careers.jobvite.com/careersites/JobviteWebServices.pdf
 *
 * @author Jeremy Livingston
 */
class Client
{
    const JOB_FEED_FILTERS = ['type', 'availableTo', 'category', 'location', 'region', 'start', 'count'];

    const URL_PRODUCTION = 'https://api.jobvite.com';
    const URL_STAGING = 'https://api-stg.jobvite.com';

    /** @var ClientInterface */
    private $client;

    /** @var string */
    private $baseUri = self::URL_PRODUCTION;

    /** @var string */
    private $companyId;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $secretKey;

    /**
     * @param string $companyId
     * @param string $apiKey
     * @param string $secretKey
     */
    public function __construct($companyId, $apiKey, $secretKey)
    {
        $this->client = new GuzzleClient();
        $this->companyId = $companyId;
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    /**
     * Retrieve the job feed associated with the configured company ID
     *
     * @param array $filters Optional filters with keys found in self::JOB_FEED_FILTERS
     * @return string
     * @throws Exception\JobviteException
     */
    public function getJobFeed(array $filters = [])
    {
        $response = $this->client->request(
            'GET',
            '/v1/jobFeed',
            ['base_uri' => $this->baseUri, 'query' => $this->buildQuery($filters, self::JOB_FEED_FILTERS)]
        );

        try {
            return \GuzzleHttp\json_decode((string) $response->getBody());
        } catch (\InvalidArgumentException $invalidArgumentException) {
            throw new Exception\JobviteException('Unable to decode response as JSON.', 0, $invalidArgumentException);
        }
    }

    /**
     * Toggle the base URI based on whether the client is using production
     *
     * @param bool $production
     */
    public function setProduction($production)
    {
        $this->baseUri = $production ? self::URL_PRODUCTION : self::URL_STAGING;
    }

    /**
     * Override the default Guzzle Client implementation
     *
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Merge the required authentication keys with the list of valid optional filters found in $allowedKeys
     *
     * @param array $filters
     * @param array $allowedKeys
     * @return array
     */
    private function buildQuery(array $filters, array $allowedKeys = array())
    {
        $query = [
            'companyId' => $this->companyId,
            'api' => $this->apiKey,
            'sc' => $this->secretKey
        ];

        if ($allowedKeys) {
            $filters = array_intersect_key($filters, array_flip($allowedKeys));
        }

        return array_merge($query, $filters);
    }
}
