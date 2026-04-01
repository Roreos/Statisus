<?php

namespace App\Services\Checkers;

use App\Models\Monitor;

class TcpChecker
{
    public function check(Monitor $monitor): CheckResult
    {
        $host    = $monitor->target;
        $port    = $monitor->port;
        $timeout = $monitor->timeout;

        if (! $port) {
            return CheckResult::down('No port specified for TCP check');
        }

        $start = hrtime(true);

        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);

        if ($socket === false) {
            return CheckResult::down("TCP connection failed: {$errstr} (errno: {$errno})", $responseTime);
        }

        fclose($socket);

        return CheckResult::up($responseTime, null, null, ['host' => $host, 'port' => $port]);
    }
}
