<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Writes one line per customer authentication audit event.
 */
class AuditHandler extends BaseHandler
{
    /**
     * @var int
     */
    protected $loggerType = MonologLogger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/customer_audit_log/audit.log';
}
