<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Writes only the "audit persistence failed" fallback messages, not the audit events themselves.
 */
class ErrorHandler extends BaseHandler
{
    /**
     * @var int
     */
    protected $loggerType = MonologLogger::ERROR;

    /**
     * @var string
     */
    protected $fileName = '/var/log/customer_audit_log/error.log';
}
