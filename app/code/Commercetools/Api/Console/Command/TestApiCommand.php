<?php
/**
 * Copyright © Commercetools API Integration. All rights reserved.
 */

declare(strict_types=1);

namespace Commercetools\Api\Console\Command;

use Commercetools\Api\Api\ProductRepositoryInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to test Commercetools API product retrieval
 */
class TestApiCommand extends Command
{
    const ARGUMENT_PRODUCT_ID = 'product-id';

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param string|null $name
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        string $name = null
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('commercetools:api:test')
            ->setDescription('Test Commercetools API by fetching a product')
            ->addArgument(
                self::ARGUMENT_PRODUCT_ID,
                InputArgument::REQUIRED,
                'Product ID to fetch from Commercetools'
            );

        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productId = $input->getArgument(self::ARGUMENT_PRODUCT_ID);

        try {
            $output->writeln('<info>Fetching product from Commercetools...</info>');
            $product = $this->productRepository->getById($productId);

            $output->writeln('<info>Product found:</info>');
            $output->writeln(sprintf('ID: %s', $product->getId()));
            $output->writeln(sprintf('Key: %s', $product->getKey() ?? 'N/A'));
            $output->writeln(sprintf('Version: %s', $product->getVersion() ?? 'N/A'));
            $output->writeln(sprintf('Created At: %s', $product->getCreatedAt() ?? 'N/A'));
            $output->writeln(sprintf('Last Modified: %s', $product->getLastModifiedAt() ?? 'N/A'));

            // Display master data if available
            $masterData = $product->getMasterData();
            if (!empty($masterData)) {
                $output->writeln('<info>Master Data:</info>');
                $output->writeln(json_encode($masterData, JSON_PRETTY_PRINT));
            }

            $output->writeln('<success>Product retrieved successfully!</success>');
            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}