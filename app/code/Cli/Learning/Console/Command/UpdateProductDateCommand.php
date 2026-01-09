<?php
namespace Cli\Learning\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProductDateCommand extends Command
{
    const INPUT_KEY_SKU = 'sku';

    /** @var ProductRepositoryInterface */
    private $productRepository;
    /** @var DateTime */
    private $dateTime;
    /** @var State */
    private $state;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DateTime $dateTime,
        State $state
    ) {
        $this->productRepository = $productRepository;
        $this->dateTime = $dateTime;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('cli_learning:update-product-date')
            ->setDescription('Update current date for a product by SKU')
            ->addArgument(self::INPUT_KEY_SKU, InputArgument::REQUIRED, 'Product SKU');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('adminhtml');
        $sku = $input->getArgument(self::INPUT_KEY_SKU);
        try {
            $product = $this->productRepository->get($sku);
            $product->setData('updated_at', $this->dateTime->gmtDate());
            $this->productRepository->save($product);
            $output->writeln("Product with SKU $sku updated_at date set to " . $product->getData('updated_at'));
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
        return 0;
    }
}
