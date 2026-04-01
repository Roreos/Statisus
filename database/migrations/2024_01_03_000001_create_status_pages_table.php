<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#0ea5e9'); // tailwind sky-500
            $table->string('background_color')->default('#0f172a'); // tailwind slate-900
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('show_incidents')->default(true);
            $table->boolean('show_maintenance')->default(true);
            $table->boolean('show_uptime_graph')->default(true);
            $table->timestamps();
        });

        Schema::create('status_page_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable(); // override monitor name
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['status_page_id', 'monitor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_page_monitors');
        Schema::dropIfExists('status_pages');
    }
};
