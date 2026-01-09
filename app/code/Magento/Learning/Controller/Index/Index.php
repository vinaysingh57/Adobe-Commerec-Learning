<?php
namespace Magento\Learning\Controller\Index;
use Magento\Learning\Model\ClassDefault;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $classDefault;
    protected $_pageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		ClassDefault $classDefault)
	{
		$this->_pageFactory = $pageFactory;
		$this->classDefault = $classDefault;
		return parent::__construct($context);
	}

	public function execute()
	{
		echo "In Execute";//die;
		//echo "Index ClassA Namespace : ".$this->classDefault->namespace;die;
        return $this->_pageFactory->create();
	}
}