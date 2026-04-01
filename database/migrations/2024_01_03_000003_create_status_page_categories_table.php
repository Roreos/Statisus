<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_page_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('circle'); // heroicon name suffix
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('status_pages', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('status_page_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('status_pages', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\StatusPageCategory::class);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('status_page_categories');
    }
};
