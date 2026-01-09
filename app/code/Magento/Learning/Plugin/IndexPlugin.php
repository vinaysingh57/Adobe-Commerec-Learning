<?php
namespace Magento\Learning\Plugin;


use Magento\Checkout\Controller\Cart\Add;
use Psr\Log\LoggerInterface;

class IndexPlugin
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

    public function aroundExecute(Add $subject, callable $proceed)
    {
        $this->logger->info('In Around Plugin Execute Method');
        // Code before original execute
       // \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info('Before execute');

        $result = $proceed(); // Call original execute

        $this->logger->info('In Around Plugin Execute Method After');

        // Code after original execute
        //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info('After execute');

        return $result;
    }
}