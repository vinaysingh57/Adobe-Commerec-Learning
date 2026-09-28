<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Service;

use Commercetools\Api\Api\ConfigurationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * API client service for Commercetools REST API
 */
class ApiClient
{
    /**
     * @var ConfigurationInterface
     */
    private ConfigurationInterface $configuration;

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ConfigurationInterface $configuration
     * @param Curl $curl
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigurationInterface $configuration,
        Curl $curl,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->curl = $curl;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Make GET request to Commercetools API
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws LocalizedException
     */
    public function get(string $endpoint, array $params = []): array
    {
        if (!$this->configuration->isEnabled()) {
            throw new LocalizedException(__('Commercetools API module is disabled.'));
        }
        //echo "<pre>";print_r($endpoint);echo "</pre>";die;
        $url = $this->buildUrl($endpoint, $params);

        //echo "<pre>";print_r($url);echo "</pre>";die;

        $this->setupCurlHeaders();

        try {
            $this->curl->get($url);
            $response = $this->curl->getBody();
            $statusCode = $this->curl->getStatus();

            $this->logRequest('GET', $url, [], $response, $statusCode);

            if ($statusCode >= 400) {
                throw new LocalizedException(
                    __('Commercetools API request failed with status %1: %2', $statusCode, $response)
                );
            }

            return $this->json->unserialize($response);

        } catch (\Exception $e) {
            $this->logger->error('Commercetools API GET request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__('API request failed: %1', $e->getMessage()));
        }
    }

    /**
     * Make POST request to Commercetools API
     *
     * @param string $endpoint
     * @param array $data
     * @param array $params
     * @return array
     * @throws LocalizedException
     */
    public function post(string $endpoint, array $data = [], array $params = []): array
    {
        if (!$this->configuration->isEnabled()) {
            throw new LocalizedException(__('Commercetools API module is disabled.'));
        }

        $url = $this->buildUrl($endpoint, $params);
        $this->setupCurlHeaders();

        try {
            $jsonData = $this->json->serialize($data);
            $this->curl->post($url, $jsonData);
            $response = $this->curl->getBody();
            $statusCode = $this->curl->getStatus();

            $this->logRequest('POST', $url, $data, $response, $statusCode);

            if ($statusCode >= 400) {
                throw new LocalizedException(
                    __('Commercetools API request failed with status %1: %2', $statusCode, $response)
                );
            }

            return $this->json->unserialize($response);

        } catch (\Exception $e) {
            $this->logger->error('Commercetools API POST request failed', [
                'url' => $url,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__('API request failed: %1', $e->getMessage()));
        }
    }

    /**
     * Build full API URL
     *
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    private function buildUrl(string $endpoint, array $params = []): string
    {
        //$baseUrl $baseUrl = rtrim($this->configuration->getApiUrl(), '/');
        $baseUrl = 'https://api.us-east-2.aws.commercetools.com';
        //$projectKey = $this->configuration->getProjectKey();
        $projectKey = 'quick-deliver-b2c';
        //$endpoint = 'https://api.us-east-2.aws.commercetools.com';
        //$endpoint ='';
        
        $url = sprintf('%s/%s%s', $baseUrl, $projectKey, $endpoint);
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Setup cURL headers for Commercetools API
     */
    private function setupCurlHeaders(): void
    {
        /*
        $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->configuration->getAccessToken()
        ]);
*/

         $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . 'VDA6PS0AuOW7jk5-vLJhGT8I1M-SK81d'
        ]);

        $this->curl->setTimeout($this->configuration->getApiTimeout());
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 2);
    }

    /**
     * Log API request for debugging
     *
     * @param string $method
     * @param string $url
     * @param array $requestData
     * @param string $response
     * @param int $statusCode
     */
    private function logRequest(
        string $method,
        string $url,
        array $requestData,
        string $response,
        int $statusCode
    ): void {
        if ($this->configuration->isDebugEnabled()) {
            $this->logger->info('Commercetools API Request', [
                'method' => $method,
                'url' => $url,
                'request_data' => $requestData,
                'response' => $response,
                'status_code' => $statusCode
            ]);
        }
    }
}