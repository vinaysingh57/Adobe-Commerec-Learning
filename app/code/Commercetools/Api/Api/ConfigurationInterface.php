<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Api;

/**
 * Configuration interface for Commercetools API settings
 * @api
 */
interface ConfigurationInterface
{
    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugEnabled(): bool;

    /**
     * Get project key
     *
     * @return string
     */
    public function getProjectKey(): string;

    /**
     * Get client ID
     *
     * @return string
     */
    public function getClientId(): string;

    /**
     * Get client secret
     *
     * @return string
     */
    public function getClientSecret(): string;

    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * Get API URL
     *
     * @return string
     */
    public function getApiUrl(): string;

    /**
     * Get API timeout
     *
     * @return int
     */
    public function getApiTimeout(): int;
}