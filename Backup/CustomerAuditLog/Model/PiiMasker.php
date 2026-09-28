<?php
declare(strict_types=1);

namespace Abbott\CustomerAuditLog\Model;

/**
 * Hashes PII (email, IP) so audit logs never store reversible personal data.
 */
class PiiMasker
{
    /**
     * @param string|null $email
     * @return string|null
     */
    public function hashEmail(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }
        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * Truncates the last IP octet (or the last 16 bits for IPv6) before hashing,
     * so the stored value cannot be used to re-identify an individual IP address.
     *
     * @param string|null $ip
     * @return string|null
     */
    public function hashIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }
        return hash('sha256', $this->truncateIp($ip));
    }

    /**
     * @param string $ip
     * @return string
     */
    private function truncateIp(string $ip): string
    {
        if (str_contains($ip, '.')) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }
        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            $count = count($parts);
            for ($i = max(0, $count - 2); $i < $count; $i++) {
                $parts[$i] = '0';
            }
            return implode(':', $parts);
        }
        return $ip;
    }
}
