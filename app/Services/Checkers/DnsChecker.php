<?php

namespace App\Services\Checkers;

use App\Models\Monitor;

class DnsChecker
{
    private const TYPE_MAP = [
        'A'     => DNS_A,
        'AAAA'  => DNS_AAAA,
        'CNAME' => DNS_CNAME,
        'MX'    => DNS_MX,
        'TXT'   => DNS_TXT,
        'NS'    => DNS_NS,
        'SOA'   => DNS_SOA,
    ];

    public function check(Monitor $monitor): CheckResult
    {
        $host       = $monitor->target;
        $recordType = strtoupper($monitor->dns_record_type ?? 'A');
        $expected   = $monitor->dns_expected_value;
        $dnsType    = self::TYPE_MAP[$recordType] ?? DNS_A;

        $start = hrtime(true);

        try {
            $records = dns_get_record($host, $dnsType);
        } catch (\Throwable $e) {
            return CheckResult::down('DNS lookup failed: ' . $e->getMessage());
        }

        $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);

        if (empty($records)) {
            return CheckResult::down("No {$recordType} records found for {$host}", $responseTime);
        }

        $values = $this->extractValues($records, $recordType);

        if ($expected) {
            $matched = collect($values)->contains(fn($v) => str_contains($v, $expected));
            if (! $matched) {
                return CheckResult::degraded(
                    $responseTime,
                    "Expected value '{$expected}' not found in DNS records. Got: " . implode(', ', $values),
                    null,
                    ['records' => $values],
                );
            }
        }

        return CheckResult::up($responseTime, null, null, ['records' => $values, 'type' => $recordType]);
    }

    private function extractValues(array $records, string $type): array
    {
        return array_map(function ($record) use ($type) {
            return match ($type) {
                'A'     => $record['ip'] ?? '',
                'AAAA'  => $record['ipv6'] ?? '',
                'CNAME' => $record['target'] ?? '',
                'MX'    => ($record['target'] ?? '') . ' (pri: ' . ($record['pri'] ?? '?') . ')',
                'TXT'   => implode(' ', $record['entries'] ?? []),
                'NS'    => $record['target'] ?? '',
                default => json_encode($record),
            };
        }, $records);
    }
}
