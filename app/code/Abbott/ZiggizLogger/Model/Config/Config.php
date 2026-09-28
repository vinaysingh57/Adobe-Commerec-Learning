<?php
declare(strict_types=1);

namespace Abbott\ZiggizLogger\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Monolog\Logger;

class Config
{
    private const ENABLED = 'abbott_ziggiz/general/enabled';
    private const ENDPOINT = 'abbott_ziggiz/general/endpoint';
    private const TOKEN = 'abbott_ziggiz/general/token';
    private const LOG_LEVEL = 'abbott_ziggiz/general/log_level';
    private const TIMEOUT = 'abbott_ziggiz/general/timeout';
    private const SERVICE_NAME = 'abbott_ziggiz/general/service_name';

    private const DEFAULT_ENDPOINT = 'https://lunarhelix.sh3.us-east-2.aws.ziggiz.io:4318/v1/logs';
    private const DEFAULT_LOG_LEVEL = Logger::INFO;
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_SERVICE_NAME = 'magento';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    /**
     * Is Enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLED);
    }

    /**
     * Get Endpoint
     *
     * @return mixed
     */
    public function getEndpoint()
    {
        $endpoint = $this->scopeConfig->getValue(self::ENDPOINT);
        return !empty($endpoint) ? $endpoint : self::DEFAULT_ENDPOINT;
    }

    /**
     * Get Token
     *
     * @return mixed
     */
    public function getToken()
    {
        $token = $this->scopeConfig->getValue(self::TOKEN);
        return $token ? $this->encryptor->decrypt($token) : '';
    }

    /**
     * Get Log Level
     *
     * @return mixed
     */
    public function getLogLevel()
    {
        $level = (int) $this->scopeConfig->getValue(self::LOG_LEVEL);
        return $level ?: self::DEFAULT_LOG_LEVEL;
    }

    /**
     * Get Timeout
     *
     * @return mixed
     */
    public function getTimeout()
    {
        $timeout = (int) $this->scopeConfig->getValue(self::TIMEOUT);
        return $timeout ?: self::DEFAULT_TIMEOUT;
    }

    /**
     * Get Service Name
     *
     * @return mixed
     */
    public function getServiceName()
    {
        $serviceName = $this->scopeConfig->getValue(self::SERVICE_NAME);
        return !empty($serviceName) ? $serviceName : self::DEFAULT_SERVICE_NAME;
    }
}
