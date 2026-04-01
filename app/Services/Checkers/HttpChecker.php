<?php

namespace App\Services\Checkers;

use App\Models\Monitor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class HttpChecker
{
    public function check(Monitor $monitor): CheckResult
    {
        $client = new Client([
            'timeout'         => $monitor->timeout,
            'connect_timeout' => $monitor->timeout,
            'allow_redirects' => $monitor->follow_redirects,
            'verify'          => $monitor->verify_ssl,
            'http_errors'     => false,
        ]);

        $headers = $monitor->headers ?? [];
        $start   = hrtime(true);

        try {
            $response     = $client->request(
                $monitor->method ?? 'GET',
                $monitor->target,
                [
                    'headers' => $headers,
                    'body'    => $monitor->request_body,
                ]
            );
            $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);
            $statusCode   = $response->getStatusCode();
            $body         = (string) $response->getBody();

            // Check expected status code
            if ($monitor->expected_status_code && $statusCode !== $monitor->expected_status_code) {
                return CheckResult::down(
                    "Expected status {$monitor->expected_status_code}, got {$statusCode}",
                    $responseTime,
                    $statusCode,
                );
            }

            // Check expected body
            if ($monitor->expected_body_contains && ! str_contains($body, $monitor->expected_body_contains)) {
                return CheckResult::degraded(
                    $responseTime,
                    "Response body does not contain: {$monitor->expected_body_contains}",
                    $statusCode,
                );
            }

            // 5xx = down, 4xx client errors = degraded, 2xx/3xx = up
            if ($statusCode >= 500) {
                return CheckResult::down("Server error: {$statusCode}", $responseTime, $statusCode);
            }

            if ($statusCode >= 400) {
                return CheckResult::degraded($responseTime, "Client error: {$statusCode}", $statusCode);
            }

            return CheckResult::up($responseTime, $statusCode, substr($body, 0, 500));
        } catch (ConnectException $e) {
            $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);
            return CheckResult::down('Connection failed: ' . $e->getMessage(), $responseTime);
        } catch (RequestException $e) {
            $responseTime = (int) ((hrtime(true) - $start) / 1_000_000);
            return CheckResult::down('Request failed: ' . $e->getMessage(), $responseTime);
        } catch (\Throwable $e) {
            return CheckResult::down('Unexpected error: ' . $e->getMessage());
        }
    }
}
