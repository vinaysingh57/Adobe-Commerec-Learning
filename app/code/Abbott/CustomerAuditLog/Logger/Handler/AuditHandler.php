<?php
/**
 * Copyright © Abbott Laboratories. All rights reserved.
 */
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Writes customer audit entries to var/customer_audit_log/customer_audit.log.
 *
 * Extending the framework Base handler ensures the target directory is created
 * automatically when missing and that the default Magento line format is used.
 */
class AuditHandler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/customer_audit_log/customer_audit.log';
}
