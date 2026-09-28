<?php
/**
 * Copyright © Abbott Laboratories. All rights reserved.
 */
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Central service responsible for recording customer audit events.
 *
 * All entries are emitted through a dedicated Monolog channel that targets
 * var/customer_audit_log/customer_audit.log using the default Magento format.
 */
class AuditLogger
{
    public const SYSTEM = 'novasalud';

    public const EVENT_LOGIN_SUCCESS = 'login_success';
    public const EVENT_LOGIN_FAILURE = 'login_failure';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_PASSWORD_CHANGED = 'password_changed';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILURE = 'failure';

    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param LoggerInterface $logger
     * @param IdentityGeneratorInterface $identityGenerator
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly IdentityGeneratorInterface $identityGenerator,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Log a successful customer login.
     *
     * @param int|string|null $customerId
     * @return void
     */
    public function logLoginSuccess($customerId): void
    {
        $this->logger->info(self::EVENT_LOGIN_SUCCESS, $this->buildContext([
            'customer_id' => $this->normalizeId($customerId),
            'event_type' => self::EVENT_LOGIN_SUCCESS,
            'status' => self::STATUS_SUCCESS,
        ]));
    }

    /**
     * Log a failed customer login attempt.
     *
     * @param int|string|null $customerId
     * @param string $username
     * @param string|null $reason
     * @return void
     */
    public function logLoginFailure($customerId, string $username, ?string $reason = null): void
    {
        $this->logger->warning(self::EVENT_LOGIN_FAILURE, $this->buildContext([
            'customer_id' => $this->normalizeId($customerId),
            'username' => $this->maskEmail($username),
            'username_hash' => $this->hashUsername($username),
            'event_type' => self::EVENT_LOGIN_FAILURE,
            'status' => self::STATUS_FAILURE,
            'failure_reason' => $reason !== null && $reason !== '' ? $reason : 'Invalid credentials',
        ]));
    }

    /**
     * Log a customer logout.
     *
     * @param int|string|null $customerId
     * @return void
     */
    public function logLogout($customerId): void
    {
        $this->logger->info(self::EVENT_LOGOUT, $this->buildContext([
            'customer_id' => $this->normalizeId($customerId),
            'event_type' => self::EVENT_LOGOUT,
            'status' => self::STATUS_SUCCESS,
        ]));
    }

    /**
     * Log a customer password change.
     *
     * @param int|string|null $customerId
     * @return void
     */
    public function logPasswordChanged($customerId): void
    {
        $this->logger->info(self::EVENT_PASSWORD_CHANGED, $this->buildContext([
            'customer_id' => $this->normalizeId($customerId),
            'event_type' => self::EVENT_PASSWORD_CHANGED,
            'status' => self::STATUS_SUCCESS,
        ]));
    }

    /**
     * Prepend a unique event id and occurrence timestamp to the log context.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function buildContext(array $context): array
    {
        return array_merge(
            [
                'event_id' => $this->identityGenerator->generateId(),
                'occurred_at' => $this->dateTime->gmtDate(self::DATE_TIME_FORMAT),
            ],
            $context,
            ['system' => self::SYSTEM]
        );
    }

    /**
     * Normalize a customer identifier for logging.
     *
     * @param int|string|null $customerId
     * @return int|string
     */
    private function normalizeId($customerId)
    {
        if ($customerId === null || $customerId === '') {
            return 'unknown';
        }

        return is_numeric($customerId) ? (int) $customerId : $customerId;
    }

    /**
     * Mask an email for audit output: reveal first/last 2 chars of the local part, keep the domain.
     *
     * @param string $value
     * @return string
     */
    private function maskEmail(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'unknown';
        }

        $atPos = strrpos($value, '@');
        if ($atPos === false) {
            return $this->maskSegment($value);
        }

        return $this->maskSegment(substr($value, 0, $atPos)) . substr($value, $atPos);
    }

    /**
     * Reveal the first and last two characters of a segment, mask the remainder.
     *
     * @param string $segment
     * @return string
     */
    private function maskSegment(string $segment): string
    {
        $length = strlen($segment);
        if ($length <= 4) {
            return str_repeat('*', max($length, 1));
        }

        return substr($segment, 0, 2) . str_repeat('*', $length - 4) . substr($segment, -2);
    }

    /**
     * Deterministic, non-reversible identifier for data-lake correlation.
     *
     * @param string $value
     * @return string
     */
    private function hashUsername(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }
}
