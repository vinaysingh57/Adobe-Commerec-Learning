<?php
declare(strict_types=1);

namespace Abbott\ZiggizLogger\Logger\Handler;

use Abbott\ZiggizLogger\Model\Config\Config;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class Ziggiz extends AbstractProcessingHandler
{
    private const TOPIC_NAME = 'abbott.ziggiz.logger';

    private int $requestCount = 0;
    private ?int $configuredLevel = null;

    public function __construct(
        private readonly Config $config,
        private readonly PublisherInterface $publisher,
        private readonly Json $serializer,
        $level = null,
        bool $bubble = true
    ) {
        $this->configuredLevel = is_int($level) ? $level : (int) $this->config->getLogLevel();
        parent::__construct($this->configuredLevel, $bubble);
    }

    protected function write(array $record): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $effectiveLevel = $this->configuredLevel ?? Logger::INFO;
        $recordLevel = (int) ($record['level'] ?? $effectiveLevel);

        if ($recordLevel < $effectiveLevel) {
            return;
        }

        $endpoint = $this->config->getEndpoint();
        $token = $this->config->getToken();
        $serviceName = $this->config->getServiceName();
        $timeout = $this->config->getTimeout();

        $resourceAttributes = [];
        $this->appendAttribute($resourceAttributes, 'service.name', $serviceName);
        $this->appendAttribute($resourceAttributes, 'service.namespace', 'abbott');
        $this->appendAttribute($resourceAttributes, 'service.instance.id', $this->resolveServiceInstanceId());
        $this->appendAttribute($resourceAttributes, 'telemetry.sdk.language', 'php');
        $this->appendAttribute($resourceAttributes, 'telemetry.sdk.name', 'abbott-ziggiz-logger');
        $this->appendAttribute($resourceAttributes, 'telemetry.sdk.version', defined('PHP_VERSION') ? PHP_VERSION : null);
        $this->appendAttribute($resourceAttributes, 'process.runtime.name', 'php');
        $this->appendAttribute($resourceAttributes, 'process.runtime.version', PHP_VERSION);
        $this->appendAttribute($resourceAttributes, 'process.runtime.description', PHP_SAPI);
        $this->appendAttribute($resourceAttributes, 'process.pid', (string) getmypid());
        $this->appendAttribute($resourceAttributes, 'process.executable.path', PHP_BINARY);
        $this->appendAttribute($resourceAttributes, 'host.arch', php_uname('m'));
        $this->appendAttribute($resourceAttributes, 'os.type', PHP_OS_FAMILY);
        $this->appendAttribute($resourceAttributes, 'os.description', php_uname());

        $hostName = gethostname();
        if (is_string($hostName) && $hostName !== '') {
            $this->appendAttribute($resourceAttributes, 'host.name', $hostName);
        }

        $mageMode = (string) (getenv('MAGE_MODE') ?: '');
        if ($mageMode !== '') {
            $this->appendAttribute($resourceAttributes, 'deployment.environment', strtolower($mageMode));
        }

        $this->appendAttribute($resourceAttributes, 'magento.area', isset($_SERVER['MAGE_RUN_TYPE']) ? (string) $_SERVER['MAGE_RUN_TYPE'] : null);
        $this->appendAttribute($resourceAttributes, 'magento.store', isset($_SERVER['MAGE_RUN_CODE']) ? (string) $_SERVER['MAGE_RUN_CODE'] : null);

        $logAttributes = [];
        $this->appendAttribute($logAttributes, 'channel', (string) ($record['channel'] ?? 'main'));
        $this->appendAttribute($logAttributes, 'logger.name', (string) ($record['channel'] ?? 'main'));
        $this->appendAttribute($logAttributes, 'event.domain', 'log');
        $this->appendAttribute($logAttributes, 'log.level', (string) ($record['level_name'] ?? Logger::getLevelName($recordLevel)));
        $this->appendAttribute($logAttributes, 'log.logger', (string) ($record['channel'] ?? 'main'));
        $this->appendAttribute($logAttributes, 'code.filepath', isset($record['extra']['file']) ? (string) $record['extra']['file'] : null);
        $this->appendAttribute($logAttributes, 'code.function', isset($record['extra']['function']) ? (string) $record['extra']['function'] : null);
        $this->appendAttribute($logAttributes, 'code.line', isset($record['extra']['line']) ? (string) $record['extra']['line'] : null);
        $this->appendAttribute($logAttributes, 'process.pid', (string) getmypid());
        $this->appendAttribute($logAttributes, 'http.method', isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : null);
        $this->appendAttribute($logAttributes, 'url.path', isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : null);
        $this->appendAttribute($logAttributes, 'url.scheme', isset($_SERVER['REQUEST_SCHEME']) ? (string) $_SERVER['REQUEST_SCHEME'] : null);
        $this->appendAttribute($logAttributes, 'server.address', isset($_SERVER['SERVER_ADDR']) ? (string) $_SERVER['SERVER_ADDR'] : null);
        $this->appendAttribute($logAttributes, 'server.port', isset($_SERVER['SERVER_PORT']) ? (string) $_SERVER['SERVER_PORT'] : null);
        $this->appendAttribute($logAttributes, 'network.protocol.version', isset($_SERVER['SERVER_PROTOCOL']) ? (string) $_SERVER['SERVER_PROTOCOL'] : null);
        $this->appendAttribute($logAttributes, 'user_agent.original', isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : null);
        $this->appendAttribute($logAttributes, 'http.request.referrer', isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : null);
        $this->appendAttribute($logAttributes, 'http.route', isset($_SERVER['PATH_INFO']) ? (string) $_SERVER['PATH_INFO'] : null);
        $this->appendAttribute($logAttributes, 'thread.name', PHP_SAPI);
        $this->appendAttribute($logAttributes, 'thread.id', (string) getmypid());

        $context = $record['context'] ?? [];
        if (is_array($context)) {
            $exceptionContext = $context['exception'] ?? null;
            $exceptionFile = is_array($exceptionContext) ? ($exceptionContext['file'] ?? null) : null;
            $exceptionLine = is_array($exceptionContext) ? ($exceptionContext['line'] ?? null) : null;

            if (!empty($context['trace.id'])) {
                $this->appendAttribute($logAttributes, 'trace.id', (string) $context['trace.id']);
            }
            if (!empty($context['span.id'])) {
                $this->appendAttribute($logAttributes, 'span.id', (string) $context['span.id']);
            }
            if (!empty($context['request_id'])) {
                $this->appendAttribute($logAttributes, 'request.id', (string) $context['request_id']);
            }
            if (!empty($context['correlation_id'])) {
                $this->appendAttribute($logAttributes, 'correlation.id', (string) $context['correlation_id']);
            }

            $filePath = $context['file'] ?? $exceptionFile;
            if (is_string($filePath) && $filePath !== '') {
                $this->appendAttribute($logAttributes, 'file.path', $filePath);
            }

            $line = $context['line'] ?? $exceptionLine;
            if ($line !== null && $line !== '') {
                $this->appendAttribute($logAttributes, 'file.line', (string) $line);
            }

            if ($exceptionContext instanceof \Throwable) {
                $ex = $exceptionContext;
                $this->appendAttribute($logAttributes, 'exception.type', get_class($ex));
                $this->appendAttribute($logAttributes, 'exception.message', $ex->getMessage());
                $this->appendAttribute($logAttributes, 'exception.code', (string) $ex->getCode());
                $this->appendAttribute($logAttributes, 'exception.stacktrace', $ex->getTraceAsString());
                $this->appendAttribute($logAttributes, 'file.path', $ex->getFile());
                $this->appendAttribute($logAttributes, 'file.line', (string) $ex->getLine());
            }

            foreach ([
                'store',
                'website',
                'customer_id',
                'order_id',
                'quote_id',
                'cart_id',
                'module',
                'action',
                'event_id',
                'occurred_at',
                'event_type',
                'status',
                'system',
                'username',
                'username_hash',
                'failure_reason',
            ] as $key) {
                if (isset($context[$key]) && $context[$key] !== '') {
                    $this->appendAttribute($logAttributes, 'magento.' . $key, (string) $context[$key]);
                }
            }
        }

        $clientIp = $this->resolveClientIp();
        if ($clientIp !== null) {
            $this->appendAttribute($logAttributes, 'client.ip', $clientIp);
        }

        $scriptPath = isset($_SERVER['SCRIPT_FILENAME']) ? (string) $_SERVER['SCRIPT_FILENAME'] : '';
        if ($scriptPath !== '' && !in_array('file.path', array_column($logAttributes, 'key'), true)) {
            $this->appendAttribute($logAttributes, 'file.path', $scriptPath);
        }

        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
        if ($host !== '') {
            $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
            $scheme = isset($_SERVER['REQUEST_SCHEME']) ? (string) $_SERVER['REQUEST_SCHEME'] : 'https';
            $this->appendAttribute($logAttributes, 'url.full', $scheme . '://' . $host . $uri);
        }

        $payload = [
            'resourceLogs' => [[
                'resource' => [
                    'attributes' => $this->mapToOtlpAttributes($resourceAttributes),
                ],
                'scopeLogs' => [[
                    'scope' => [
                        'name' => 'abbott-ziggiz-logger',
                        'version' => PHP_VERSION,
                    ],
                    'logRecords' => [[
                        'timeUnixNano' => (string) (int) round(microtime(true) * 1000000000),
                        'severityNumber' => $recordLevel,
                        'severityText' => $record['level_name'] ?? Logger::getLevelName($recordLevel),
                        'body' => [
                            'stringValue' => $this->stringifyBody($record['message'] ?? ''),
                        ],
                        'attributes' => $this->mapToOtlpAttributes($logAttributes),
                    ]]
                ]]
            ]]
        ];

        try {
            $this->publisher->publish(
                self::TOPIC_NAME,
                $this->serializer->serialize([
                    'endpoint' => $endpoint,
                    'token' => $token,
                    'timeout' => $timeout,
                    'payload' => $payload,
                ])
            );
            $this->requestCount++;
        } catch (\Throwable $e) {
            // Silent fail to avoid blocking application logs
            // Consider implementing retry logic or error tracking here
        }
    }

    private function appendAttribute(array &$attributes, string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (array_key_exists($key, $attributes)) {
            return;
        }

        $attributes[$key] = $value;
    }

    private function mapToOtlpAttributes(array $attributes): array
    {
        $formatted = [];
        foreach ($attributes as $key => $value) {
            $formatted[] = [
                'key' => (string) $key,
                'value' => [
                    'stringValue' => (string) $value,
                ],
            ];
        }

        return $formatted;
    }

    private function stringifyBody(mixed $body): string
    {
        if (is_scalar($body) || $body === null) {
            return (string) $body;
        }

        return $this->serializer->serialize($body);
    }

    private function resolveServiceInstanceId(): ?string
    {
        $hostName = gethostname();
        if (is_string($hostName) && $hostName !== '') {
            return $hostName . '-' . getmypid();
        }

        return null;
    }

    private function resolveClientIp(): ?string
    {
        $forwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim((string) $_SERVER['HTTP_X_FORWARDED_FOR']) : '';
        if ($forwardedFor !== '') {
            $first = trim(explode(',', $forwardedFor)[0]);
            if ($first !== '') {
                return $first;
            }
        }

        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
        return $remoteAddr !== '' ? $remoteAddr : null;
    }
}
