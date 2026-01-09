<?php
namespace Magento\Learning\Plugin;


use Psr\Log\LoggerInterface;


class ProductPlugin
{

    protected $logger;

    public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }


    
    public function beforeGetName(\Magento\Catalog\Model\Product $subject)
    {
        $this->logger->info('Getting product name for ID: ' . $subject->getName());
        
    }

    

    public function afterGetName($subject, $result)
    {
        return $result . ' - Special Edition';
    }



    public function aroundGetName(\Magento\Catalog\Model\Product $subject, callable $proceed)
    {
        $originalName = $proceed();
        return 'Modified: ' . $originalName;
    }
}
