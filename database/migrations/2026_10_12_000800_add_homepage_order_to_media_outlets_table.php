<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_outlets', function (Blueprint $table) {
            // Position of the outlet among the homepage news cards (admin-set); null = after the numbered ones.
            $table->unsignedInteger('homepage_order')->nullable()->after('is_highlighted');
        });
    }

    public function down(): void
    {
        Schema::table('media_outlets', function (Blueprint $table) {
            $table->dropColumn('homepage_order');
        });
    }
};
