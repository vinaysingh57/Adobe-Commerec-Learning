<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Learning\Cron;

class TestingCron
{

    public function __construct()
    {
        echo "TestingCron";
    }

    public function execute()
    {
        echo "<br/>TestingCron Execute";
    }
}
