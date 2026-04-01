<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // investigating, identified, monitoring, resolved
            $table->text('message');
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamps();

            $table->index(['incident_id', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_updates');
    }
};
