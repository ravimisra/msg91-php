<?php

namespace Craftsys\Msg91\Requests;

use Craftsys\Msg91\Exceptions\ValidationException;
use Craftsys\Msg91\Options;
use Craftsys\Msg91\Response;
use GuzzleHttp\Client as GuzzleHttpClient;

abstract class Request
{

    /**
     * Http client for request handling
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Options for the request
     * @var \Craftsys\Msg91\Options
     */
    protected $options;

    /**
     * Request Method
     * @var string
     */
    protected $method = "POST";

    /**
     * Request url
     * @var string
     */
    protected $url = "";

    /**
     * Validation instance
     */
    protected $validator;

    /**
     * Create a new request instance
     *
     * @param \GuzzleHttp\Client $httpClient
     * @param \Craftsys\Msg91\Options $options
     * @return void
     */
    public function __construct(GuzzleHttpClient $httpClient, Options $options)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
        $this->validator = new Validator();
    }

    /**
     * Get the request payload
     * @return array
     */
    abstract protected function getPayload(): array;

    protected function validate(array $payload)
    {
        $token = $payload['authkey'] ?? "";
        if (!$token) {
            $this->validator->addError('authkey', 'Authkey is required');
        }
    }

    /**
     * Send the request and return the response or exception
     * @return \Craftsys\Msg91\Response|null
     * @throws \Craftsys\Msg91\Exceptions\ResponseErrorException
     * |\Craftsys\Msg91\Exceptions\ValidationException
     * |\GuzzleHttp\Exception\ClientException
     */
    public function handle()
    {
        $client = $this->httpClient;
        $payload = $this->getPayload();
        $this->validate($payload);
        if (!$this->validator->isValid()) {
            throw new ValidationException('Invalid request parameters', 422, null, $this->validator->errors());
        }
        $method = strtolower($this->method);
        return new Response(
            $client->{$method}($this->url, [
                "form_params" => $payload
            ])
        );
    }
}
