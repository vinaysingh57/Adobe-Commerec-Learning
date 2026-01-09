<?php
namespace Vendor\CrudExample\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Vendor\CrudExample\Model\ItemFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Save extends Action
{
    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Save item from frontend form
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $item = $this->itemFactory->create();
            $item->setData($data);
            $item->save();
            $this->messageManager->addSuccessMessage(__('Item saved successfully.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
