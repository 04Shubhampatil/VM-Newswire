<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('category', 64)->index();
            $table->boolean('is_active')->default(true)->index();
            // Highlighted outlets appear in the home page hero and media strip.
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('package_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_outlet_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['package_id', 'media_outlet_id']);
            $table->index(['package_id', 'is_featured', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_media');
        Schema::dropIfExists('media_outlets');
    }
};
