<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_outlets', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            // Poster shown in the hero distribution network (stored path on the public disk).
            $table->string('poster_path')->nullable()->after('logo_path');
            $table->unsignedSmallInteger('poster_width')->nullable()->after('poster_path');
            $table->unsignedSmallInteger('poster_height')->nullable()->after('poster_width');
            $table->string('short_description', 160)->nullable()->after('category');
            $table->text('description')->nullable()->after('short_description');
        });

        // Backfill slugs for existing rows.
        foreach (DB::table('media_outlets')->select('id', 'name')->orderBy('id')->get() as $row) {
            $base = Str::slug($row->name) ?: 'outlet';
            $slug = $base;
            $i = 2;
            while (DB::table('media_outlets')->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            DB::table('media_outlets')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('media_outlets', function (Blueprint $table) {
            $table->dropColumn(['slug', 'poster_path', 'poster_width', 'poster_height', 'short_description', 'description']);
        });
    }
};
