<?php
declare(strict_types=1);

namespace Abbott\ZiggizLogger\Model\Queue;

use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;

class Consumer
{
    private const MAX_ATTEMPTS = 3;
    private const BATCH_SIZE = 20;
    private const FLUSH_INTERVAL_SECONDS = 2;

    private array $batch = [];
    private int $lastFlushAt;
    private bool $shutdownRegistered = false;

    public function __construct(
        private readonly Curl $curl,
        private readonly Json $serializer
    ) {
        $this->lastFlushAt = time();
    }

    public function process(string $rawData): void
    {
        $this->registerShutdownFlush();

        try {
            $data = $this->serializer->unserialize($rawData);
            if (!is_array($data)) {
                return;
            }

            if ($this->isValidMessage($data)) {
                $this->batch[] = $data;
                $this->flushBatchIfNeeded();
            }
        } catch (Exception $e) {
            // Silent fail to keep consumer running and avoid recursive logging.
        }
    }

    private function registerShutdownFlush(): void
    {
        if ($this->shutdownRegistered) {
            return;
        }

        $this->shutdownRegistered = true;
        register_shutdown_function(function (): void {
            $this->flushBatch();
        });
    }

    private function isValidMessage(array $data): bool
    {
        $endpoint = (string)($data['endpoint'] ?? '');
        $payload = $data['payload'] ?? [];

        return $endpoint !== '' && is_array($payload);
    }

    private function flushBatchIfNeeded(): void
    {
        $shouldFlushBySize = count($this->batch) >= self::BATCH_SIZE;
        $shouldFlushByTime = (time() - $this->lastFlushAt) >= self::FLUSH_INTERVAL_SECONDS;

        if ($shouldFlushBySize || $shouldFlushByTime) {
            $this->flushBatch();
        }
    }

    private function flushBatch(): void
    {
        if ($this->batch === []) {
            return;
        }

        $groups = [];
        foreach ($this->batch as $message) {
            $endpoint = (string)$message['endpoint'];
            $token = (string)($message['token'] ?? '');
            $timeout = max(1, (int)($message['timeout'] ?? 5));
            $groupKey = hash('sha256', $endpoint . '|' . $token . '|' . $timeout);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'endpoint' => $endpoint,
                    'token' => $token,
                    'timeout' => $timeout,
                    'payloads' => [],
                ];
            }

            $groups[$groupKey]['payloads'][] = $message['payload'];
        }

        $this->batch = [];
        $this->lastFlushAt = time();

        foreach ($groups as $group) {
            $batchedPayload = [
                'resourceLogs' => [],
            ];

            foreach ($group['payloads'] as $payload) {
                $resourceLogs = $payload['resourceLogs'] ?? [];
                if (is_array($resourceLogs)) {
                    $batchedPayload['resourceLogs'] = array_merge($batchedPayload['resourceLogs'], $resourceLogs);
                }
            }

            if ($batchedPayload['resourceLogs'] === []) {
                continue;
            }

            $headers = [
                'Content-Type' => 'application/json',
            ];

            if ($group['token'] !== '') {
                $headers['Authorization'] = 'Bearer ' . $group['token'];
            }

            $body = $this->serializer->serialize($batchedPayload);
            $this->postWithRetry($group['endpoint'], $headers, $body, (int)$group['timeout']);
        }
    }

    private function postWithRetry(string $endpoint, array $headers, string $body, int $timeout): void
    {
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $this->curl->setTimeout($timeout);
                $this->curl->setHeaders($headers);
                $this->curl->post($endpoint, $body);

                $statusCode = (int)$this->curl->getStatus();
                if ($statusCode >= 200 && $statusCode < 300) {
                    return;
                }
            } catch (Exception $e) {
                // Try again on transient transport failures.
            }

            if ($attempt < self::MAX_ATTEMPTS) {
                usleep(100000 * $attempt);
            }
        }
    }
}
