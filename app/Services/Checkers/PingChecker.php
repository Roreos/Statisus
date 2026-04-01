<?php

namespace App\Services\Checkers;

use App\Models\Monitor;

class PingChecker
{
    public function check(Monitor $monitor): CheckResult
    {
        $host    = escapeshellarg($monitor->target);
        $timeout = $monitor->timeout;

        // Build OS-appropriate ping command
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "ping -n 1 -w " . ($timeout * 1000) . " {$host} 2>&1";
        } else {
            $cmd = "ping -c 1 -W {$timeout} {$host} 2>&1";
        }

        $start  = hrtime(true);
        $output = [];
        $code   = 0;

        exec($cmd, $output, $code);

        $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);
        $outputStr    = implode("\n", $output);

        if ($code !== 0) {
            return CheckResult::down("Ping failed: host unreachable", $responseTime, null, ['output' => $outputStr]);
        }

        // Parse RTT from output
        $rtt = $this->parseRtt($outputStr);

        return CheckResult::up($rtt ?? $responseTime, null, null, ['output' => $outputStr]);
    }

    private function parseRtt(string $output): ?int
    {
        // Windows: "Average = 12ms"
        if (preg_match('/Average\s*=\s*(\d+)ms/i', $output, $m)) {
            return (int) $m[1];
        }
        // Linux/Mac: "time=12.3 ms"
        if (preg_match('/time[<=]([\d.]+)\s*ms/i', $output, $m)) {
            return (int) round((float) $m[1]);
        }
        return null;
    }
}
