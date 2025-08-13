<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained('users');
            $table->foreignUlid('category_id')->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration');
            $table->decimal('price', 10, 2);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['provider_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
