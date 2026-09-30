<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand', 64)->nullable()->index();
            $table->string('short_description', 500);
            $table->longText('full_content')->nullable();
            $table->string('distribution_summary')->nullable();
            $table->json('features')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->char('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
        });

        // Keeps old package URLs working after an admin changes a slug.
        Schema::create('package_slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('old_slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_slug_redirects');
        Schema::dropIfExists('packages');
    }
};
