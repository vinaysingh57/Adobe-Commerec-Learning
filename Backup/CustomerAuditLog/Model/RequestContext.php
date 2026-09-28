<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Math\Random;

/**
 * Captures the ambient request data (IP, user agent, correlation id) shared by
 * every observer/plugin so it isn't duplicated across capture points.
 */
class RequestContext
{
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param RemoteAddress $remoteAddress
     * @param RequestInterface $request
     * @param Random $random
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        RequestInterface $request,
        Random $random
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
        $this->random = $random;
    }

    /**
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        return $ip !== false ? $ip : null;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        $userAgent = $this->request->getServer('HTTP_USER_AGENT');
        return $userAgent !== null ? (string)$userAgent : null;
    }

    /**
     * One id per HTTP request so all audit events emitted while handling it can be correlated.
     *
     * @return string
     */
    public function getCorrelationId(): string
    {
        $correlationId = $this->request->getParam('_abbott_audit_correlation_id');
        if (!$correlationId) {
            $correlationId = $this->random->getUniqueHash();
            if ($this->request instanceof \Magento\Framework\App\Request\Http) {
                $this->request->setParam('_abbott_audit_correlation_id', $correlationId);
            }
        }
        return (string)$correlationId;
    }
}
