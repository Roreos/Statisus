<?php

namespace App\Services\Checkers;

use App\Models\Monitor;

class SslChecker
{
    public function check(Monitor $monitor): CheckResult
    {
        $host    = $this->extractHost($monitor->target);
        $port    = $monitor->port ?? 443;
        $timeout = $monitor->timeout ?? 10;

        $start = hrtime(true);

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer'       => false, // we verify manually below
                'verify_peer_name'  => false,
                'SNI_enabled'       => true,
                'peer_name'         => $host,
            ],
        ]);

        $socket = @stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);

        if (!$socket) {
            return CheckResult::down("SSL connection failed: {$errstr} (errno: {$errno})", $responseTime);
        }

        $params = stream_context_get_params($socket);
        fclose($socket);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;

        if (!$cert) {
            return CheckResult::down('Could not retrieve SSL certificate', $responseTime);
        }

        $certInfo  = openssl_x509_parse($cert);
        $validTo   = $certInfo['validTo_time_t'] ?? 0;
        $validFrom = $certInfo['validFrom_time_t'] ?? 0;
        $subject   = $certInfo['subject']['CN'] ?? 'Unknown';
        $issuer    = $certInfo['issuer']['O'] ?? 'Unknown';
        $daysLeft  = (int) ceil(($validTo - time()) / 86400);

        $meta = [
            'subject'    => $subject,
            'issuer'     => $issuer,
            'valid_from' => date('Y-m-d', $validFrom),
            'valid_to'   => date('Y-m-d', $validTo),
            'days_left'  => $daysLeft,
        ];

        // Expired
        if ($validTo < time()) {
            return CheckResult::down("SSL certificate expired on " . date('Y-m-d', $validTo), $responseTime, null, $meta);
        }

        // Expiring within 14 days = degraded
        if ($daysLeft <= 14) {
            return CheckResult::degraded(
                $responseTime,
                "SSL certificate expires in {$daysLeft} days ({$subject})",
                null,
                $meta
            );
        }

        // Expiring within 30 days = degraded (softer warning)
        if ($daysLeft <= 30) {
            return CheckResult::degraded(
                $responseTime,
                "SSL certificate expires in {$daysLeft} days",
                null,
                $meta
            );
        }

        return CheckResult::up($responseTime, null, null, $meta);
    }

    private function extractHost(string $target): string
    {
        // Strip scheme if present
        $host = preg_replace('#^https?://#', '', $target);
        // Strip path
        return explode('/', $host)[0];
    }
}
