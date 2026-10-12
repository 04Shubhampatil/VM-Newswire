<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Admin-chosen: list this package in the header "Packages" dropdown.
            $table->boolean('show_in_menu')->default(false)->after('is_highlighted');
        });

        // Keep the menu as it was: the first four active packages in display order.
        $ids = DB::table('packages')->where('is_active', true)->whereNull('deleted_at')
            ->orderBy('display_order')->orderBy('name')->limit(4)->pluck('id');
        DB::table('packages')->whereIn('id', $ids)->update(['show_in_menu' => true]);
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('show_in_menu');
        });
    }
};
