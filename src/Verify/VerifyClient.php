<?php

namespace ApiCheck\Api\Verify;

use stdClass;
use ApiCheck\Api\ApiClient;

class VerifyClient
{
    /**
     * @var ApiClient
     */
    protected $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Verify an email address.
     *
     * Returns an object with: disposable_email (bool), greylisted (bool),
     * status ("valid" | "invalid" | "unknown")
     *
     * @param string $email
     * @return stdClass
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function verifyEmail($email)
    {
        return $this->client->doRequest('GET', "verify/{$this->client->getApiVersion()}/email", ['email' => $email]);
    }

    /**
     * Verify a phone number.
     *
     * Returns an object with: valid (bool), and on success details including
     * country_code, area_code, international_formatted, number_type, etc.
     *
     * @param string $number  Phone number including country code (e.g. +31612345678)
     * @return stdClass
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function verifyPhone($number)
    {
        return $this->client->doRequest('GET', "verify/{$this->client->getApiVersion()}/phone", ['number' => $number]);
    }
}
