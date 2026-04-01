<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // email, discord, slack, webhook, sms, push
            $table->boolean('is_enabled')->default(true);
            $table->json('config'); // type-specific config (url, email, phone, etc.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_channels');
    }
};
