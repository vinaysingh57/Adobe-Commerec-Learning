<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Model;

use Commercetools\Api\Api\ConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration model for Commercetools API settings
 */
class Configuration implements ConfigurationInterface
{
    const XML_PATH_ENABLED = 'commercetools_api/general/enabled';
    const XML_PATH_DEBUG = 'commercetools_api/general/debug';
    const XML_PATH_PROJECT_KEY = 'commercetools_api/connection/project_key';
    const XML_PATH_CLIENT_ID = 'commercetools_api/connection/client_id';
    const XML_PATH_CLIENT_SECRET = 'commercetools_api/connection/client_secret';
    const XML_PATH_ACCESS_TOKEN = 'commercetools_api/connection/access_token';
    const XML_PATH_API_URL = 'commercetools_api/connection/api_url';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getProjectKey(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PROJECT_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getClientId(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CLIENT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getClientSecret(): string
    {
        $encryptedValue = $this->scopeConfig->getValue(
            self::XML_PATH_CLIENT_SECRET,
            ScopeInterface::SCOPE_STORE
        );

        return $encryptedValue ? $this->encryptor->decrypt($encryptedValue) : '';
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken(): string
    {
        $encryptedValue = $this->scopeConfig->getValue(
            self::XML_PATH_ACCESS_TOKEN,
            ScopeInterface::SCOPE_STORE
        );

        return $encryptedValue ? $this->encryptor->decrypt($encryptedValue) : '';
    }

    /**
     * @inheritDoc
     */
    public function getApiUrl(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_API_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiTimeout(): int
    {
        return 30; // Default timeout in seconds
    }
}