<?php

namespace Aerni\Spotify;

class PendingRequest
{
    public $endpoint;

    public $acceptedParams;

    public $responseArrayKey;

    public $requestedParams;

    public ?string $accessToken;

    public string $method = 'GET';

    public array $body = [];

    public ?string $rawBody = null;

    public function __construct(string $endpoint, array $acceptedParams = [], ?string $accessToken = null)
    {
        $this->endpoint = $endpoint;
        $this->acceptedParams = $acceptedParams;
        $this->accessToken = $accessToken;
    }

    /**
     * Set the country if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function country(?string $country = null): self
    {
        $this->setRequestedParam('country', $country);

        return $this;
    }

    /**
     * Set the fields if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function fields(string $fields): self
    {
        $this->setRequestedParam('fields', $fields);

        return $this;
    }

    /**
     * Set include_external if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function includeExternal(string $value): self
    {
        $this->setRequestedParam('include_external', $value);

        return $this;
    }

    /**
     * Set include_groups if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function includeGroups(string $value): self
    {
        $this->setRequestedParam('include_groups', $value);

        return $this;
    }

    /**
     * Set the limit if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function limit(int $limit): self
    {
        $this->setRequestedParam('limit', $limit);

        return $this;
    }

    /**
     * Set the offset if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function offset(int $offset): self
    {
        $this->setRequestedParam('offset', $offset);

        return $this;
    }

    /**
     * Set the market if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function market(?string $market = null): self
    {
        $this->setRequestedParam('market', $market);

        return $this;
    }

    /**
     * Set the locale if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function locale(?string $locale = null): self
    {
        $this->setRequestedParam('locale', $locale);

        return $this;
    }

    /**
     * Set the timestamp if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function timestamp(string $timestamp): self
    {
        $this->setRequestedParam('timestamp', $timestamp);

        return $this;
    }

    /**
     * Set the before cursor/timestamp if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function before(int|string $before): self
    {
        $this->setRequestedParam('before', $before);

        return $this;
    }

    /**
     * Set the after cursor/timestamp if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function after(int|string $after): self
    {
        $this->setRequestedParam('after', $after);

        return $this;
    }

    /**
     * Set the time_range if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function timeRange(string $timeRange): self
    {
        $this->setRequestedParam('time_range', $timeRange);

        return $this;
    }

    /**
     * Set the device_id if provided.
     *
     * @return $this
     *
     * @throws Exceptions\ValidatorException
     */
    public function deviceId(string $deviceId): self
    {
        $this->setRequestedParam('device_id', $deviceId);

        return $this;
    }

    /**
     * Add the requested parameters to an array.
     *
     * @param  int|string|null  $value
     *
     * @throws Exceptions\ValidatorException
     */
    private function setRequestedParam(string $requestedParam, $value): void
    {
        Validator::validateRequestedParam($requestedParam, $this->acceptedParams);

        $this->requestedParams[$requestedParam] = $value;
    }

    /**
     * Execute the request. This is the final method and has to be called at the end of the method chain.
     *
     * @throws Exceptions\SpotifyApiException
     */
    public function get(?string $responseArrayKey = null): array
    {
        $this->responseArrayKey = $responseArrayKey;

        return resolve(CreateRequestAction::class)->execute($this);
    }
}
