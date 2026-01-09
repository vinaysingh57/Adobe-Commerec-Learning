<?php
namespace Observer\Learning\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CustomEventObserver implements ObserverInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getData('custom_data');
        $this->logger->info('Custom event triggered with data: ' . print_r($data, true));
    }
}
