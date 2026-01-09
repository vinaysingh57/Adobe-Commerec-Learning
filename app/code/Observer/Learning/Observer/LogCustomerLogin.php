<?php
namespace Observer\Learning\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class LogCustomerLogin implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $this->logger->info('Customer logged in: ' . $customer->getEmail());

        // Dispatch custom event after login
        if (isset($customer)) {
            /** @var \Magento\Framework\Event\ManagerInterface $eventManager */
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $eventManager = $objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
            $eventManager->dispatch('custom_event_demo', ['custom_data' => ['customer_email' => $customer->getEmail()]]);
        }
    }
}
