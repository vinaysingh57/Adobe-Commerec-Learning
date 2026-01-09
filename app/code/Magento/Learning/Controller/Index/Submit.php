<?php
namespace Magento\Learning\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Learning\Model\ClassDefault;

class Submit extends Action
{
    protected $classDefault;

    public function __construct(
        Context $context,
        ClassDefault $classDefault
    )
    {
        $this->classDefault = $classDefault;
        parent::__construct($context);
    }

    public function execute()
    {
        echo "Submit ClassA Namespace : ".$this->classDefault->namespace;die;
    }
}