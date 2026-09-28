<?php
/**
 * Copyright © Abbott Laboratories. All rights reserved.
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Abbott_CustomerAuditLog',
    __DIR__
);
