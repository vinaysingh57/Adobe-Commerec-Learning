<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$om = Bootstrap::create(BP, $_SERVER)->getObjectManager();
$area = $argv[1] ?? 'crontab';
$om->get(\Magento\Framework\App\State::class)->setAreaCode($area);

// The Ziggiz handler is bound to Monolog only inside an area's DI config, never globally.
$om->configure($om->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class)->load($area));

$logger = $om->get(\Psr\Log\LoggerInterface::class);

$prop = (new ReflectionClass(\Monolog\Logger::class))->getProperty('handlers');
$prop->setAccessible(true);
$names = array_map('get_class', $prop->getValue($om->get(\Magento\Framework\Logger\Monolog::class)));
echo "area=$area\nhandlers: " . implode(', ', $names) . "\n\n";

$logger->info('ZIGGIZ-TEST plain info message');
$logger->warning('ZIGGIZ-TEST with magento context', [
    'order_id' => '000000123', 'customer_id' => '42', 'store' => '1',
    'module' => 'Abbott_ZiggizLogger', 'correlation_id' => 'local-test-abc',
]);
try {
    throw new \RuntimeException('ZIGGIZ-TEST simulated failure', 555);
} catch (\Throwable $e) {
    $logger->error('ZIGGIZ-TEST exception captured', ['exception' => $e]);
}
$logger->debug('ZIGGIZ-TEST debug that SHOULD be filtered out');
echo "emitted\n";
