<?php
namespace Eabbott\Ancatalog\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Zend\Form\Element\DateTime;
 
class UpdateAnCatalogDate extends Command
{
    // Constant declared for name of the parameter
    const DATE = 'date';
    const CONFIG_PATH = "ancatalog/configuration/updatedat";
    const ENABLE_CONFIG_PATH = "ancatalog/configuration/enable";
 
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
 
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;
 
    /**
     * @var \Eabbott\Ancatalog\Helper\Data
     */
    protected $ancatalogHelper;
 
    /**
     * UpdateAnCatalogDate constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Eabbott\Ancatalog\Helper\Data $ancatalogHelper
     * @param null $name
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Eabbott\Ancatalog\Helper\Data $ancatalogHelper,
        $name = null
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList =  $cacheTypeList;
        $this->ancatalogHelper = $ancatalogHelper;
        parent::__construct($name);
    }
 
    /**
     * configure CLI command
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::DATE,
                null,
                InputOption::VALUE_REQUIRED,
                'Provide AN catalog updated date '
            )
        ];
 
        $this->setName('update:date');
        $this->setDescription('Command to update AN catalog date, Date should be in m-d-Y format');
        $this->setDefinition($options);
 
        parent::configure();
    }
 
    /**
     *  Execute the CLI
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this|int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
 
            //Check if configuration is enabled or not
            if (!$this->ancatalogHelper->getConfig(self::ENABLE_CONFIG_PATH)) {
                return $output->writeln("ERROR: Please enable module from configuration.");
            }
 
            // Get the date from input
            $updatedAt = $input->getOption(self::DATE);
            // Update the date to configuration
            if (isset($updatedAt) && $this->validateDate($updatedAt)) {
                $this->resourceConfig->saveConfig(
                    self::CONFIG_PATH,
                    $updatedAt,
                    \Eabbott\Ancatalog\Helper\Data::DEFAULT_SCOPE,
                    0
                );
 
                // Clearing the cache to update date on frontend
                $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                $output->writeln("Date is updated as ".$updatedAt);
 
            } else {
                $output->writeln("Date is required and should be in MM-DD-YYYY format");
            }
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
        }
 
        return $this;
    }
 
    /**
     * Validate date format before update
     * @param $date
     * @param string $format
     * @return bool
     */
    public function validateDate($date, $format = 'm-d-Y')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}