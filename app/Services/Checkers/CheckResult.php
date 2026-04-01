<?php

namespace App\Services\Checkers;

class CheckResult
{
    public function __construct(
        public readonly string  $status,        // up, down, degraded
        public readonly ?int    $responseTime,  // ms
        public readonly ?int    $statusCode,
        public readonly ?string $responseBody,
        public readonly ?string $errorMessage,
        public readonly array   $metadata = [],
    ) {}

    public static function up(int $responseTime, ?int $statusCode = null, ?string $body = null, array $meta = []): self
    {
        return new self('up', $responseTime, $statusCode, $body, null, $meta);
    }

    public static function degraded(int $responseTime, string $reason, ?int $statusCode = null, array $meta = []): self
    {
        return new self('degraded', $responseTime, $statusCode, null, $reason, $meta);
    }

    public static function down(string $reason, ?int $responseTime = null, ?int $statusCode = null, array $meta = []): self
    {
        return new self('down', $responseTime, $statusCode, null, $reason, $meta);
    }
}
