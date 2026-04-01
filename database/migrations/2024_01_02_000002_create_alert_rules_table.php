<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_enabled')->default(true);

            // Trigger conditions
            $table->boolean('alert_on_down')->default(true);
            $table->boolean('alert_on_degraded')->default(false);
            $table->boolean('alert_on_recovery')->default(true);
            $table->integer('failure_threshold')->default(1); // failures before first alert

            // Escalation: if still down after X minutes, alert escalation channels
            $table->boolean('escalation_enabled')->default(false);
            $table->integer('escalation_after_minutes')->default(30);

            $table->timestamps();
        });

        Schema::create('alert_rule_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alert_channel_id')->constrained()->cascadeOnDelete();
            $table->integer('escalation_level')->default(0); // 0 = primary, 1+ = escalation
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rule_channels');
        Schema::dropIfExists('alert_rules');
    }
};
