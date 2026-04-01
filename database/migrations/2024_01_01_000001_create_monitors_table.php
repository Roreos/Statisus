<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // http, https, tcp, ping, dns
            $table->string('target'); // URL, host, IP
            $table->integer('port')->nullable();
            $table->integer('interval')->default(60); // seconds
            $table->integer('timeout')->default(10); // seconds
            $table->boolean('is_enabled')->default(true);

            // HTTP specific
            $table->string('method')->default('GET');
            $table->json('headers')->nullable();
            $table->text('request_body')->nullable();
            $table->integer('expected_status_code')->nullable();
            $table->string('expected_body_contains')->nullable();
            $table->boolean('follow_redirects')->default(true);
            $table->boolean('verify_ssl')->default(true);

            // DNS specific
            $table->string('dns_record_type')->nullable(); // A, AAAA, CNAME, MX, TXT
            $table->string('dns_expected_value')->nullable();
            $table->string('dns_nameserver')->nullable();

            // Status
            $table->string('status')->default('pending'); // up, down, degraded, pending
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_status_change_at')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->integer('failure_threshold')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
