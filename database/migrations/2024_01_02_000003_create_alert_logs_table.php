<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alert_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('alert_channel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');        // down, degraded, recovery
            $table->integer('escalation_level')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['monitor_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_logs');
    }
};
