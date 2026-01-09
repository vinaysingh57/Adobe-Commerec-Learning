<?php

namespace VoiceSearch\ProductSearch\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class Search implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        Image $imageHelper,
        PriceHelper $priceHelper,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Execute voice search
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $searchQuery = $this->request->getParam('q', '');
            $searchQuery = 'testing';
            
            if (empty($searchQuery)) {
                return $result->setData([
                    'success' => false,
                    'message' => 'Search query is required',
                    'products' => []
                ]);
            }

            // Create product collection with search filter
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(['name', 'price', 'image', 'url_key']);
            $collection->addAttributeToFilter('status', 1); // Only enabled products
            $collection->addAttributeToFilter(
                'name',
                ['like' => '%' . $searchQuery . '%']
            );
            $collection->setStore($this->storeManager->getStore());
            $collection->setPageSize(10); // Limit results
            
            $products = [];
            
            foreach ($collection as $product) {
                try {
                    $productData = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'price' => $this->priceHelper->currency($product->getFinalPrice(), true, false),
                        'image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
                        'url' => $product->getProductUrl(),
                        'sku' => $product->getSku()
                    ];
                    $products[] = $productData;
                } catch (\Exception $e) {
                    $this->logger->error('Voice Search - Error processing product ' . $product->getId() . ': ' . $e->getMessage());
                    continue;
                }
            }

            return $result->setData([
                'success' => true,
                'message' => sprintf('Found %d products for "%s"', count($products), $searchQuery),
                'query' => $searchQuery,
                'products' => $products,
                'total' => $collection->getSize()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Voice Search Error: ' . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => 'An error occurred during search. Please try again.',
                'products' => []
            ]);
        }
    }
}